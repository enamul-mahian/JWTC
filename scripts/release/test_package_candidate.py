import importlib.util
import json
import tarfile
import tempfile
import unittest
from pathlib import Path

spec = importlib.util.spec_from_file_location('package_candidate', Path(__file__).with_name('package_candidate.py'))
candidate = importlib.util.module_from_spec(spec)
spec.loader.exec_module(candidate)


class CandidatePackageTest(unittest.TestCase):
    def fixture(self, root):
        files = {
            'artisan': '<?php', 'composer.json': '{}', 'composer.lock': '{}',
            'bootstrap/app.php': '<?php', 'public/index.php': '<?php',
            'vendor/autoload.php': '<?php',
            'vendor/composer/installed.json': '{"dev":false,"packages":[]}',
            'public/build/manifest.json': json.dumps({'app': {'file': 'assets/app.js'}}),
            'public/build/assets/app.js': 'console.log("fixture");',
            '.env': 'APP_KEY=private', 'public/uploads/passport.pdf': 'private',
            'storage/app/document.pdf': 'private', 'bootstrap/cache/config.php': 'private',
            'public/images/old-photo.jpg': 'private', 'database/real.sql': 'private',
            'tests/fixture.php': 'private', 'public/theme/jwtc.css': 'body{}',
        }
        for name, content in files.items():
            path = root / name
            path.parent.mkdir(parents=True, exist_ok=True)
            path.write_text(content)
        return list(files)

    def run_build(self, root, output, files):
        return candidate.build(root, output, 'a' * 40, files)

    def test_archive_excludes_credentials_uploads_and_runtime_cache_even_if_tracked(self):
        with tempfile.TemporaryDirectory() as temp:
            root = Path(temp) / 'source'
            files = self.fixture(root)
            result = self.run_build(root, Path(temp) / 'output', files)
            with tarfile.open(Path(temp) / 'output' / result['package']) as archive:
                names = archive.getnames()
            self.assertIn('public/theme/jwtc.css', names)
            self.assertIn('vendor/autoload.php', names)
            self.assertIn('public/build/assets/app.js', names)
            for name in ('.env', 'public/uploads/passport.pdf', 'storage/app/document.pdf',
                         'bootstrap/cache/config.php', 'public/images/old-photo.jpg', 'database/real.sql'):
                self.assertNotIn(name, names)
            self.assertFalse(result['production_ready'])

    def test_development_vendor_is_refused(self):
        with tempfile.TemporaryDirectory() as temp:
            root = Path(temp) / 'source'
            files = self.fixture(root)
            (root / 'vendor/composer/installed.json').write_text('{"dev":true}')
            with self.assertRaisesRegex(ValueError, '--no-dev'):
                self.run_build(root, Path(temp) / 'out', files)

    def test_missing_frontend_asset_is_refused(self):
        with tempfile.TemporaryDirectory() as temp:
            root = Path(temp) / 'source'
            files = self.fixture(root)
            (root / 'public/build/assets/app.js').unlink()
            files.remove('public/build/assets/app.js')
            with self.assertRaisesRegex(ValueError, 'missing or unsafe'):
                self.run_build(root, Path(temp) / 'out', files)

    def test_symlinked_upload_cannot_enter_runtime(self):
        with tempfile.TemporaryDirectory() as temp:
            root = Path(temp) / 'source'
            files = self.fixture(root)
            (root / 'public/leak.php').symlink_to(root / '.env')
            with self.assertRaisesRegex(ValueError, 'Unsafe runtime'):
                self.run_build(root, Path(temp) / 'out', files + ['public/leak.php'])

    def test_path_traversal_is_excluded(self):
        for name in ('../.env', '/tmp/secret.php', 'public/../.env', 'public\\secret.php'):
            self.assertFalse(candidate.tracked_runtime(name))


if __name__ == '__main__':
    unittest.main()
