#!/usr/bin/env python3
"""Preserve older immutable rollback code in verified private archives."""
import hashlib,json,os,pathlib,subprocess,sys,tarfile
ALLOWED={'app','config','database','resources','routes','vendor','bootstrap','composer.json','composer.lock','artisan'}
def compact(previous):
 previous=pathlib.Path(previous)
 if previous.is_symlink() or previous.resolve()!=previous or not previous.is_dir():raise ValueError('unsafe previous')
 expected={};directories=[]
 for item in previous.rglob('*'):
  relative=item.relative_to(previous).as_posix()
  if item.is_symlink() or relative.split('/')[0] not in ALLOWED:raise ValueError('non-code path')
  if item.is_dir():directories.append(item);continue
  if not item.is_file():raise ValueError('non-file')
  expected[relative]=(hashlib.sha256(item.read_bytes()).hexdigest(),item.stat().st_size,item.stat().st_mode&0o777)
 archive=previous.parent/'previous-code-verified.tar.gz';temp=previous.parent/'previous-code-verified.tar.gz.part'
 if archive.exists() or temp.exists():raise ValueError('archive already exists')
 try:
  with tarfile.open(str(temp),'w:gz',dereference=True) as tar:
   for directory in sorted(directories):tar.add(str(directory),arcname=directory.relative_to(previous).as_posix(),recursive=False)
   for relative in sorted(expected):tar.add(str(previous/relative),arcname=relative,recursive=False)
  os.chmod(str(temp),0o600)
  found=set();found_dirs=set();expected_dirs={p.relative_to(previous).as_posix():p.stat().st_mode&0o777 for p in directories}
  with tarfile.open(str(temp),'r:gz') as tar:
   for member in tar.getmembers():
    if member.isdir():
     if member.name not in expected_dirs or member.mode&0o777!=expected_dirs[member.name]:raise ValueError('archive directory')
     found_dirs.add(member.name);continue
    if not member.isfile() or member.name not in expected or member.name in found:raise ValueError('archive member')
    sha,size,mode=expected[member.name]
    if member.size!=size or member.mode&0o777!=mode or hashlib.sha256(tar.extractfile(member).read()).hexdigest()!=sha:raise ValueError('archive bytes')
    found.add(member.name)
  if found!=set(expected) or found_dirs!=set(expected_dirs):raise ValueError('archive missing file')
  os.rename(str(temp),str(archive))
  for relative,(sha,size,mode) in expected.items():
   item=previous/relative
   if item.is_symlink() or hashlib.sha256(item.read_bytes()).hexdigest()!=sha:raise ValueError('source changed')
  for relative in expected:(previous/relative).unlink()
  for directory in sorted(directories,key=lambda p:len(p.parts),reverse=True):directory.rmdir()
  previous.rmdir()
  return len(expected),len(directories),archive.stat().st_size
 except Exception:
  # The archive/source are retained on any unexpected state; never bulk-delete.
  raise
if __name__=='__main__':
 try:
  os.umask(0o077);home=pathlib.Path.home();root=home/'joingallagher.uk';parent=home/'jwtc-private-deployments'
  if pathlib.Path.cwd()!=root or parent.is_symlink() or parent.resolve()!=parent:raise ValueError('root')
  previous=sorted([p for p in parent.glob('*/previous') if p.is_dir()],key=lambda p:p.parent.name,reverse=True)
  counts=[0,0,0]
  for p in previous[2:]:
   n,d,b=compact(p);counts=[counts[0]+n,counts[1]+d,counts[2]+b]
  print('VERIFIED_ROLLBACK_ARCHIVES',json.dumps({'files_preserved':counts[0],'directories_compacted':counts[1],'compressed_bytes':counts[2],'latest_full_rollbacks_retained':min(2,len(previous))}))
  uapi=next((p for p in ['/usr/bin/uapi','/usr/local/cpanel/bin/uapi','/bin/uapi'] if os.path.isfile(p)),None)
  if uapi:
   result=json.loads(subprocess.check_output([uapi,'--output=json','Quota','get_quota_info']).decode())
   quota=result.get('result',{}).get('data',{})
   print('SAFE_ACCOUNT_QUOTA',json.dumps({k:v for k,v in quota.items() if k in ['megabyte_limit','megabytes_used','under_megabyte_limit','under_quota_overall','under_inode_limit','inode_limit','inodes_used','inodes_remain']}))
 except Exception as e:
  print('FAIL: rollback compaction stopped; verified archives and original bytes retained',type(e).__name__);sys.exit(1)
