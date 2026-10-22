# Private staging restore

The owner reported downloading backup `20261022-215145-f92ca979` and the separate keys. This is an owner report, not a cryptographically verified off-host copy.

`cpanel-restore-staging.yml` restores exactly that backup into the empty independent `dropoqux_jwtc_stage` database and `/home/dropoqux/jwtc-private-staging/20261022-215145-f92ca979/application`. It uses existing SSH/staging secrets. A main push touching this workflow or restore helpers also starts the operation. All commands run only after PHP syntax and restore-guard tests pass.

Guards:

- Existing live configuration is read without booting Laravel. Staging identity must differ, be empty, and have only USAGE globally plus grants on the exact escaped staging schema; roles/wildcard schema access/grant option are refused.
- cPanel verifies document roots, private destination, quota, and available disk space. Destination must be new; existing or partial restores are never overwritten.
- Both encrypted and decrypted archive SHA256 values must match the retained manifest. Stored-object backups require separate review.
- A streaming extractor permits only ordinary files/directories, rejects traversal, duplicates, links, special files, and oversized contents, and creates private permissions. Every restored file hash must match the original snapshot.
- The copied production `.env` is replaced with a minimal private staging environment preserving the original APP_KEY and using only staging DB credentials. No SMTP credentials are copied into this environment. Framework, queues, scheduler and web processes remain stopped. Saved database SMTP profiles remain private data and require separate runtime isolation before application testing.
- The SQL guard permits ordinary mysqldump table/data/session statements only. It rejects qualified schema references, stored code, database switches, global operations and client commands. The import uses the restricted staging user and noninteractive binary mode without force.
- Re-exported staging SQL must exactly match original executable SQL lines after removing only standalone metadata comments and blank lines. Table count must match. Private temporary plaintext dumps/archives are removed only after success.

Success produces a private `restore-report.json`; it does not change the original backup manifest. Failures retain partial private staging outputs for investigation and never delete tables automatically. Do not rerun over a partial restore. No live application files, database rows, keys, feature flags or public document roots are changed. This rehearsal does not deploy the release candidate, run its migrations, prove browser flows or make the application production ready.
