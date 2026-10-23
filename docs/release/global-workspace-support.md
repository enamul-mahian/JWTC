# Global workspace, job filters and support release

Deployed 2026-10-23 UTC. Approved application source: ea31a4493b87fc26c38551bb33c162a02c54c195 (PR 19 after PR 18). CI run 39993592495 passed all four jobs and 176 tests, including MySQL/MariaDB migration checks, compiled Blade templates and asset checks.

Production deployment run: 39995437323, deployment 20261023-173205-5b17b280. Fresh encrypted backup: 20261023-173127-1790a620. Schema: 65 migrations. Legacy rows, uploaded files and original application key preserved. Email Studio and Support Center flags activated.

Public and applicant job search now share keyword/location/category/salary filtering and stable pagination. Five salary periods supported, including legacy case/whitespace. Applicant workspace and private modules share typography, forms, sidebar and buttons. Custom pagination does not depend on Bootstrap.

Support Inbox & Refunds exposes JoinGallagher/company recipients, scoped company replies, private staff notes, refund review requests and branded email notifications. Encrypted durable delivery records retain SMTP failures for administrator retry. Refund requests do not automatically transfer money. Email Studio provides formatted templates and SMTP profile controls.

Hosting inode quota blocked backup creation. Older rollback code was preserved in hash-verified private archives before compaction; latest two full rollback trees and private environments retained. 64,340 files and 9,903 directories archived; account then reported 269,134 of 300,000 inodes. Three local archive-preservation tests pass.

Production read-only rendering passed admin jobs/editor/applications/progress/workflow, chat schedule, employer job creation, applicant profile/search/password, locations, support inbox/compose and Email Studio. No customer records or outgoing messages were created. Logged-in browser appearance was not verified.

Live browser verification: Ireland returns 11 jobs; second page returns the final one, retaining location. Monthly returns four matching jobs and resets page. Pagination is one styled navigation without bullets. Actual screenshot captured.

Production mail inventory: zero saved SMTP profiles, no default profile, zero saved templates; environment host exists but username is absent. Mail delivery is not verified or claimed configured. Administrator must save valid SMTP credentials/default profile securely in Email Studio. Existing format content was not found in production saved templates; editor remains available for recreating/importing it.

Deployment workflow restored to manual dispatch after successful promotion.
