import importlib.util,io,pathlib,tarfile,tempfile,unittest,os
spec=importlib.util.spec_from_file_location('global_extract',pathlib.Path(__file__).with_name('extract_global_candidate.py'));mod=importlib.util.module_from_spec(spec);spec.loader.exec_module(mod)
class ExtractTest(unittest.TestCase):
 def package(self,base,name='app/example.php',data=b'<?php example',kind=None):
  archive=base/'code.tar.gz'
  with tarfile.open(archive,'w:gz') as tar:
   m=tarfile.TarInfo(name);m.size=len(data)
   if kind:m.type=kind;m.linkname='/etc/passwd';m.size=0
   tar.addfile(m,io.BytesIO(data))
  return archive
 def test_resume_shares_identical_code_without_changing_source_mode(self):
  with tempfile.TemporaryDirectory() as d:
   p=pathlib.Path(d);source=p/'source';target=p/'target';(source/'app').mkdir(parents=True);(source/'app/example.php').write_bytes(b'<?php example');os.chmod(source/'app/example.php',0o644);(target/'app').mkdir(parents=True);(target/'app/example.php').write_bytes(b'<?ph')
   mod.extract(self.package(p),target,1024,source)
   self.assertEqual(os.stat(source/'app/example.php').st_ino,os.stat(target/'app/example.php').st_ino);self.assertEqual(os.stat(source/'app/example.php').st_mode&0o777,0o644)
 def test_different_new_code_is_independent(self):
  with tempfile.TemporaryDirectory() as d:
   p=pathlib.Path(d);source=p/'source';target=p/'target';(source/'app').mkdir(parents=True);(source/'app/example.php').write_bytes(b'old')
   mod.extract(self.package(p),target,1024,source);self.assertEqual((source/'app/example.php').read_bytes(),b'old');self.assertNotEqual(os.stat(source/'app/example.php').st_ino,os.stat(target/'app/example.php').st_ino)
 def test_archive_traversal_and_symlinks_rejected(self):
  for name,kind in [('../escape',None),('/escape',None),('app/link',tarfile.SYMTYPE)]:
   with self.subTest(name=name),tempfile.TemporaryDirectory() as d:
    p=pathlib.Path(d);(p/'source').mkdir()
    with self.assertRaises(ValueError):mod.extract(self.package(p,name=name,kind=kind),p/'target',1024,p/'source')
 def test_unknown_partial_or_extra_file_is_retained_and_rejected(self):
  for extra in [False,True]:
   with self.subTest(extra=extra),tempfile.TemporaryDirectory() as d:
    p=pathlib.Path(d);(p/'source').mkdir();(p/'target/app').mkdir(parents=True);file=p/('target/unrecognized' if extra else 'target/app/example.php');file.write_bytes(b'not package data')
    with self.assertRaises(ValueError):mod.extract(self.package(p),p/'target',1024,p/'source')
    self.assertEqual(file.read_bytes(),b'not package data')
 def test_expanded_size_limit(self):
  with tempfile.TemporaryDirectory() as d:
   p=pathlib.Path(d);(p/'source').mkdir()
   with self.assertRaises(ValueError):mod.extract(self.package(p),p/'target',1,p/'source')
if __name__=='__main__':unittest.main()
