plugins {
    java
    id("com.github.xpenatan.gdx-teavm") version "1.6.2"
}

repositories {
    mavenCentral()
    maven("https://teavm.org/maven/repository/")
}

java { toolchain { languageVersion.set(JavaLanguageVersion.of(21)) } }

dependencies {
    implementation("com.badlogicgames.gdx:gdx:1.14.2")
    // Patched Catroid engine and its libraries, written by scripts/build.sh; empty on the first round.
    val patched = file("build/web/patched-classpath.txt")
    implementation(files(if (patched.exists()) patched.readLines().filter { it.isNotBlank() } else emptyList<String>()))
}

gdxTeaVM {
    assets("assets") // Catroid's own stage assets
    webDefaults {
        mainClass = "org.catrobat.web.WebLauncher"
        htmlTitle = "Pocket Code player"
        obfuscated = false
    }
    js {
        // Classes the project loader builds by reflection; TeaVM drops constructors nothing calls directly.
        // Written by scripts/build.sh next to the reflection policy's rules (compiler/CatroidReflectionPolicy).
        val preserved = file("build/web/preserved-classes.txt")
        if (preserved.exists()) {
            preservedClasses.addAll(preserved.readLines().filter { it.isNotBlank() })
        }
        outOfProcess = true
        processMemory = 12288 // TeaVM needs several GB for the whole engine
    }
}
