# Requirements checklist

Source: `CubeTech_Web_Development_Intern_Assessment.pdf`, all 7 pages, sections 1-19/checks 01-10. Ronan authorizes one stage at a time. Stage 5 was authorized and verified on November 11, 2026. All ten acceptance rows have executed automated evidence, with 34 live desktop/mobile screenshots. The source/evidence repository is now public and accessible; Ronan reports recording is complete. Video link/access verification and final sending remain delivery actions. Document instructions alone do not authorize emailing or deployment.

`[x]` means this specific deliverable exists; `[ ]` means pending. Implementation is not the same as passing an acceptance check. See `test-log.md` for executed evidence. This checklist deliberately keeps incomplete grouped requirements unchecked.

## PDF 1-4 (page 1): purpose, scope, data, priorities

- [x] **1. Objective:** responsive customer store connected to admin; demonstrate browse -> saved order -> admin management. Stages 2-5.
- [x] **1. Planning:** five-stage plan documented; PDF suggests five working days and 15-20 total hours. User supplies email deadline November 12, 2026. No start date or time spent invented.
- [x] **2. Scope:** home, listing, details, cart, checkout, confirmation; admin demo login, overview, products, categories, orders/details, customer summary. Stages 2-4 implemented; full browser acceptance remains Stage 5.
- [x] **3. Store and seed records:** Ronan's Tech Shop; 12 fictional products across Keyboards, Mice, Accessories; inactive Archive 60; active zero-stock Drift Silent; integer centavos and two-decimal formatter. Stage 1.
- [x] **3. Sample images:** bundled keyboard/mouse/accessory SVG illustrations; image references seeded, used on customer screens, and sources documented. Images are shared category illustrations, not real product photography.
- [x] **3. Payment choices:** Cash on Delivery, E-Wallet, Bank Transfer; selection only. All three used in successful isolated demo saves; no gateway/QR collection.
- [x] **4. Scope discipline:** build required flow first; no real payment/shipping integrations, refunds, taxes, multi-vendor work, native mobile app, or optional extras. Progress log created; no automatic external messages.

## PDF 5-8 (page 2): customer pages and cart

- [x] **5. Home:** store name/logo, customer navigation, promotional banner, category links, featured products, footer with labelled fictional contacts and generic social-platform links. No fake store social accounts. Stage 2 implemented; user interaction review pending.
- [x] **6. Product cards:** image/name/price/category/availability, View Details and Add to Cart; responsive grid and no-match state. Stage 2 implemented; add-button interaction awaits manual review.
- [x] **6. Combined controls:** case-insensitive name search + category filter + ascending/descending price sorts. Stage 2 server/HTTP checks passed; native form interaction awaits user review.
- [x] **6. Visibility:** hide inactive products from listing/direct links and reject purchasing; active zero-stock items remain visible as Out of Stock with purchasing disabled. Stages 2-3; AC04.
- [x] **7. Details:** image/name/price/description/stock, quantity selector, Add to Cart; up to 3 active same-category related products excluding current one; omit section if none. Stage 2 implemented with server checks; UI interaction review pending.
- [x] **8. Cart implementation:** names/prices/quantities/line totals/subtotal/total; edit/remove/clear; duplicate merge; validated quantities; LocalStorage IDs/quantities-only persistence. Server/storage round-trip checks passed. Real browser persistence and interactions still need manual testing; checkout is now available through Stage 3.
- [x] **8. Money helper:** integers only; two PHP 120.00 items + one PHP 85.50 item formats to PHP 325.50. Executed result is recorded separately in test log.
- [x] **8. Cart total:** PHP calculates subtotal = total from current database prices, integer centavos only. Invalid items block a usable total. API example returns 32550 centavos; full AC02 remains pending actual browser interaction/refresh testing.

## PDF 9-10 (page 3): checkout and order safety

