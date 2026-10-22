import io
import tempfile
import tarfile
import unittest
from pathlib import Path
from restore_guard import extract, validate_sql


class RestoreGuardsTest(unittest.TestCase):
    def archive(self, root, members):
        archive = root / 'files.tar.gz'
        with tarfile.open(archive, 'w:gz') as stream:
            for name, kind, data in members:
                entry = tarfile.TarInfo(name)
                entry.type = kind
                entry.size = len(data) if kind == tarfile.REGTYPE else 0
                entry.linkname = '/etc/passwd' if kind in (tarfile.SYMTYPE, tarfile.LNKTYPE) else ''
                stream.addfile(entry, io.BytesIO(data) if entry.size else None)
        return archive

    def test_regular_files_extract(self):
        with tempfile.TemporaryDirectory() as temp:
            root = Path(temp)
            target = root / 'application'
            target.mkdir()
            archive = self.archive(root, [('./', tarfile.DIRTYPE, b''), ('./storage/a', tarfile.REGTYPE, b'private')])
            extract(archive, target, 100)
            self.assertEqual((target / 'storage/a').read_bytes(), b'private')
            self.assertEqual((target / 'storage/a').stat().st_mode & 0o777, 0o600)

    def test_archive_traversal_links_devices_duplicates_and_limit_rejected(self):
        cases = [
            [('..//escape', tarfile.REGTYPE, b'x')],
            [('/absolute', tarfile.REGTYPE, b'x')],
            [('link', tarfile.SYMTYPE, b'')],
            [('link', tarfile.LNKTYPE, b'')],
            [('device', tarfile.CHRTYPE, b'')],
            [('same', tarfile.REGTYPE, b'x'), ('./same', tarfile.REGTYPE, b'y')],
            [('big', tarfile.REGTYPE, b'x' * 11)],
        ]
        for members in cases:
            with self.subTest(members=members), tempfile.TemporaryDirectory() as temp:
                root = Path(temp)
                target = root / 'application'
                target.mkdir()
                with self.assertRaises((ValueError, FileExistsError)):
                    extract(self.archive(root, members), target, 10)
                self.assertFalse((root / 'escape').exists())

    def check_sql(self, text):
        with tempfile.TemporaryDirectory() as temp:
            path = Path(temp) / 'backup.sql'
            path.write_text(text)
            return validate_sql(path)

    def test_typical_dump_with_quoted_private_text_and_conditional_comments(self):
        text = """-- Dump metadata
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
CREATE TABLE `users` (`id` bigint NOT NULL, `text` text, `salary` decimal(10,2));
LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'mail@example.test; DROP DATABASE production; /* hi */',12.50),(2,'it\\'s okay',-1.2e3);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
"""
        self.assertEqual(self.check_sql(text), 7)

    def test_sql_cross_schema_stored_code_global_and_client_commands_rejected(self):
        for text in [
            'USE production;', 'DROP DATABASE production;',
            'INSERT INTO `production`.`users` VALUES (1);',
            'CREATE TABLE `x` (`id` int, FOREIGN KEY (`id`) REFERENCES `production`.`users` (`id`));',
            '/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `evil` BEFORE INSERT ON `x` FOR EACH ROW SET @x=1 */;',
            'CREATE EVENT `e` ON SCHEDULE EVERY 1 SECOND DO SELECT 1;',
            'SET GLOBAL event_scheduler=ON;', 'SET @x=LOAD_FILE(\'/etc/passwd\');',
            'GRANT ALL ON *.* TO `x`;', 'source /tmp/inject;', '\\! touch /tmp/inject',
            'INSERT INTO `x` SELECT * FROM `users`;', 'DELIMITER $$',
            "INSERT INTO `x` VALUES ('unterminated);",
        ]:
            with self.subTest(text=text), self.assertRaises(ValueError):
                self.check_sql(text)


if __name__ == '__main__':
    unittest.main()
