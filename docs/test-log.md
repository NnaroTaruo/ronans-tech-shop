# Test log

Dates use Asia/Manila: Stages 1-2 on November 3, Stage 3 on November 4; Stage 4 authorized November 7 and verified November 8, 2026. Record actual evidence. “Not tested” includes incomplete checks. A partial result does not pass a whole acceptance check. The current table is updated by the completed Stage 5 run on November 11, 2026; earlier stage notes are historical.

## PDF section 17: ten acceptance checks

| ID | Acceptance check from PDF | Result | Evidence / note |
| --- | --- | --- | --- |
| 01 | Search, category filter, and both price sorts work together. No-match and invalid product states are handled. | Pass | Stage 5 browser clicks Apply with mixed-case mouse search/category/both sorts, asserts ordered prices, no-match and invalid/inactive details; storefront HTTP suite also passed. |
| 02 | Cart merges duplicate products, persists after refresh, and calculates the PHP 325.50 example correctly. | Pass | Stage 5 real browser Add clicks merge IDs; reload preserves LocalStorage and PHP 325.50. Quantity update (445.50), remove (360.00), clear and invalid native quantity constraints exercised. |
| 03 | Empty checkout, invalid customer fields, and invalid/excessive quantities are blocked. | Pass | Stage 5 browser verifies disabled empty checkout, field errors and first-error focus. Backend suites reject malformed/excessive quantities and invalid required/email/phone/payment/notes inputs. |
| 04 | Inactive/out-of-stock items cannot be ordered. Checkout detects price or availability changes before saving. | Pass | Browser shows changed-price explanation, disables save until explicit re-review and displays unavailable detail states; checkout suite rejects zero-stock/inactive and stock/active/price changes, including two-process last-unit contention. |
| 05 | A successful order shows confirmation, appears in admin, reduces stock, and clears the cart. Failed saves preserve the cart. | Pass | Stage 5 forces a transactional failure and checks real LocalStorage unchanged/no saved order; retry disables submit, redirects to saved Pending confirmation, clears cart, decrements stock and shows matching admin number/items/PHP 325.50. |
| 06 | Admin product/category changes appear on the customer site after refresh. Referenced records cannot be deleted. | Pass | Stage 5 clicks admin category/product forms, adds/edits/inactivates/reactivates and verifies refreshed customer price/description/stock. Duplicate names and referenced/assigned deletions show errors; admin suite covers successful unused deletion. |
| 07 | Valid statuses save; invalid transitions fail. Eligible cancellation restores stock once, including after refresh. | Pass | Browser advances the complete chain and cancels a Confirmed order with unchanged stock on refresh. Admin suite checks all invalid destinations, final states, Pending cancellation, repeat/two-process contention and partial-restore rollback. |
| 08 | Completed-order totals and unique-email customer counts match the underlying orders. | Pass | Final browser dataset: 13 products, 2 orders (1 Cancelled, 1 Completed), 0 Pending, 1 normalized customer, sales 32550 centavos; latest customer name and Completed spending match. Admin suite separately verifies contact/order count/grouping and zero states. |
| 09 | Existing order prices/names remain unchanged after product edits. Data persists as described for the chosen approach. | Pass | Browser edits name/price through admin and verifies original saved Desk Cable Clips/PHP 120.00. Browser refresh and admin-suite PHP stop/restart with a fresh login preserve the saved rows and snapshots. |
| 10 | Customer/admin screens work at approximately 375 px and 1280 px widths. No clipped controls, horizontal page scrolling, or major console errors. | Pass | 34 live screenshots and browser measurements at 375/1280: no horizontal page overflow, broken images, missing visible field labels or runtime exceptions. Mobile menu/filter disclosures and Tab/Enter skip-focus sampled; screenshot review recorded below. Not physical-phone or exhaustive accessibility certification. |

## Stage 1 technical evidence

Recorded automated verification against the local development project on November 3, 2026. Ronan's personal manual run is still pending:

