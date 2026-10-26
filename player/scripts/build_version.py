#!/usr/bin/env python3
"""Version the player by its build inputs, independent of TeaVM output order."""

import hashlib
from pathlib import Path
import subprocess


def build_version(root):
    paths = subprocess.check_output(
        ["git", "ls-files", "-z", "--", "catroid.commit", "build.gradle.kts",
         "settings.gradle.kts", "gradle", "gradlew", "compat", "src", "web", "scripts"],
        cwd=root,
    ).split(b"\0")
    digest = hashlib.sha256()
    for name in sorted(filter(None, paths)):
        digest.update(name + b"\0")
        digest.update(hashlib.sha256((root / name.decode()).read_bytes()).digest())
    # The nightly pin workflow can build a different commit before updating catroid.commit.
    commit = subprocess.check_output(
        ["git", "-C", str(root / "build/catroid"), "rev-parse", "HEAD"], text=True,
    ).strip()
    digest.update(commit.encode())
    return digest.hexdigest()[:12]


if __name__ == "__main__":
    print(build_version(Path(__file__).resolve().parents[1]))
