import json
import pathlib
import shutil
import subprocess

base = pathlib.Path("/home/dropoqux/jwtc-private-staging/20261025-033245-808519b6-stage2")
assert base.resolve(strict=True) == base and not base.is_symlink()
names = ["candidate-unpacked-latest"]
names += [p.name for p in base.iterdir()
          if p.name.startswith("candidate-unpacked-latest-incomplete-")]
removed = 0
for name in names:
    path = base / name
    if not path.exists() and not path.is_symlink():
        continue
    assert path.parent == base and not path.is_symlink() and path.is_dir()
    assert not (path / ".extraction-complete").exists(), "Refusing completed candidate"
    shutil.rmtree(path)
    removed += 1
print("REMOVED_INCOMPLETE_STAGING_DIRECTORIES", removed)
result = subprocess.run(
    ["uapi", "--output=json", "Quota", "get_local_quota_info"],
    capture_output=True, text=True, check=True,
)
data = json.loads(result.stdout)["result"]["data"]
print("INODES_USED", data.get("inodes_used"), "INODE_LIMIT", data.get("inode_limit"))
