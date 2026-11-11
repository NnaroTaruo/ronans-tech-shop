# Progress log

## November 3, 2026 — Stage 1

**Scope:** first stage only, per Ronan's pasted request. Existing folder inspected before writing: empty, no repository or local project instructions found. All seven PDF pages read as text and rendered for visual inspection.

**Completed implementation:** requirements mapping for PDF sections 1-19 and all ten acceptance checks; Windows setup guide and five-stage plan; five InnoDB tables; 12 fictional products across three categories; inactive/zero-stock examples; hashed demo-admin seed; reusable PHP layout/PDO/escaping/session/CSRF/integer-money helpers; bundled SVG sample assets and disclosure; repeatable non-overwriting seed and explicit demo reset; runnable foundation/database checks.

**Environment:** PHP 8.2.12 with PDO MySQL, Git 2.51.1, Node 24.12.0; XAMPP's MariaDB client 10.4.32. Database server initially stopped. Ronan approved MariaDB locally while retaining MySQL-compatible SQL. No secrets requested through chat.

**Verification completed:** all PHP syntax checks; foundation money/escaping/CSRF checks; Tailwind build; actual MariaDB schema/seed; repeat seed preservation; guarded and explicit reset; seed/image/hash/InnoDB/category/negative-stock database checks; HTTP/private-file checks; Git ignores; shared layout inspected and visually reviewed at 375/768/1280px with final 0 FAIL / 0 WARN and empty console-error lists. See `test-log.md` for commands, scope, and evidence. All ten full PDF acceptance checks remain Not tested. Keyboard interactions and Ronan's own local run remain pending.

**Next:** Ronan runs Stage 1 and explains the three learning questions. Only after confirmation, Stage 2 adds customer home/catalog/details/search/filter/sort/cart.

**Setup state:** XAMPP MariaDB started after filesystem approval; newly created development DB seeded; ignored local config prepared; local Git repository initialized; CSS compiled. No Stage 1 implementation blocker remains. PHP verification preview is stopped at handoff; MariaDB remains running for Ronan. Oracle MySQL compatibility is intended but has not been executed against an Oracle MySQL server. In-app browser host unavailable; approved installed-browser inspection supplied layout evidence instead.

**Time spent:** not recorded. Do not turn the PDF's 15-20 hour suggested budget into a claim of actual work time.

**Deadline:** user-provided assessment email deadline November 12, 2026. No public deployment, GitHub publication, or email sent.

### Files added and their purpose

| Files | Purpose |
| --- | --- |
| `README.md` | Overview, Windows setup/run/reset, five-stage plan, storage/limits, route status, demo credentials, learning questions. |
| `docs/requirements-checklist.md` | PDF sections 1-19, all ten checks, and additional user rules. |
| `docs/test-log.md` | Ten acceptance statuses plus separate executed foundation evidence. |
| `docs/progress-log.md` | Work completed, next step, blockers, file guide; no invented hours. |
| `docs/system-flow.md` | Current request flow, five-table relationships, planned order/admin flow. |
| `docs/ai-disclosure.md` | Actual AI help, documentation references, original demo asset sources. |
| `config/config.example.php` | Safe tracked placeholders for a local database connection. |
| `config/local.php` (ignored) | Working local-only database settings; not for GitHub. |
| `app/bootstrap.php` | Load helpers, timezone, session/cookie and response defaults. |
| `app/database.php` | One PDO connection, prepared-statement defaults and strict SQL/UTC setup. |
| `app/functions.php` | Escaped HTML, safe money parsing/formatting, CSRF token helpers. |
| `app/views/header.php`, `app/views/footer.php` | Reusable semantic layout/navigation and fictional contact details. |
| `public/index.php` | Stage 1 database readiness/seed-count page and build-plan sections. |
| `database/schema.sql` | Five InnoDB tables, constraints and relationships. |
| `database/seed.php` | Twelve fictional products with string prices and image references. |
| `scripts/setup-database.php` | CLI-only table creation, non-overwriting seed, guarded demo reset. |
| `tests/foundation.php`, `tests/database.php` | Small executed money/security/database checks; DB test mutations roll back. |
| `resources/css/app.css`, `public/assets/css/app.css` | Tailwind source/theme and compiled browser stylesheet. |
| `public/assets/favicon.svg`, `public/assets/images/*.svg` | Original keyboard identity and three fictional category illustrations. |
| `package.json`, `package-lock.json` | Official Tailwind CLI dependency/build commands and locked dependency versions. |
| `.gitignore` | Exclude local config, sessions, temporary files, dependencies and likely secret files. |
| `PRODUCT.md`, `DESIGN.md` | Confirmed brief and the implemented shared-layout design choices for later stages. |