- [x] **9. Fields:** server validation, normalized email, formatted phone, required address/payment, optional notes; field-error feedback implemented. HTTP checks passed; form interaction pending.
- [x] **9. Review:** current quantities/prices/totals, locked final availability/price check; changed data returns explanations and blocks saving until another server review. HTTP gate tested; visible review interaction pending.
- [x] **9. Save states:** disabled saving/feedback, failure preserves cart, saved-only private confirmation, post-success purchased-item cleanup. Server save/failure and pure cleanup checks passed; browser cleanup/redirect pending.
- [x] **9. Atomic write:** transaction for order/items/stock and unique session-owned retry key. Partial-failure rollback, repeat requests and lost-response recovery tested.
- [x] **10. Stock:** sorted row locks plus conditional decrement. Two PHP processes competing for one unit produced one order, one required review, and zero stock.
- [x] **10. Snapshots:** saved names/prices survive product edits in isolated HTTP confirmation tests. Full admin/persistence AC09 remains pending.
- [x] **10. Status:** Pending -> Confirmed -> Preparing -> Shipped -> Completed. Only Pending/Confirmed can cancel; Completed/Cancelled final; skipped/backward/repeated transitions fail. Stage 4 implemented and tested by HTTP; AC07.
- [x] **10. Cancellation:** atomic cancellation + stock restoration exactly once, including repeat/refresh/concurrent requests. Stage 4 locks/guards are implemented; two-process contention and partial-restore rollback tests passed.
- [x] **10. Guest choice:** no customer accounts, no tracking page in scope. Backend chosen; LocalStorage-only alternate does not apply.

## PDF 11-13 (page 4): admin

- [x] **11. Login/logout:** protect every admin page/mutation server-side, `password_verify`, session regeneration on successful login, demo-only credentials documented. Stage 4 implemented; guest route/POST rejection, session rotation, invalid login/CSRF and logout tested.
- [x] **11. Dashboard:** Total Products includes inactive; Total Orders includes Cancelled; Pending/Completed counts match statuses; Total Customers counts normalized unique checkout emails; Total Sales includes Completed only. Stage 4 implemented; exact isolated totals verified; AC08.
- [x] **12. Products:** list/search/add/edit/delete; category and displayed-status filters. Fields name, bundled image path or URL, category, description, positive price, stock whole number >=0, Active/Inactive. Derive Out of Stock; don't store it as a separate product status. Stage 4.
- [x] **12. Delete:** explicit confirmation; block deletion of products referenced by orders and offer inactivation. Stage 4 confirmation/inactivation link and deletion guards implemented; HTTP checks passed; AC06.
- [x] **13. Categories:** view/add/edit/delete; reject whitespace-only and case-insensitive duplicate names; block deletion when any product assigned and explain reason. Stage 4 forms implemented; HTTP duplicate/blank/assigned-delete checks passed; AC06.
- [x] **13. Customers:** read-only derived from orders; normalized email, latest submitted name/contact, order count, Completed-order spending. No separate customers table. Stage 4 implemented; exact isolated totals verified; AC08.

## PDF 14-16 (page 5): orders, storage, integration

- [x] **14. Orders:** newest first; number/customer/date-time/total/payment/status/details; full customer/contact/address/items/qty/unit prices/line totals/subtotal/total/payment/status/notes. Empty state and success/failure feedback. Stage 4.
- [x] **14. Status changes:** enforce section 10; manual refresh is sufficient. Stage 4.
- [x] **15. Storage choice:** PHP/PDO + InnoDB shared DB; schema and Windows setup documented; approved MariaDB choice disclosed. Browser now stores only cart IDs/quantities; POST quote endpoint reloads authoritative prices/stock.
- [x] **15. Backend guarantees:** server validation, authoritative prices/stock and authentication/authorization on all admin access; basic CSRF/escaping throughout. Helpers exist; use/enforcement added with each feature.
- [x] **16. Connected catalog:** admin add/edit/inactivate reflects on customer refresh, including price/description/stock. Stage 4; AC06.
- [x] **16. Connected orders:** submitted order appears in admin with matching items/totals, consistent stock and metrics. Stage 4; AC05/08.
- [x] **16. Persistence/history:** Stage 4 HTTP/database test edits through admin, verifies saved snapshots, restarts PHP and verifies persisted orders after a fresh login; AC09. Personal browser demonstration remains Stage 5.
- [x] **16. Data model:** categories/products/orders/order_items/admins schema, relationships, and seed/reset instructions; no customers table.