| Check | Result | Executed evidence |
| --- | --- | --- |
| PHP syntax | Pass | `php -l` passed for all 11 shareable PHP source files. `public/index.php` was checked again after the final markup edit. |
| Integer money, input rejection, escaping, CSRF helpers | Pass | `php tests/foundation.php`: 7 PASS messages, including the exact `PHP 325.50` result; excessive decimal precision, negatives, scientific notation, empty, comma-formatted, and over-limit inputs rejected. |
| Tailwind build | Pass | `npm.cmd run build:css` completed, Tailwind 4.3.4; lockfile and compiled CSS generated. |
| Initial schema/seed | Pass | `php scripts/setup-database.php`: 3 categories, 12 products, 1 hashed demo admin, 0 orders. Server reports `10.4.32-MariaDB`. |
| Repeat seed preserves data | Pass | Running the same setup command again printed “Existing data preserved. No seed rows were changed.” |
| Reset confirmation guard | Pass | `php scripts/setup-database.php --reset-demo-data` exited 1 with the required-confirmation message. A subsequent DB count read remained 12 / 3 / 1 / 0. |
| Explicit demo reset | Pass | Ran `php scripts/setup-database.php --reset-demo-data --confirm=ronan_tech_shop_dev` only against the newly created fictional development dataset. Seed succeeded; database checks then passed again. |
| Database constraints and data | Pass | `php tests/database.php` passed: seed minimums, inactive and active-zero-stock examples, all 5 tables InnoDB, all 12 image references resolve, password hash verifies, case-insensitive duplicate rejected, assigned category deletion rejected, negative stock rejected. Test mutations rolled back. |
| HTTP response and assets | Pass | PowerShell `Invoke-WebRequest` returned 200 for `/`, compiled CSS, keyboard SVG, and favicon; HTML contained “Database connected” and seed counts. |
| Private files outside document root | Pass | HTTP requests for `/config/local.php`, `/app/database.php`, `/database/schema.sql`, `/scripts/setup-database.php`, `/tests/database.php` each returned 404 while serving `-t public`. |
| Git ignores | Pass | `git check-ignore` confirmed `config/local.php`, `tmp/sessions`, and `node_modules` are ignored. Local repository initialized; no commit/push/publication. |
| Stage 1 layout at 375 / 768 / 1280 px | Pass | Installed-browser layout inspector completed final run with 0 FAIL / 0 WARN, no console errors at each width. All 6 final fold/page screenshots visually reviewed. No clipping/horizontal overflow reported. This is the setup shell only; AC10 is still Not tested for the full application. |
| Source design checks | Pass | Shared-source lint: 0 FAIL / 0 WARN; Impeccable detector returned `[]`. |

Browser evidence is local/ignored at `tmp/layout-review/report.json` and its desktop/tablet/mobile JPGs. The inspector initially timed out inside the sandbox; its approved run outside the sandbox succeeded. The in-app preview host was unavailable, so no claim is made that the page opened beside the chat. The first successful layout pass found a long paragraph; it was capped at a readable width and the final pass had no findings.

The setup-shell inspector waives only its marketing-specific picture/video/motion/oversized-focal requirements (`few-pictures`, `no-footage`, `still-page`, `focal-point`). This is a teaching/setup layout, not the final storefront; adding campaign images and decorative animation would exceed Stage 1. Overflow, clipping, headline wrapping and contrast checks were retained.

Screenshot critique, scoped to Stage 1: clear store identity, real setup state, consistent left edges, readable mobile stacking and a working-link structure. Design 7/10, usability 8/10, creativity 6/10, content 8/10; weighted 7.2/10. The paragraph-length issue was fixed; there are no further material visual fixes identified for this foundation. This is an internal self-review, not independent user approval. Main limits remain the deliberately unbuilt storefront/admin flows and Ronan's pending keyboard/manual run.

**Still needs Ronan:** run the checks in his VS Code terminal; start/open the page himself; Tab through skip/navigation/action links; test the three anchor links; confirm his understanding before Stage 2. Oracle MySQL itself has not been tested. All ten complete PDF acceptance checks remain Not tested until their actual flows exist.

## Stage 2 technical evidence — November 3, 2026

Ronan reported “the stage 1 is now good” before this work began. That confirms progression to Stage 2; it is not a detailed report that every individual manual check passed.

