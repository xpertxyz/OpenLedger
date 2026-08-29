plugins {
    alias(libs.plugins.android.application)
}

android {
    namespace = "com.xpertxyz.ledger"
    compileSdk = 37

    defaultConfig {
        applicationId = "com.xpertxyz.ledger"
        minSdk = 24
        targetSdk = 37
        versionCode = 9
        versionName = "1.0.9"

        // Only arm64. Every Android device sold for years is arm64, and each extra ABI adds
        // another ~8MB PHP binary to the download for machines that no longer exist.
        ndk { abiFilters += "arm64-v8a" }

        // Compiled in, not switchable at runtime: an app whose behaviour changes after review
        // is the pattern that gets it pulled from Play. PhpServer passes these to PHP as
        // HL_SHARING / HL_GOOGLE_SIGNIN, which config.php reads exactly as a server would.
        //
        // There was a second "connected" flavour here for a build that talks to the hosted
        // ledger. It was deleted rather than tested: it could not have worked. The bundled PHP
        // is built --disable-all with no openssl, so it has no https:// stream wrapper, and
        // verifyGoogleIdToken() reaches Google's tokeninfo endpoint over HTTPS. Turning
        // sign-in on would have produced a login screen that can never succeed, in a build
        // still serving the local SQLite file. A real connected app is a WebView pointed at
        // the website — different code, and worth writing when it is actually wanted.
        buildConfigField("boolean", "FEATURE_SHARING", "false")
        buildConfigField("boolean", "FEATURE_SIGNIN", "false")

        // BackupCrypto keeps its key in the Android Keystore, which only exists on a device,
        // so its tests are instrumented rather than local JVM ones.
        testInstrumentationRunner = "androidx.test.runner.AndroidJUnitRunner"
    }

    buildFeatures { buildConfig = true }

    buildTypes {
        release {
            // The Drive client libraries are most of the download; R8 removes the ~90% of them
            // this app never calls. proguard-rules.pro keeps back the parts reached only by
            // reflection — without it the build succeeds and backup fails at runtime.
            isMinifyEnabled = true
            isShrinkResources = true
            proguardFiles(getDefaultProguardFile("proguard-android-optimize.txt"), "proguard-rules.pro")
        }
    }

    // Play delivers one APK per device from the bundle. Only ABI matters here: there is a
    // single density-independent icon and no translated strings of our own, so splitting on
    // those would add build variants for nothing.
    bundle {
        language { enableSplit = false }
    }

    sourceSets["main"].jniLibs.srcDirs("src/main/jniLibs")

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }

    packaging {
        // The PHP interpreter must stay uncompressed and be extracted at install time, or it
        // cannot be executed from nativeLibraryDir. See build-php.sh for the whole story.
        jniLibs { useLegacyPackaging = true }
        // The Google auth libraries each ship their own copy of these, and the merger will not
        // pick a winner on its own. None of them affect runtime behaviour.
        resources {
            excludes += setOf(
                "META-INF/INDEX.LIST",
                "META-INF/DEPENDENCIES",
                "META-INF/LICENSE*",
                "META-INF/NOTICE*",
                "META-INF/*.kotlin_module"
            )
        }
    }
}

/**
 * Copy the PHP app into assets at build time.
 *
 * The Android build has no copy of the ledger's source. It takes the same files the website
 * serves, straight from the repository root, so the two cannot diverge — if it is not in the
 * web app it is not in the phone app.
 */
val phpAppFiles = listOf(
    "index.php", "lib.php", "views.php", "config.php", "router.php", "api.php",
    "manifest.webmanifest"
)
val phpAppDirs = listOf("design-tokens", "assets")

/**
 * A task rather than a bare Copy so that its output is a DirectoryProperty, which is the only
 * thing AGP's asset API will accept.
 *
 * That matters more than it sounds. This was a Copy whose output directory was handed to
 * `sourceSets.assets.srcDir(files(...).builtBy(task))`, and AGP does not carry a task
 * dependency through that: mergeAssets ran, this never did, and the APK shipped whatever PHP
 * happened to be left in build/php-assets from an earlier build. Editing views.php and
 * reinstalling produced an app running the old page, silently — the exact drift this whole
 * copy-from-the-repo arrangement exists to prevent.
 */
abstract class SyncPhpApp : DefaultTask() {
    @get:InputFiles @get:PathSensitive(PathSensitivity.RELATIVE)
    abstract val appFiles: ConfigurableFileCollection

    @get:InputFiles @get:PathSensitive(PathSensitivity.RELATIVE)
    abstract val appDirs: ConfigurableFileCollection

    /** Set by AGP, not by us: it decides where a variant's generated assets live. */
    @get:OutputDirectory
    abstract val outDir: DirectoryProperty

    @get:Inject abstract val fs: FileSystemOperations

    @TaskAction
    fun sync() {
        val dirs = appDirs.files
        val files = appFiles.files
        fs.sync {
            into(outDir.dir("app"))
            from(files)
            dirs.forEach { d -> from(d) { into(d.name) } }
            // .env is a server-side file holding MySQL credentials. It must never reach a
            // phone — the Android build is configured entirely through the process
            // environment instead.
            exclude(".env", ".env.example", "data/**")
        }
    }
}

val syncPhpApp by tasks.registering(SyncPhpApp::class) {
    val repoRoot = rootProject.projectDir.parentFile
    appFiles.from(phpAppFiles.map { File(repoRoot, it) })
    appDirs.from(phpAppDirs.map { File(repoRoot, it) })
}

// The AGP way to contribute generated assets. Unlike srcDir(), this wires the task into every
// consumer — merge, lint, bundle — so none of them can read the directory before it is written.
androidComponents {
    onVariants { variant ->
        variant.sources.assets?.addGeneratedSourceDirectory(syncPhpApp, SyncPhpApp::outDir)
    }
}

dependencies {
    implementation(libs.androidx.activity)
    implementation(libs.androidx.fragment.ktx)
    implementation(libs.androidx.lifecycle.runtime.ktx)
    // The app lock. Also what pulls in androidx.fragment, which BiometricPrompt needs a
    // FragmentActivity for — hence MainActivity's base class.
    implementation(libs.androidx.biometric)
    // No appcompat: the activity is a ComponentActivity and the theme extends the platform's
    // Material, so it only ever contributed dex and ~80 translated app-name strings.
    implementation(libs.androidx.work.runtime.ktx)
    // Play's flexible in-app update. The download and the install are Play's; every piece of
    // UI around them is views.php, so an update offer looks like the rest of the app.
    implementation(libs.play.app.update)
    // Local watch sync. The watch cannot reach this app's loopback server, so it asks over
    // the Data Layer instead and LedgerWearService answers from the same PHP. See WEAR.md.
    implementation(libs.play.services.wearable)
    // Drive backup, appDataFolder scope only — see ANDROID.md for why not full `drive`.
    implementation(libs.play.services.auth)
    implementation(libs.androidx.credentials)
    implementation(libs.androidx.credentials.play.services.auth)
    implementation(libs.googleid)
    implementation(libs.google.api.client.android)
    implementation(libs.google.api.services.drive)
    implementation(libs.kotlinx.coroutines.play.services)

    androidTestImplementation(libs.androidx.test.ext.junit)
    androidTestImplementation(libs.androidx.test.runner)
}