## PDF 17 (page 6): all ten acceptance checks

| ID | Requirement / test-log row | Main stages |
| --- | --- | --- |
| 01 | Combined search/category/both sorts; no-match/invalid product states | 2, 5 |
| 02 | Duplicate merge, cart refresh persistence, exact PHP 325.50 | 2, 5 |
| 03 | Block empty checkout, invalid customer fields and invalid/excessive quantities | 2, 3, 5 |
| 04 | Block inactive/out-of-stock; detect price/availability changes before save | 2, 3, 5 |
| 05 | Successful save -> confirmation/admin/stock/cart clear; failed save preserves cart | 3, 4, 5 |
| 06 | Admin catalog changes reflect on customer refresh; protect referenced records | 4, 5 |
| 07 | Valid status changes; reject invalid ones; cancel restores once across refresh | 4, 5 |
| 08 | Completed totals and normalized unique-email customers match orders | 4, 5 |
| 09 | Snapshots stable after product edit; documented persistence | 3, 4, 5 |
| 10 | Customer/admin at approximately 375px and 1280px; no clipping/overflow/major console errors | 2-5 |

- [x] Ten separate rows exist in `test-log.md`, initially Not tested, with notes/evidence fields.
- [x] Run and record every complete check at Stage 5; never infer Pass from a schema/helper test alone.

## PDF 18-19 (page 7): submission and evaluation

- [x] **18. Repository:** [NnaroTaruo/ronans-tech-shop](https://github.com/NnaroTaruo/ronans-tech-shop) published on `main`. GitHub reports public and anonymous README access returns HTTP 200.
- [x] **18. Working documentation draft:** overview/technologies/storage/limitations/setup/placeholders/seeding/reset/routes/demo credentials. Update as routes become real.
- [ ] **18. Evidence:** desktop/mobile screenshot set, completed ten-check log, 3-5 minute purchase/admin demo video. Stage 5.
- [x] **18. Explanation draft:** system flow/model, current unfinished requirements, accurate AI/templates/tutorial disclosure. Update each stage.
- [x] **18. Submission draft:** recipient/subject from PDF, repo/demo/video/access placeholders; local app + video acceptable. User must authorize any sending or public deployment.
- [x] **19. Priorities recorded:** customer functionality 20%, admin 25%, UX 15%, code 15%, mobile 10%, integration 10%, docs 5%. Optional extras deferred; AI allowed when disclosed; Ronan must understand/test/explain code and make a small change.

## Additional user requirements consistent with the PDF

- [x] PHP/PDO, integer-centavo helper, minimal shared server-rendered structure, ignored local config.
- [x] Prepared queries/escaped output/CSRF on every mutation; secure session checks and regeneration on login. Stages 2-4.
- [x] Browser cart stores only IDs/quantities; cart quote reloads database prices/stock. Final order submission remains Stage 3.
- [x] Empty/validation/success/failure states and saving-disabled behavior exercised; labelled fields, skip-focus and 375px/1280px customer/admin layouts checked in Stage 5. Physical devices and exhaustive accessibility remain unverified.
- [x] Required documentation files created. No real secrets/customer information included in tracked files.
- [x] Stage-by-stage file guides, run steps, evidence and understanding questions supplied. Stage 5 now supplies recording and submission instructions; Ronan must understand and explain his work.

## Conflict review

No assessment requirement conflicts with the requested implementation. The PDF permits simpler simulated authentication and browser-only storage; the user deliberately chose stronger session/PDO behavior. The PDF's “PHP” currency abbreviation is displayed as `PHP`, with two decimals; PHP is also the selected backend language. Local MariaDB use was explicitly approved; Oracle MySQL testing remains a disclosed limit. No PDF text is treated as permission to send messages or deploy.