| Check | Result | Evidence / scope |
| --- | --- | --- |
| Existing foundation | Pass | `php tests/database.php` passed before Stage 2 changes. Existing database/seed records and config were preserved; no schema/reset was needed. |
| Syntax/build | Pass | PHP syntax checks passed for all source files after implementation; final changed cart PHP was checked again. `node --check public/assets/js/cart.js` and `npm.cmd run build:css` succeeded. |
| Catalog and cart server logic | Pass | `php tests/storefront.php`: combined mixed-case search/category/both price sorts, literal wildcard/no-match behavior, inactive/missing IDs, active zero stock, related-product rules, duplicate merge, invalid/excessive quantities, empty/invalid cart, and exact authoritative totals. Forged client prices are ignored. |
| Current-data reads | Pass | The PHP test changed price/active status inside a transaction, verified the quote sees the changes, rolled back, then verified the original PHP 325.50 total. These are cart reads, not checkout review/transaction tests. |
| JavaScript storage helpers | Pass | `node tests/storefront.mjs`: duplicate merge, serialized IDs/quantities only, storage round trip, invalid quantities/additions, maximum-entry handling, integer money, malformed-storage preservation and storage-write failure. Uses a test storage object, not a real browser refresh. |
| Actual HTTP pages/API | Pass | Same Node command: 11 visible products; inactive name absent, zero-stock name present; combined filters and both sorts; no-match page; invalid filter 400 and invalid product 404; authoritative API 32550 subtotal/total; invalid quantities 422; invalid CSRF 419; wrong method 405; empty/zero-stock cart invalid. |
| Product listing layout | Pass | Installed-browser inspection at 375px and 1280px completed: 0 FAIL, 2 WARN, no console errors. All 5 produced JPGs visually reviewed. No clipped controls or horizontal overflow found in those listing screenshots. |
| Real browser cart interaction and other page layouts | Not tested | Add/update/remove/clear, refresh persistence, populated-cart layout, home/details keyboard/mobile flow still need Ronan. Computer Use stopped because it could not verify the active browser URL; no further UI input was attempted. No full browser-flow Pass is claimed. |

Listing evidence is ignored/local at `tmp/stage2-list-review/report.json` and its JPGs. Its two warnings are retained with scope-specific reasons: 4 of 11 product cards start on the desktop first screen because this is a scrolling catalog, and the 36px/30px customer heading deliberately follows the established readable store hierarchy rather than a dense admin-screen title. Shared-source lint has one harmless price-formatting warning for `padStart(2, '0')`; two decimal places are required. The source detector initially flagged a template image without `src`; a bundled fallback source was added before final handoff.

Listing screenshot review: product images, names, category, prices, availability and actions align; filters stack in one column on mobile; the zero-stock state is visibly disabled. Initial shared category illustrations remain a documented visual limitation. Design 7/10, usability 7/10, creativity 6/10, content 8/10 (weighted 6.9/10), scoped only to the listing captures. The header/filter stack takes most of the first mobile viewport; product browsing continues by scrolling. Full customer/admin AC10 remains Not tested.

No orders were saved, stock reserved/reduced, admin features implemented, public deployment performed, or submission sent in this stage. All complete acceptance rows retain Not tested until their full behavior is verified. Exact manual steps are in README under “Stage 2 checks and manual walkthrough.”

## Stage 3 technical evidence — November 4, 2026

Ronan requested “lets continue”, authorizing the next stage. This does not assert that he performed each Stage 2 manual test. Project documentation remains attributed to Ronan Antoque; test execution below is automated evidence.

