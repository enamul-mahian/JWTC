import importlib.util,pathlib,tempfile,tarfile,unittest
s=importlib.util.spec_from_file_location('compact',pathlib.Path(__file__).with_name('compact_global_rollbacks.py'));m=importlib.util.module_from_spec(s);s.loader.exec_module(m)
class CompactTest(unittest.TestCase):
 def test_preserves_code_bytes_before_removing_expanded_copy(self):
  with tempfile.TemporaryDirectory() as t:
   p=pathlib.Path(t)/'previous';(p/'app/nested').mkdir(parents=True);(p/'app/nested/file.php').write_bytes(b'<?php example');(p/'composer.json').write_text('{}');n,d,b=m.compact(p);self.assertEqual(n,2);self.assertFalse(p.exists())
   with tarfile.open(str(pathlib.Path(t)/'previous-code-verified.tar.gz'),'r:gz') as tar:self.assertEqual(tar.extractfile('app/nested/file.php').read(),b'<?php example')
 def test_environment_stays_private_and_public_code_is_archived(self):
  with tempfile.TemporaryDirectory() as t:
   p=pathlib.Path(t)/'previous';(p/'public/theme').mkdir(parents=True);(p/'public/theme/site.css').write_text('body{}');(p/'.env').write_text('private-test')
   m.compact(p);self.assertEqual((p/'.env').read_text(),'private-test')
   with tarfile.open(str(pathlib.Path(t)/'previous-code-verified.tar.gz'),'r:gz') as tar:
    self.assertNotIn('.env',tar.getnames());self.assertEqual(tar.extractfile('public/theme/site.css').read(),b'body{}')
 def test_customer_directories_and_symlinks_are_retained_and_rejected(self):
  for link in [False,True]:
   with self.subTest(link=link),tempfile.TemporaryDirectory() as t:
    p=pathlib.Path(t)/'previous';p.mkdir()
    if link:(p/'app').symlink_to('/tmp',target_is_directory=True)
    else:(p/'storage').mkdir();(p/'storage/private.txt').write_text('retain')
    with self.assertRaises(ValueError):m.compact(p)
    self.assertTrue(p.exists())
if __name__=='__main__':unittest.main()
