# System flow and data model

## Preserved Stage 1 flow

Browser -> `public/setup.php` (original Stage 1 index) -> `app/bootstrap.php` -> `db()` using ignored local config -> read product/category counts -> shared header/page/footer HTML. PHP sessions store their files under ignored `tmp/sessions/`; Stage 4 also uses these sessions for admin authorization.

`scripts/setup-database.php` (CLI only) -> create missing tables -> if data exists, preserve it -> otherwise start transaction -> insert categories/products/password hash -> commit. On a seed error, roll back row changes. MySQL schema creation commits implicitly and is not inside that transaction.

## The five tables in plain English

| Table | Purpose / relationship |
| --- | --- |
| `categories` | One category has many products. Its unique case-insensitive name rejects duplicates. |
| `products` | Each product belongs to one existing category. Holds current name/description/image, integer price, stock, active/featured flags. Active zero stock means Out of Stock, derived later in UI. |
| `orders` | One saved guest purchase, including checkout details, normalized email, payment selection, totals, status, unique order number and retry key. One order has many order items. |
| `order_items` | Each line links one order to one product, with quantity and saved name/price snapshots. A referenced product cannot be hard-deleted. Line total is integer unit price × integer quantity; no separate redundant line-total column. |
| `admins` | Demo admin email and `password_hash()` result. No plaintext password column. |

A **foreign key** points to an existing row in another table. For example, `products.category_id` points to `categories.id`. `RESTRICT` blocks deletions that would leave dangling references. There is no customers table: summaries group orders by normalized email. Existing order checkout fields preserve what was submitted on each order.

Money is whole centavos. `price_to_centavos('85.50')` returns `8550`. Two times `12000`, plus `8550`, equals `32550`; formatting produces `PHP 325.50`. No floating-point conversion is used. Product prices are bounded to PHP 9,999,999.99. Cart and checkout bound totals/multiplications to JavaScript's largest exact integer; orders use wider database columns.

## Current Stage 2 flow

Browser GET `/` or `/products.php` -> shared bootstrap -> `app/catalog.php` -> active product query -> reusable card view -> server-rendered HTML. Search, category and sort travel in one GET form. Category values and sort options are validated; name search uses the case-insensitive database collation and treats `%` / `_` literally. Unavailable product IDs return 404 without leaking inactive details.

Add/update click -> JavaScript reads LocalStorage IDs/quantities -> merges or changes quantity -> POST `/api/cart.php` with session CSRF header -> PHP validates integer IDs/quantities, reloads active product data, checks stock and integer totals -> JSON result -> JavaScript saves only IDs/quantities on successful validation. Loading the cart repeats the quote so visible names/prices/stock are current. Invalid lines show corrective feedback and prevent a usable total; invalid/unavailable saved items are not silently discarded.

Remove/clear are local cart edits; reload checks remaining prices. Empty cart displays an empty state. Cross-tab storage events refresh the display; simultaneous cart writes are not atomic, so use one tab for edits. Cart changes do not reserve stock. Stage 3 now enables checkout only for a valid nonempty cart.

The read-only cart quote API never inserts orders, changes stock, or accepts browser prices. It enforces CSRF/method/payload limits without exposing database exceptions. The separate checkout endpoint performs order review/save as described below.

## Stage 3 checkout — implemented

`checkout.js` -> POST `api/checkout.php` action `review` -> normalize/sort IDs and quantities -> authoritative quote -> store quote/retry key in PHP session -> display current order. Saving submits the same cart/key and customer fields. PHP validates required text, normalized email, phone formatting and payment choice, then calls `place_order()`.

`place_order()` -> resolve an existing order for this owned retry key -> begin transaction -> lock each product by increasing ID -> recheck any existing save -> compare locked data with server review. Changed data rolls back and requires a separate review; an immediate save retry is rejected. Matching data inserts order/item snapshots and conditionally reduces stock -> commit -> grant confirmation access in the session -> saved response. Errors roll back all order/item/stock changes. The unique retry key resolves duplicate requests or a lost response after commit. The original schema already provides the required fields; no migration was needed.

Browser cleanup runs only for a committed response or authorized saved confirmation with matching per-tab pending progress. It subtracts purchased quantities, keeps later additions, and removes pending progress; a completion marker prevents a second subtraction if receipt removal fails. The record contains only the key, cart IDs/quantities and completion flag; customer details stay in form memory/server requests. Failed saves preserve the cart. A cart-storage failure can leave ordered items, with a warning on the saved confirmation. Stage 5 verifies real browser cleanup after both failed and successful saves; personal rehearsal remains recommended.

Confirmation uses both the requested order number and its server-session access key. Other sessions get 404. It reads `orders` and `order_items` only; current product names/prices cannot rewrite history. Stage 5 also verifies a live browser purchase, saved redirect, refresh and cart cleanup with fictional isolated data.

