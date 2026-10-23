#!/usr/bin/env python3
"""Resume a verified CI extraction, sharing byte-identical immutable code files."""
import hashlib, os, pathlib, sys, tarfile

def extract(archive, target, ceiling, source):
    target=pathlib.Path(target);source=pathlib.Path(source)
    if target.is_symlink() or target.resolve()!=target or source.is_symlink() or source.resolve()!=source: raise ValueError('unsafe root')
    target.mkdir(mode=0o700,exist_ok=True)
    with tarfile.open(archive,'r:gz') as tar:
        members=tar.getmembers(); total=0;seen=set();expected=set()
        for m in members:
            p=pathlib.PurePosixPath(m.name)
            if not m.name or p.is_absolute() or '..' in p.parts or '\\' in m.name or str(p) in seen:raise ValueError('unsafe member')
            seen.add(str(p))
            if not (m.isdir() or m.isfile()) or m.size<0 or m.size>64*1024*1024:raise ValueError('unsafe kind/size')
            total+=m.size
            if total>ceiling:raise ValueError('expanded limit')
            expected.add(str(p))
        for existing in target.rglob('*'):
            relative=existing.relative_to(target).as_posix()
            if existing.is_symlink():raise ValueError('existing symlink')
            if existing.is_file() and relative not in expected and relative!='.extraction-complete':raise ValueError('unknown existing file')
        shared=0;written=0
        for m in members:
            dst=target/m.name
            if not dst.parent.resolve().is_relative_to(target):raise ValueError('parent escape')
            if m.isdir():dst.mkdir(mode=0o700,parents=True,exist_ok=True);continue
            dst.parent.mkdir(mode=0o700,parents=True,exist_ok=True)
            data=tar.extractfile(m).read()
            if len(data)!=m.size:raise ValueError('short member')
            digest=hashlib.sha256(data).digest()
            if dst.exists():
                current=dst.read_bytes()
                if hashlib.sha256(current).digest()==digest:
                    pass
                elif not data.startswith(current):raise ValueError('unknown partial bytes')
            candidate=source/m.name
            allowed=m.name.startswith(('app/','config/','database/','resources/','routes/','vendor/','public/theme/','public/pwa/','public/site-chat/','public/email-studio/','public/chat/')) or m.name in ('artisan','composer.json','composer.lock','bootstrap/app.php')
            share=allowed and candidate.is_file() and not candidate.is_symlink() and candidate.resolve().is_relative_to(source) and candidate.stat().st_uid==os.getuid() and candidate.stat().st_size==m.size and hashlib.sha256(candidate.read_bytes()).digest()==digest
            if share:
                if dst.exists() and os.stat(dst).st_ino==os.stat(candidate).st_ino:continue
                if dst.exists():dst.unlink()
                os.link(candidate,dst);shared+=1
            elif not dst.exists() or hashlib.sha256(dst.read_bytes()).digest()!=digest:
                if dst.exists():dst.unlink()
                with open(dst,'xb') as f:f.write(data)
                os.chmod(dst,0o600);written+=1
        print('SHARED_CANDIDATE_FILES',shared,'WRITTEN_FILES',written)

if __name__=='__main__':
    try:
        if len(sys.argv)!=5 or sys.argv[1]!='extract':raise ValueError('arguments')
        home=pathlib.Path.home();target=pathlib.Path(sys.argv[3]);source=home/'joingallagher.uk'
        if target!=home/'jwtc-private-staging/20261022-215145-f92ca979/candidate-unpacked-portal-global-ui':raise ValueError('target')
        extract(sys.argv[2],target,int(sys.argv[4]),source)
    except Exception as e:
        print('GUARD_ERROR',type(e).__name__,getattr(e,'errno',None),file=sys.stderr);sys.exit(1)
