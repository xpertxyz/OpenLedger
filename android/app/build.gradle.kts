import java.util.Properties
import javax.inject.Inject

plugins {
    id("com.android.application")
    id("org.jetbrains.kotlin.android")
}

android {
    namespace = "com.xpertxyz.ledger"
    compileSdk = 35

    defaultConfig {
        applicationId = "com.xpertxyz.ledger"
        minSdk = 24
        targetSdk = 35
        versionCode = 1
        versionName = "1.0"

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
    kotlinOptions { jvmTarget = "17" }

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
    "index.php", "lib.php", "views.php", "config.php", "router.php",
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
    implementation("androidx.activity:activity:1.9.3")
    // The app lock. Also what pulls in androidx.fragment, which BiometricPrompt needs a
    // FragmentActivity for — hence MainActivity's base class.
    implementation("androidx.biometric:biometric:1.1.0")
    // No appcompat: the activity is a ComponentActivity and the theme extends the platform's
    // Material, so it only ever contributed dex and ~80 translated app-name strings.
    implementation("androidx.work:work-runtime-ktx:2.9.1")
    // Drive backup, appDataFolder scope only — see ANDROID.md for why not full `drive`.
    implementation("com.google.android.gms:play-services-auth:21.2.0")
    implementation("com.google.api-client:google-api-client-android:2.7.0")
    implementation("com.google.apis:google-api-services-drive:v3-rev20240914-2.0.0")

    androidTestImplementation("androidx.test.ext:junit:1.2.1")
    androidTestImplementation("androidx.test:runner:1.6.2")
}