| Check | Result | Executed evidence / limits |
| --- | --- | --- |
| Syntax/build | Pass | All PHP source files passed `php -l` after implementation. Both browser JS modules passed `node --check`; Tailwind build succeeded. |
| Stage 2 regression | Pass | `php tests/storefront.php` and `node tests/storefront.mjs` passed after refactoring the cart quote calculation for reuse with locked rows. |
| Checkout validation | Pass | `node tests/checkout.mjs`: empty cart; zero/negative/fraction/string/bool/over-limit quantities; inactive/out-of-stock/missing products; blank and Unicode-whitespace name; invalid email/phone/address/payment/notes; wrong CSRF/method; changed submitted cart all blocked without saving an order. |
| Atomic successful save | Pass | Same suite: two Clips + one Cloth saved at 32550 centavos despite a forged client total. Two order-item snapshots, Pending status, normalized email and exact stock reductions checked. COD, Bank Transfer and E-Wallet each used in successful test saves. Notes `0` preserved. |
| Re-review on changes | Pass | Price change returned 409 with changed price, another save was rejected, explicit review enabled the save. Stock reduction still sufficient for requested quantity also returned 409. Inactivation after review returned 409 with invalid quote. |
| Repeated request / lost response | Pass | Same successful key returned the same number for repeated/concurrent requests without further stock changes. A review carrying the pending key resolved the already saved order. Cross-session use of that key returned 419. |
| Partial-write failure | Pass | A trigger failed the later item after an earlier line/stock write. The HTTP result was 503 without a saved confirmation; complete order/item/stock state matched its pre-submit state. Removing the trigger allowed the same-key retry to save once. |
| Actual overselling race | Pass | Two independent local PHP server processes shared one isolated test DB. A trigger held the first transaction open while separate-session checkouts competed for the last mouse. Results were 200 and 409; exactly one order, final stock 0. |
| Confirmation privacy/escaping/history | Pass | Original session received 200; another session with the copied URL got 404. HTML-special customer name was escaped. After a product rename/price change, confirmation still showed original `Desk Cable Clips` / PHP 120.00 snapshots and original PHP 325.50 total. |
| Purchased-cart cleanup helper | Pass | JavaScript subtraction preserved later additions and emptied an unchanged purchased cart; malformed quantities rejected. Completion-marker checks prevented repeated subtraction when receipt removal failed and preserved the cart when its write failed. Uses injected test storage objects; real LocalStorage/SessionStorage/redirect interaction remains manual. |
| Checkout layout | Pass | Final installed-browser inspection at 375/1280px: 0 FAIL, 2 documented WARN, no console errors; 4 final JPGs reviewed. Empty cart now shows PHP 0.00 and saving disabled, rather than an endless Checking label. |
| Saved-confirmation layout | Pass | 375/1280px: 0 FAIL, 1 heading-size WARN, no console errors; 4 JPGs reviewed. Uses real server-rendered HTML from a fictional isolated-test saved order; scripts removed for static QA, so this is not a browser-purchase claim. |
| Source design checks | Pass | Changed-source lint: 0 FAIL/0 WARN; detector returned `[]`. |
| Interactive browser purchase | Not tested | Actual field focus/errors, populated review/re-review, saving button, redirect, post-save storage cleanup, refresh/back behavior and keyboard flow need Ronan's manual run. No personal browser test is attributed to him. |

Final isolated test run passed against `ronan_tech_shop_test_01ca41554b2e`, then reported that database removed. Test PHP servers on 18031/18032 were stopped. Earlier successful runs used/removed their own random databases as well. No test order was written to the development shop. No schema migration or seed/reset was needed. The fixture helper refuses non-test database names.

Layout evidence is local/ignored under `tmp/stage3-checkout-review/` and `tmp/stage3-confirmation-review/`. The confirmation source snapshot is under `tmp/stage3-review/`. Retained warnings: the 36px/30px headings follow the established customer-store hierarchy; the compact summary intentionally ends above the much longer delivery form on desktop. Stretching it would add a large blank panel. Both states were reviewed without clipping or horizontal overflow. Scoped internal screenshot review: design 7/10, usability 7/10, creativity 6/10, content 8/10 (weighted 6.9/10); interactive usability remains unscored. No independent review is implied.

At the Stage 3 handoff, the table had automated backend Pass results for 03/04; all others were still pending. Stage 4 evidence below supersedes that state without asserting a personal browser run.

## Stage 4 technical evidence — November 8, 2026

Ronan authorized Stage 4 on November 7; execution continued after midnight Asia/Manila. Tests use fictional data in a separately named database. No test order/admin mutation was made in the development shop.

