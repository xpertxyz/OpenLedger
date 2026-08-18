# Move dependencies to Version Catalog (libs.versions.toml)

This plan moves hardcoded dependency versions and declarations from `build.gradle.kts` files into a centralized `gradle/libs.versions.toml` file. This is the modern, recommended way to manage dependencies in Android projects, providing better maintainability and type safety.

## Proposed Changes

### [Version Catalog]

#### [NEW] [libs.versions.toml](file:///Users/pkStudio/Developer/XpertXYZ/Exp/HomeLedger/android/gradle/libs.versions.toml)
Create the version catalog file with versions, libraries, and plugins definitions.

### [Build Scripts]

#### [MODIFY] [build.gradle.kts](file:///Users/pkStudio/Developer/XpertXYZ/Exp/HomeLedger/android/build.gradle.kts)
Update the root `build.gradle.kts` to use plugin aliases from the version catalog.

#### [MODIFY] [app/build.gradle.kts](file:///Users/pkStudio/Developer/XpertXYZ/Exp/HomeLedger/android/app/build.gradle.kts)
Update the app module's `build.gradle.kts` to use plugin aliases and library accessors.

## Verification Plan

### Automated Tests
- Run `./gradlew help` to verify that the build scripts can be parsed correctly.
- Run `./gradlew assembleDebug` to ensure the project still builds successfully with the new dependency management.