## Stage 4 admin implementation

Browser -> protected `public/admin/*.php` -> `app/admin.php` -> `require_admin()` queries the existing admin ID before exposing data. Login checks a stored password hash and rotates session ID/token; logout is CSRF-protected POST and removes the admin ID. A hidden form token prevents another site from changing records using the browser's login cookie. These are real local PHP session checks; public demo credentials remain unsuitable for a public production system.

Product save -> validate required fields, category, positive decimal price converted to centavos, whole stock, Active/Featured choices and bundled/HTTPS image reference -> begin transaction -> lock edited product -> compare its signed revision -> insert/update -> commit -> redirect with a success message. A revision includes stock and all editable fields, so a stale form cannot overwrite checkout's decrement. Reloading is required to review current values. Delete confirmation does not mutate on GET; POST is required. Existing order references block hard deletion and assigned products block category deletion.

Category uniqueness uses the existing case-insensitive database index. Customer grouping uses normalized email and joins each group's highest order ID to obtain the latest submitted name/contact. Dashboard totals come directly from the shared database; no illustrative sales or customer figures are embedded in the UI. Dates display in Philippine time.

Server-side checks are the authority even if JavaScript is disabled or someone forges a form. The small admin script supplies repeat-click feedback and collapsible mobile navigation/filters only. Tests use independent PHP processes and an isolated database to verify concurrent cancellation, rollback, snapshots and persistence after restarting PHP; Stage 5 live browser acceptance is separately recorded in the test log and `evidence/browser-results.json`.

## Connected flow, including implemented Stage 4

1. **Browse (Stage 2, implemented):** active products, combined search/category/sort; details rejects inactive/unknown IDs. Related products use same category and active status. Zero-stock remains visible without purchasing.
2. **Cart (Stage 2, implemented):** IDs/quantities-only LocalStorage and backend quotes. Whole quantities/stock checked; subtotal equals total. Stage 5 verifies browser cart interaction and refresh persistence.
3. **Review (Stage 3, implemented):** server quote and owned session retry key; current field/quantity/payment checks, no trusted client prices.
4. **Submit (Stage 3, implemented):** transaction, sorted locks, mandatory re-review on changes, snapshots/stock saved atomically; repeat key resolves the original save.
5. **Confirmation (Stage 3, implemented):** original-session saved-order access, immutable names/prices, post-success cleanup. Another session cannot use the number alone.
6. **Admin (Stage 4, implemented):** login checks `password_verify()`, regenerates session ID, then every page/mutation checks admin authorization. Every state-changing form checks a CSRF token. Logout invalidates the login session. Prepared statements keep values out of SQL syntax; `e()` escapes values in HTML.
7. **Status/cancel (Stage 4, implemented):** lock the order row and validate the exact next transition. Cancellation only from Pending/Confirmed locks its product rows in consistent order, restores quantities, marks Cancelled/stock_restored_at, and commits together. A repeated/concurrent cancellation sees the final state and cannot restore twice. A failed change rolls back.
8. **Summaries (Stage 4, implemented):** all products count, including inactive; all saved orders count, including Cancelled. Pending/Completed each count their status. Total Sales sums Completed totals only. Customers are grouped by normalized email, with latest submitted name/contact (newest order/id), all-order count, and Completed spending. Do not pick an arbitrary name from a grouped query.

Allowed order sequence: `Pending -> Confirmed -> Preparing -> Shipped -> Completed`. Cancellation branches only from Pending or Confirmed to Cancelled. Completed and Cancelled have no outgoing transitions. The status ENUM limits spelling, but **does not enforce transitions**; Stage 4 server logic enforces them while holding the order lock.

## Security boundary

PHP and the database decide permissions, price, stock, totals, and save success. Browser validation is convenient feedback; it is not trusted. CSRF (cross-site request forgery) protection pairs a random session token with a form so another website cannot silently submit changes using that session. Session cookies are HttpOnly and SameSite Lax, and Secure when served over HTTPS. Current local HTTP is development only. Never serve the repository root or commit local credentials/session files.

## Stage 5 evidence path

The final browser run creates a separate random test database, seeds fictional rows and starts its own PHP server. A temporary browser profile clicks real forms and cart controls, reloads actual browser storage, forces a failed save, retries successfully, follows confirmation, manages the order in admin, verifies stock/status/summaries/snapshots and captures 375px/1280px pages. It removes that test database and server afterward. The included JSON report records the checks and measured pages; the normal development database is preserved. Existing backend suites separately cover forged requests, concurrent stock/cancellation, rollback and PHP restart persistence.

The final source package contains application/config placeholders/schema/seed/compiled assets/docs/tests/evidence. Local configuration, sessions, temporary tooling and dependencies are excluded. Reviewers follow README setup; they do not need the local testing tool installation. Recording/sharing a video and repository and sending the reviewed email are separate delivery actions documented in the submission checklist.
