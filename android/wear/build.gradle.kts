plugins {
    alias(libs.plugins.android.application)
    // Only this module needs it: the phone app is a WebView and has no Compose in it.
    alias(libs.plugins.compose.compiler)
}

android {
    namespace = "com.xpertxyz.ledger.wear"
    compileSdk = 37

    defaultConfig {
        // The same applicationId as the phone app, on purpose: that is what lets Play carry
        // both in one listing under one Wear OS form factor, so installing the watch app is
        // a toggle on the phone app's store page rather than a second thing to find.
        //
        // It does NOT mean the two talk to each other. This app is a client of the website
        // (see api.php); the phone build serves its own SQLite over 127.0.0.1 and has no
        // socket a watch could reach. They share a name and a look, nothing else.
        applicationId = "com.xpertxyz.ledger"

        // Wear OS 3 and up. Below that is a fundamentally different platform (Wear 2 apps are
        // paired-only, with a different distribution model) and nothing Samsung still ships.
        minSdk = 30
        targetSdk = 36

        // Must not collide with the phone's, because Play scopes version codes to the whole
        // app rather than to a form factor. Offset rather than shared so the two can be
        // released independently without either one blocking the other.
        versionCode = 10002
        versionName = "1.0.2"

        // Where the ledger lives. A watch has nowhere sensible to type a URL, and the pairing
        // code is six digits with no room to carry one, so the address is compiled in. Point a
        // build at a local server with:
        //   ./gradlew :wear:assembleDebug -PledgerUrl=http://192.168.1.5:8080
        buildConfigField(
            "String",
            "LEDGER_URL",
            "\"${(project.findProperty("ledgerUrl") as String? ?: "https://ledger.xpertxyz.com").trimEnd('/')}\""
        )
    }

    buildFeatures {
        compose = true
        buildConfig = true
    }

    buildTypes {
        release {
            isMinifyEnabled = true
            isShrinkResources = true
            proguardFiles(getDefaultProguardFile("proguard-android-optimize.txt"), "proguard-rules.pro")
        }
    }

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }
}

dependencies {
    val composeBom = platform(libs.compose.bom)
    implementation(composeBom)

    implementation(libs.androidx.activity.compose)
    implementation(libs.androidx.compose.ui)
    implementation(libs.androidx.compose.ui.graphics)
    implementation(libs.androidx.lifecycle.runtime.ktx)

    // Wear's own Material 3 — not the phone's. Different components (ScalingLazyColumn, the
    // round TimeText header) and different touch targets, on a screen where the phone's
    // layout assumptions are simply wrong.
    implementation(libs.wear.compose.material3)
    implementation(libs.wear.compose.foundation)

    // The system input picker — voice, the tiny keyboard, handwriting — behind one intent.
    // A watch has no room for a keypad of our own, and Samsung's own input methods are the
    // ones the wearer already knows.
    implementation(libs.wear.input)

    // The tile: the swipe-right card next to the watch face. Drawn with ProtoLayout, not
    // Compose — a tile is rendered by the system launcher in another process, so it is a
    // serialised layout tree rather than a running composition.
    implementation(libs.wear.tiles)
    implementation(libs.wear.protolayout)
    implementation(libs.wear.protolayout.material3)
    implementation(libs.wear.protolayout.expression)
    // Two numbers on whatever watch face the wearer already uses, next to the step count and
    // the battery the system already provides. See LedgerComplications.kt.
    implementation(libs.wear.complications)
    // TileService hands back a ListenableFuture, and the tiles library ships only the
    // interface. This is the smallest implementation of it that exists — Guava, the usual
    // answer, is several megabytes for one static factory method.
    implementation(libs.androidx.concurrent.futures)

    // Local mode: talking to the phone app's own ledger over Bluetooth, with no server and no
    // internet on either device. See PhoneBackend.kt.
    implementation(libs.play.services.wearable)
    implementation(libs.kotlinx.coroutines.play.services)
    // Not used directly. play-services-wearable drags in a pre-1.3 androidx.fragment, whose
    // FragmentActivity never called super.onRequestPermissionsResult — which makes every
    // registerForActivityResult in this module unsound, and which lintVital fails the release
    // build over. Naming a current version is what resolves it.
    implementation(libs.androidx.fragment.ktx)
    // Flushes expenses that were logged with no route to the server. See PendingSync.kt.
    implementation(libs.androidx.work.runtime.ktx)

    testImplementation(libs.junit)
    // Android's org.json is an unimplemented stub on the JVM — every method throws
    // "not mocked". The palette is parsed from JSON, so testing it needs a real one.
    testImplementation(libs.org.json)
}