Ignored `tmp/` also contains PDF review renders, temporary npm cache, and layout-inspection evidence. These are local working files, not submission assets.

## November 3, 2026 — Stage 2

**User confirmation:** Ronan reported Stage 1 is good. Proceeded only to the next agreed stage. No further user setup information was needed.

**Completed:** customer home with demo banner/category links/featured products; combined case-insensitive search/category/both price sorts; active-only product details with quantity/stock and up to three related products; shared product card; persistent IDs/quantities-only cart with duplicate merging, quantity editing, removal, clearing, safe current-price totals and invalid/unavailable recovery; same-origin CSRF-protected quote API. Original setup page preserved at `/setup.php`. Shared navigation now uses working customer routes. Schema and existing seed data preserved.

**Verification:** foundation/database checks; PHP/JS syntax; CSS build; storefront PHP and Node HTTP/storage checks passed. Product listing inspected at 375/1280px: 0 failed measurements, two documented design warnings, no console errors; five screenshots reviewed. Actual browser cart clicks/refresh and home/details/populated-cart mobile/keyboard checks remain pending. Computer Use stopped because it could not verify the active browser URL; no further app input was attempted. See test log for precise evidence and limitations.

**Next:** Ronan follows the README Stage 2 walkthrough and understanding questions, then confirms before Stage 3 checkout/transaction work. Checkout remains visibly unavailable; no order/admin behavior has been built early.

**Blockers / limits:** no server implementation blocker. Browser interaction verification requires the manual walkthrough. Shared category images are initial demo illustrations; Oracle MySQL still untested. Concurrent cart edits in different tabs use browser LocalStorage rather than atomic locking; use one tab for edits. Stock is not reserved by a cart.

**Time spent:** not recorded. No actual time entry inferred from the suggested assessment budget.

### Stage 2 file guide

| Files added/changed | Purpose |
| --- | --- |
| `app/catalog.php` | Prepared active-product queries, related products, safe image references, server cart normalization/stock/money quotes. |
| `app/views/product-card.php` | Reused product display and add form for home/list/related sections. |
| `public/index.php` | Working customer home replacing the original setup landing page. |
| `public/setup.php` | Preserved Stage 1 setup screen. |
| `public/products.php` | Validated combined GET filters, sorting and empty/error states. |
| `public/product.php` | Active product details, quantity form and related products; unavailable 404 state. |
| `public/cart.php` | Cart page, summary, empty/loading states, row template and explicit unavailable-checkout message. |
| `public/api/cart.php` | Read-only POST JSON quote endpoint with CSRF/method/payload/error guards. |
| `public/assets/js/cart.js` | LocalStorage persistence, duplicate merge, checked add/update/remove/clear, feedback, counts and refresh requests. |
| `app/views/header.php`, `app/views/footer.php` | Customer navigation/cart count, shared script/CSRF metadata, labelled generic social links. |
| `resources/css/app.css`, `public/assets/css/app.css` | Responsive product grid, forms, cart rows, disabled/error/focus states and rebuilt CSS. |
| `tests/storefront.php`, `tests/storefront.mjs` | Database-backed catalog/cart checks, HTTP checks and isolated JS storage checks. |
| `package.json` | Mark browser/test JavaScript as ES modules; no dependencies added. |
| `README.md`, all five `docs/*.md`, `PRODUCT.md`, `DESIGN.md` | Current routes, Stage 2 flow, actual evidence/limits, file guide, manual steps, neutral assistance disclosure, and confirmed progression. |

Local PHP verification server stopped at handoff; MariaDB remains available. No commit, push, deployment, or submission email performed.

## November 4, 2026 — Stage 3

**Authorization:** Ronan requested continuation. Proceeded to Stage 3 only; this does not invent a detailed Stage 2 manual test report.

**Completed:** guest checkout fields and server validation; current order review stored in PHP session; explicit re-review after price/name/stock/active changes; sorted product row locks; one transaction for order/item snapshots/stock reductions; unique order number and idempotent retry key; Pending confirmation restricted to the placing browser session; post-success cart cleanup with per-tab retry recovery. Cart now enables checkout for valid nonempty quotes. Existing schema/seed/config preserved.

