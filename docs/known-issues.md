# Known issues and delivery status

Project author: **Ronan Antoque**. Updated November 11, 2026.

## Deliberate assessment limits

- Runs locally with PHP 8.2 and XAMPP MariaDB 10.4.32. The schema targets MySQL-compatible InnoDB; an actual Oracle MySQL run has not been performed.
- Fictional payment selections only: Cash on Delivery, E-Wallet and Bank Transfer. No gateway, payment collection, shipping integration, tax, discount or refund system.
- Guest checkout; no customer account or public tracking page. Saved confirmation requires the original browser session; admin can access saved orders after authenticated login.
- Initial seed images are shared category SVG illustrations, not product-specific photography. Footer contacts are fictional and social links are labelled generic platform links.
- Public demo credentials are for local assessment use. Production security hardening (such as account recovery, persistent login rate limits, deployment/session operations and monitoring) is outside the assessment scope. Do not expose this local setup as a production store.
- Cart storage is per browser/profile and site origin. Clearing browser data removes the cart; concurrent edits in several tabs are not atomic. A cart does not reserve stock. Server checkout locks and rechecks stock before saving.
- Storage restrictions can prevent purchased-item cleanup. Confirmation shows a warning if that happens; review the cart before ordering again. Retry keys prevent saving the same owned checkout twice.
- HTTPS product images may be entered by admin, but external availability/licensing is not controlled by this project. Seed assets are bundled and work offline.

## Remaining delivery actions

The source and documentation can be reviewed locally. Actual video recording/upload, GitHub repository publication/reviewer access, final replacement of email link placeholders and sending remain Ronan's actions. They are not marked complete by automated tests. No public deployment is necessary for the local-demo-plus-video route.

The final test log distinguishes executed automated checks from unverified personal/device testing. Browser emulation at 375px is not a test on a physical phone. Keyboard sampling is not a complete accessibility certification. Do not claim either in the submission.

See `submission-checklist.md`, `demo-guide.md`, `evidence/README.md` and `test-log.md` for exact evidence and next actions. No known blocker should be hidden: if a final check fails, update this file and the corresponding test-log row before submitting.
