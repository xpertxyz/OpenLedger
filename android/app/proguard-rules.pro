# R8 rules for the release build.
#
# Almost all of this file exists for one reason: the Google API client maps JSON to model
# classes by reflection, so R8 cannot see those fields being used and strips them. The failure
# is not a build error — it is a backup that uploads and a restore that silently finds nothing,
# which is the worst way for this particular feature to break.

# Model fields are bound by @Key, never referenced directly.
-keepclassmembers class * {
  @com.google.api.client.util.Key <fields>;
}
-keep class com.google.api.client.** { *; }
-keep class com.google.api.services.drive.** { *; }
-keep class com.google.api.client.googleapis.extensions.android.gms.auth.** { *; }

# GSON/Gson factory types reached reflectively by the same machinery.
-keep class com.google.gson.** { *; }
-keepattributes Signature, *Annotation*, EnclosingMethod, InnerClasses

# Play Services resolves these by name.
-keep class com.google.android.gms.auth.** { *; }
-keep class com.google.android.gms.common.** { *; }

# The WebView calls these directly from JavaScript, so nothing in the app "uses" them and R8
# would otherwise rename or remove every one. Losing them means the backup panel's buttons
# quietly do nothing in release and work perfectly in debug.
-keepclassmembers class xyz.openledger.app.BackupBridge {
  @android.webkit.JavascriptInterface <methods>;
}

# WorkManager instantiates workers by class name from its own database.
-keep class * extends androidx.work.ListenableWorker {
  public <init>(android.content.Context, androidx.work.WorkerParameters);
}

# Desktop-only transports the client library references but Android never loads.
-dontwarn com.google.api.client.http.apache.**
-dontwarn org.apache.http.**
-dontwarn javax.naming.**
-dontwarn java.awt.**