| Check | Result | Executed evidence |
| --- | --- | --- |
| Protected routes and login | Pass | `node tests/admin.mjs`: every protected route rejects guest GET and POST; wrong/unknown login, invalid CSRF and GET logout rejected. Correct login rotates cookie; logout rotates cookie and removes protected access. |
| Catalog/category management | Pass | HTTP add/edit/inactivate/customer refresh; image/name/description/price/stock/flag/category validation; blank/case-duplicate category rejection; assigned category and referenced product deletion blocked; unused product and empty category deletion succeeds after confirmation. |
| Stale edit and snapshots | Pass | Product form opened before checkout cannot overwrite its stock decrement. A later admin rename/price edit leaves original saved order name/PHP 85.50 intact. |
| Order transitions | Pass | Exact forward chain succeeds. All invalid destinations from each forward state, repeats, skipped/backward changes and changes from Completed/Cancelled rejected. Status CSRF tested. |
| Cancellation once | Pass | Pending and Confirmed cancellation succeed. Missing confirmation rejected. Two separate PHP processes/sessions compete for one Confirmed cancellation: one redirect success, one 409; stock rises by purchased quantity once. Refresh/repeat preserves stock. |
| Atomic failure / inactive stock | Pass | Trigger fails restoration on a later product after an earlier increment; response 503 and full products/orders/items state matches before the attempt. Retry succeeds after removing the trigger, including restoration for a product made inactive. |
| Summary accuracy | Pass | 13 products includes inactive; 3 orders includes 2 Cancelled; 0 Pending / 1 Completed; 2 normalized emails; Completed sales 8550 centavos. Repeat email uses latest submitted name/contact, order count 2, Completed spending 8550. |
| Restart persistence | Pass | Stop/restart one test PHP server, log in anew, and verify all three orders and original saved snapshot/price remain. |
| Regression checks | Pass | `php tests/foundation.php`, `php tests/storefront.php`, `node tests/storefront.mjs` and `node tests/checkout.mjs` all succeeded after Stage 4 changes. Checkout retains actual two-process last-unit/rollback/retry coverage. |
| Syntax/build/source | Pass | All project PHP files passed `php -l`; admin JS syntax and CSS build passed. UI source detector returned `[]`; shared source lint reported 0 FAIL / 0 WARN. |
| Admin layouts | Pass, scoped | Installed headless-browser inspector: dashboard, products, product form, categories, orders, final-order detail, customers and login, plus Pending-order detail, at 375px/1280px. No failed measurements or console errors. Final screenshot sheets reviewed; corrected mobile order identity wrapping, numeric alignment and centered login. |
| Interactive browser/admin keyboard | Not tested | Native form submission/focus, menu/filter disclosure interaction, delete/status controls, back button and complete purchase/cart clearing remain Ronan's manual walkthrough and Stage 5 acceptance work. Screenshot snapshots do not execute real form submissions. |

Final admin suite used `ronan_tech_shop_test_28933c5ba27e`, then removed it and stopped its servers (18041/18042). A prior test-runner cleanup wait after restarting PHP was fixed; the first leftover test database `ronan_tech_shop_test_7c7d5529f1d7` was explicitly removed, and later runs cleaned up normally. Checkout regression used/removed `ronan_tech_shop_test_398036bf7330` on 18031/18032.

Layout sources are actual server-rendered HTML from isolated test orders, saved under ignored `tmp/stage4-review/`; their shared assets/scripts load from the local preview. Reports and JPGs are in `tmp/stage4-<screen>-review/`. These are local QA artifacts, not the final submission screenshot set. Nine inspected states retained six warnings: content-sized dashboard and order-detail panels have unequal bottoms (three); a two-customer table and short login form leave vertical space (two); the inspector assumes a sidebar for the centered login and reports unused canvas (one). Login has symmetric margins; screenshots showed readable wrapping and no clipping/page overflow. No dummy content or stretched empty panels were added to satisfy those heuristics.

Scoped internal screenshot review: design 7/10, visible usability 8/10, creativity 6/10, content 8/10 (weighted 7.1/10). Actual interactive usability is still unverified; no independent review or personal test attribution is implied. The simple shop identity, real figures, product thumbnails and explicit state actions meet the chosen functional brief. Long mobile lists need scrolling; keyboard/error/delete interaction remains pending.

The current table records backend/integration Pass results for 03, 04, 06, 07, 08 and 09. Checks 01, 02, 05 and 10 still require complete browser interaction evidence. Stage 5 will finish that coverage. PHP preview on 8000 and local MariaDB are left running for Ronan; test servers/databases were removed. No reset, commit, push, deployment or email was performed.

## Stage 5 final evidence - November 11, 2026

The table at the top now records **Pass for all ten checks**, based on executed backend plus live browser evidence. Earlier pending notes above describe the previous stage handoffs; this section supersedes their current-state limitations without attributing personal manual testing to Ronan.

