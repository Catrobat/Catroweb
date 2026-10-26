"""Build versions follow source inputs and Catroid overrides, not generated output."""

from pathlib import Path
import subprocess
import tempfile
import unittest

from build_version import build_version


class BuildVersionTest(unittest.TestCase):
    def git(self, root, *args):
        return subprocess.check_output(["git", "-C", str(root), *args], stderr=subprocess.STDOUT)

    def test_inputs_and_catroid_override(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            self.git(root, "init", "-q")
            (root / "web").mkdir()
            source = root / "web/start.js"
            source.write_text("original")
            self.git(root, "add", "web")
            catroid = root / "build/catroid"
            catroid.mkdir(parents=True)
            self.git(catroid, "init", "-q")
            commit_args = ("-c", "commit.gpgsign=false", "-c", "user.name=Test", "-c", "user.email=test@example.invalid",
                           "commit", "-q", "--allow-empty", "-m")
            self.git(catroid, *commit_args, "first")
            original = build_version(root)
            self.assertRegex(original, r"^[0-9a-f]{12}$")
            (root / "build/app.js").write_text("nondeterministic generated output")
            self.assertEqual(original, build_version(root))
            source.write_text("changed")
            self.assertNotEqual(original, build_version(root))
            source.write_text("original")
            source.rename(root / "web/renamed.js")
            self.git(root, "add", "-A", "web")
            self.assertNotEqual(original, build_version(root))
            (root / "web/renamed.js").rename(source)
            self.git(root, "add", "-A", "web")
            self.assertEqual(original, build_version(root))
            self.git(catroid, *commit_args, "override")
            self.assertNotEqual(original, build_version(root))


if __name__ == "__main__":
    unittest.main()