**Verification:** PHP/JS syntax and CSS build; Stage 2 regression checks; isolated checkout suite passed including partial-write rollback, changed-data review gates, duplicate/lost-response recovery, cross-session privacy/escaping, immutable snapshots, and actual two-process last-unit contention. Test databases/servers removed after use. Checkout form/empty state and saved-confirmation snapshot inspected at 375/1280px, with no failed measurements/console errors. Warnings and screenshot scope recorded in test log.

**Manual work still needed:** Ronan performs a complete demo purchase and verifies field feedback, current order summary, saving disabled state, redirect, purchased-item clearing, confirmation refresh/privacy and populated mobile/keyboard layout. These are not marked as personally completed.

**Next:** only after confirmation, Stage 4 adds admin authentication, product/category CRUD, order status/cancellation, customer summary and dashboard. No admin implementation was included early.

**Blockers / limitations:** no server implementation blocker. Original-session confirmation access is intentional. Browser storage failure can leave purchased items in the cart and shows a warning; user must review the cart before another checkout. Oracle MySQL remains untested; local MariaDB used. API and isolated DB tests do not constitute a complete interactive-browser pass.

**Time spent:** not recorded. No elapsed-time estimate invented. Deadline remains November 12, 2026 as supplied by Ronan.

### Stage 3 file guide

| Files | Purpose |
| --- | --- |
| `app/checkout.php` | Customer validation, canonical cart, saved-order loading, change comparison, locked transactional save and retry resolution. |
| `app/catalog.php` | Share quote calculation between normal catalog reads and locked checkout product rows. |
| `public/api/checkout.php` | CSRF-protected session-owned review/save actions, re-review gate, safe errors and saved confirmation access. |
| `public/checkout.php` | Labelled guest form, order summary, field/change/loading/retry feedback. |
| `public/confirmation.php` | Original-session-only saved order, customer/payment/status/items/totals and escaped snapshots. |
| `public/assets/js/checkout.js` | Review/save interactions, field feedback, pending retry key and successful purchased-item cleanup. |
| `public/cart.php`, `public/assets/js/cart.js` | Valid nonempty checkout action, disabled while prices are being checked. |
| `app/views/header.php` | Update no-JavaScript guidance to include checkout. |
| `app/database.php` | Guarded process-environment override for separately named test databases. Normal config remains unchanged. |
| `tests/checkout.mjs`, `tests/checkout-fixture.php` | Isolated HTTP/database/race/rollback/retry tests with scoped setup and cleanup. |
| `public/assets/css/app.css` | Rebuilt Tailwind classes for the new screens. |
| `README.md`, five `docs/*.md`, `PRODUCT.md`, `DESIGN.md` | Current routes, flow/relationships, truthful evidence/limits, neutral assistance record and Stage 3 learning/run steps. |

Local PHP preview stopped at handoff; MariaDB left running for the manual run. No commit, push, deployment, or submission email performed.

## November 7-8, 2026 — Stage 4

**Authorization:** Stage 4 was approved on November 7. Work continued into November 8, Asia/Manila. Implementation covered Stage 4 only; Stage 5 awaits confirmation. Previous personal manual-test results remain unverified.

**Completed:** real PHP session admin login/logout; protected reads/mutations and CSRF checks; dashboard; product search/category/displayed-status filtering, add/edit and confirmed deletion; category management with blank/duplicate/assigned-product errors; newest-first orders and snapshot details; strict status transitions; transactional cancellation restoring stock once; read-only normalized-email customers with latest name/contact and Completed spending. Product edit revisions reject stale forms after checkout/other edits. Bundled images and HTTPS references supported. Existing shop data/config/schema preserved; no migration/reset needed.

**Verification:** `node tests/admin.mjs` passed in a random isolated database and cleaned up. Includes guest-route and guest-POST rejection, invalid login/CSRF, session rotation/logout, CRUD integration, deletion guards, stale-stock form rejection, immutable snapshots, all valid/invalid transitions, Pending/Confirmed cancellation, two-process cancellation contention, forced mid-restore rollback, inactive-product restoration, exact summaries and persistence after PHP restart. Checkout regression suite passed. PHP/JS syntax and CSS build passed. Admin test runner cleanup was corrected after its first restart run; that leftover test database was explicitly removed and subsequent runs removed their own databases. Tests did not reset the development store.