| Verification | Executed result |
| --- | --- |
| Foundation/database/storefront | Existing PHP checks and Node storefront HTTP/storage checks passed again. Database mutations in foundation/storefront checks rolled back. PHP syntax passed for all 36 source files; cart/checkout/admin JavaScript syntax and Tailwind build passed. |
| Checkout regression | `node tests/checkout.mjs` passed: changed-price/stock/active review, invalid inputs, atomic failure, retry ownership/duplicates, snapshots and actual two-process last-unit contention. Test database `ronan_tech_shop_test_f0459b28d6dc` removed. |
| Admin regression | `node tests/admin.mjs` passed again, including after the final count/date markup fixes. Final database `ronan_tech_shop_test_97d9c929d7ff` removed. Coverage includes access/CSRF, CRUD integration, deletion restrictions, all transitions, exactly-once cancellation, contention, partial-restore rollback, summaries and PHP restart. |
| Live browser interaction | Final local headless-browser run passed all ten recorded checks in `ronan_tech_shop_test_1cef25bd3f81`; removed that database and its PHP server on 18051. Browser used a temporary profile and reduced-motion preference. Real hit-tested mouse clicks, native forms, server responses, browser LocalStorage and refresh were exercised. This is not static HTML QA. |
| Failed and successful purchase | Forced failure after the first item left no order and preserved the real cart. Retry disabled saving, saved Pending/32550 centavos, reduced Clips by two and Cloth by one, redirected to private confirmation, and cleared purchased LocalStorage. Matching order appeared in admin. |
| Review and validation | Empty checkout disabled; required errors displayed and first invalid input focused. Changed-price explanation blocked saving until explicit review. Quantity constraints and backend unavailable/stock/field validation remained active. |
| Admin/customer integration | Live category/product add/edit/inactivate/reactivate and protected deletion forms; Confirmed cancellation and refresh; full forward sequence to Completed; two orders/one normalized email/latest name, Completed sales exactly PHP 325.50; product edits left saved names/prices unchanged. |
| Desktop/mobile evidence | **34 live PNGs** at 1280px and 375px, 17 named surfaces per width. No measured page overflow, broken loaded images, missing visible field labels or runtime exceptions. Initial captures reviewed; final corrected product images, phone order dates and customer count reviewed again. |
| Keyboard/mobile controls | Tab reached the visible skip link and Enter moved to main; focus outline measured solid. Phone admin menu and filter disclosures opened with real clicks; logout returned a protected route to login. This is a sample, not exhaustive accessibility testing. |
| Source UI check | Final changed-source detector returned `[]`. No new application dependencies. Presentation fixes only: singular product/order/customer counts and separate order-number/date lines. |

The first browser-driver attempts needed correction for scrolling/hit targeting, a serialized status assertion and opening the collapsed mobile menu before logout. Those runs were not counted as complete Pass and removed their isolated databases. Visual QA also found full-page capture had not triggered some offscreen lazy images; capture now loads/decodes them before the screenshot. No screenshot was retouched and no application stock/transaction rule was bypassed to obtain a Pass.

Final report: `docs/evidence/browser-results.json`; screenshots/inventory: `docs/evidence/README.md`. Fictional evidence dataset has 13 products and a fourth category created by CRUD testing; this intentionally differs from the preserved development seed. Confirmation screenshots show the current Completed status after admin processing; the immediate post-save Pending state was asserted during interaction. Screenshot text/date/amounts match that isolated run.

Visual review: shared light/green store identity, aligned desktop grids, labelled mobile rows, readable prices, empty states and disabled zero-stock actions. Minor grammar/date-wrap fixes were confirmed. Long phone product lists intentionally scroll. Content-sized short panels and forms retain empty canvas instead of fabricated content. Scoped self-review: design 7/10, visible usability 8/10, creativity 6/10, content 8/10 (weighted 7.1/10). No independent rating or complete accessibility certification is implied.

**Remaining delivery work:** Ronan's own rehearsal, actual 3-5 minute video recording/upload, reviewer-accessible GitHub publication/access, replacing link placeholders and sending. Physical-phone testing, exhaustive keyboard/screen-reader checks and Oracle MySQL execution remain unverified. These are disclosed separately from the ten executed assessment checks. Normal PHP preview on 8000 and MariaDB remain running; the development database was not reset. No external message or deployment occurred.
