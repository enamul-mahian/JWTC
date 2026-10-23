<?php
ini_set('display_errors','0');ini_set('log_errors','0');
try {
 $home=realpath(getenv('HOME'));$root=realpath('.');$base=$home.'/jwtc-private-staging/20261022-215145-f92ca979';$input=$base.'/candidate-input-portal-global-ui';$target=$base.'/candidate-unpacked-portal-global-ui';
 if($root!==$home.'/joingallagher.uk'||is_link($input)||realpath($input)!==$input)throw new RuntimeException('Root');
 if(!is_file($input.'/release-manifest.json')){echo "PASS: no obsolete candidate to reset\n";exit(0);}
 $m=json_decode(file_get_contents($input.'/release-manifest.json'),true,64,JSON_THROW_ON_ERROR);
 if(($m['commit']??'')!=='6924653527f7e31880a5b1ea571aa21c2563ae09'){echo "PASS: retained candidate already differs from obsolete build\n";exit(0);}
 if(!preg_match('/^jwtc-[a-f0-9]{40}\.tar\.gz$/',$m['package'])||!hash_equals($m['sha256'],hash_file('sha256',$input.'/'.$m['package'])))throw new RuntimeException('Archive mismatch');
 $expected=[];foreach($m['files'] as $f)$expected[$f['path']]=$f['sha256'];
 if(is_dir($target)){
  if(is_link($target)||realpath($target)!==$target)throw new RuntimeException('Target');
  $files=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($target,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);
  foreach($files as $f){if($f->isLink())throw new RuntimeException('Symlink');if($f->isDir())continue;$p=substr($f->getPathname(),strlen($target)+1);if($p!=='.extraction-complete'&&(!isset($expected[$p])||!hash_equals($expected[$p],hash_file('sha256',$f->getPathname()))))throw new RuntimeException('Unknown extracted bytes');}
  foreach($files as $f){if($f->isDir())rmdir($f->getPathname());else unlink($f->getPathname());}rmdir($target);
 }
 if(!rename($input.'/release-manifest.json',$input.'/retained-build-6924653527f7e31880a5b1ea571aa21c2563ae09.json'))throw new RuntimeException('Receipt');
 if(!unlink($input.'/'.$m['package']))throw new RuntimeException('Regenerable archive');
 echo "PASS: obsolete CI candidate cache released; GitHub artifact, staging application, backups and rollback files retained\n";
} catch(Throwable $e){echo 'FAIL: generated candidate reset stopped '.get_class($e)."\n";exit(1);}