**Design:** extend the established palette/type in Operate mode. Accent ladder, seed 517947245, open-plane labelled sidebar; no redraw. Questions: dashboard—what needs review; products/categories—what can be managed; orders—what is the next allowed action; customers—who ordered and what Completed spending belongs to each email. Face: existing keyboard/shop identity, green tint and actual product thumbnails. Figures are queries of fictional test data, not invented dashboard numbers. Responsive tables become labelled rows; mobile menu/filters fold. Login uses a centered form. Inspection evidence/limits belong in the test log.

**File guide:** `app/admin.php` holds shared access/validation/queries/status transactions; `app/views/admin-*.php` supplies the shell; `public/admin/` implements login/logout/dashboard/products/product/categories/category/orders/order/customers; `admin.js` supplies submit/menu feedback; CSS adds responsive admin layouts; `app/catalog.php` validates image references; `tests/admin.mjs` and the guarded fixture supply isolated checks. README contains the detailed file guide and exact manual run instructions. All required docs updated with neutral assistance disclosure and Ronan as author.

**Next:** Ronan runs the Stage 4 manual walkthrough and answers its three questions. After confirmation, Stage 5 completes browser acceptance, remaining mobile/keyboard checks, fixes, screenshot evidence, demo script and submission draft. No deployment or sending is authorized.

**Limits / time:** complete interactive browser purchase/admin-form and keyboard testing remains pending; local MariaDB was used, not Oracle MySQL. Shared seed category illustrations and public demo credentials remain documented limitations. No server implementation blocker. Time spent was not recorded. No commit/push/publication/email performed.

## November 11, 2026 - Stage 5

**Authorization:** Stage 5 was approved to prepare the project for the November 12 assessment deadline. The PDF's seven pages were rechecked, especially sections 17-19. Work covered final testing/fixes, evidence and submission preparation; it does not authorize sending or public deployment.

**Completed:** reran foundation/database/storefront/checkout/admin suites; completed the live browser customer-to-admin flow; recorded all ten acceptance checks with actual automated evidence; captured 34 desktop/mobile screenshots and their JSON report; corrected singular admin counts and phone order date wrapping. Existing schema/config/development orders preserved. Created timed demo guide, screenshot inventory, known limits, submission checklist, recipient/subject-correct email draft and shareable source package. All required existing docs updated; author remains Ronan Antoque with neutral truthful assistance disclosure.

**Verification:** browser tested actual Add/update/remove/clear, refresh persistence, PHP 325.50, field errors, price-change re-review, failed-save preservation, successful save/cleanup/confirmation/admin appearance, CRUD, cancellation/status chain, summaries and snapshot preservation. Backend suites add forged-request, concurrency/rollback and PHP-restart coverage. Both 375px and 1280px layouts checked; no runtime exceptions/page overflow. Sampled keyboard skip focus and mobile menu/filter interaction passed. Final admin suite and browser test databases/servers were removed. See test log/report for names and exact scope. Initial driver/capture issues were corrected before final evidence; failed/incomplete attempts were not presented as complete passes.

**Files:** `public/admin/{products,orders,order,customers}.php` receives small copy/date presentation fixes; compiled CSS rebuilt. `docs/evidence/` supplies live PNGs, report and inventory. `docs/demo-guide.md`, `submission-checklist.md`, `submission-email.md` and `known-issues.md` supply final delivery guidance. README, requirements/test/progress logs, system flow, assistance record and product context reflect Stage 5. `.gitignore` excludes generated output packages. No new application package or framework.

**Next / delivery blockers:** Ronan rehearses and records the required 3-5 minute demo, uploads it, creates/shares the GitHub repository with reviewer access, fills real repository/video links in the draft and sends it before the assessment email's exact cutoff. No GitHub remote exists yet and no video has been recorded here. Local app plus video is allowed by the PDF; localhost is not a public link. The guides state these outstanding actions explicitly.

**Limits / time:** Oracle MySQL, physical phones and exhaustive assistive-technology coverage unverified; initial images remain shared category graphics and credentials remain demo-only. No known application blocker was observed in the executed acceptance checks. Time spent was not recorded. PHP preview/MariaDB left running for rehearsal. No reset, Git commit/push/publication, hosted deployment or email sending performed.
