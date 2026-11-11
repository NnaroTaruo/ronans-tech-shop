# Ronan's Tech Shop

Project author: **Ronan Antoque**.

A fictional electronics store for Ronan Antoque's CubeTech Innovations Web Development Internship Assessment. Ronan is a fourth-year BSIT student. This project is being built in five teaching stages so he can run, maintain, and explain it.

**Current scope: Stages 1-5 implemented/prepared.** The connected customer/admin application, final automated acceptance checks, desktop/mobile screenshots, demo guide and submission draft are ready. Source and evidence are published at [NnaroTaruo/ronans-tech-shop](https://github.com/NnaroTaruo/ronans-tech-shop) on `main`; public access was verified. Ronan reports that recording is complete. Video upload/viewing access and the video link in the email draft still need completion before sending. All store/contact/product data is fictional. No real payments are collected.

**Reviewer / fresh copy:** follow **Windows setup** below. Local configuration, database contents and installed npm dependencies are intentionally excluded from the source package; create your own ignored config and run the seed instructions. Compiled CSS and bundled images are included. Start with `docs/demo-guide.md` for the recording walkthrough.

**This computer is already prepared:** `ronan_tech_shop_dev` has been created and seeded, ignored `config/local.php` is configured, CSS is compiled, and a local Git repository is initialized. Keep your existing local config. Stage 5 leaves MariaDB and the PHP preview at `http://127.0.0.1:8000` running for your manual review. If stopped or after reboot, use the run commands below. Do not start a second preview on the same port.

## Requirements in simple English

Build a mobile-friendly shop where a guest can find products, keep a cart, review current prices, and place an order. Save that order and reduce stock together. An admin must manage the same products, categories, orders, and customer summary. Keep order history accurate, reject invalid status changes, restore cancelled stock once, and record the ten acceptance checks.

Source: `CubeTech_Web_Development_Intern_Assessment.pdf`, all seven pages. [Requirements mapped to sections 1-19](docs/requirements-checklist.md). Your supplied assessment email deadline is **November 12, 2026**. The PDF defers to the email deadline; it does not specify that date. Its suggested schedule is five working days, approximately 15-20 hours overall. Actual start date/time spent have not been recorded.

## Technologies and storage

- PHP 8.2+, server-rendered HTML, PDO with the `pdo_mysql` extension.
- MySQL-compatible schema using InnoDB and UTF-8. Intended for MySQL 8.0.16+; local development uses the installed **XAMPP MariaDB 10.4.32**, as Ronan approved. These are different database products; Oracle MySQL compatibility still needs an actual run there.
- Tailwind CSS 4 compiled locally with its official CLI. Node/npm build CSS only; PHP does not need Node at runtime. The compiled stylesheet is included so the project can run without installing npm packages.
- Vanilla JavaScript handles cart interactions. LocalStorage persists only product IDs/quantities using the key `ronan-tech-shop-cart-v1`.
- Checkout review/retry/confirmation access is stored in the PHP session. Per-tab SessionStorage temporarily holds only the pending retry key and cart IDs/quantities, never customer details.
- PHP session authentication protects every admin route and mutation. Successful login verifies a password hash and rotates the session ID and CSRF token; logout removes admin access and rotates both again.
- Git/GitHub for source control. The public repository is [NnaroTaruo/ronans-tech-shop](https://github.com/NnaroTaruo/ronans-tech-shop), with `main` as its default branch.

The database is the shared source for categories, products, admins, orders, and order items. Customer and admin pages query this same database. Browser storage holds only product IDs and quantities; its contents are editable and never authorize prices, stock, permissions, or successful orders. Database data survives a PHP restart. The cart persists within the same browser profile and site origin; clearing site data removes it. Cart operations do not reserve or reduce stock.

## Folder structure

```text
app/
  bootstrap.php           load helpers and start the browser session
  database.php            one reusable PDO connection
  catalog.php             product queries and server-side cart validation/totals
  checkout.php            validation, locked final review, atomic order save
  admin.php               access checks, forms, product edits, status locks, summaries
  functions.php           HTML escaping, integer money, CSRF helpers
  views/header.php        shared document head and navigation
  views/footer.php        shared demo contact footer
  views/product-card.php  shared home/listing/related-product card
  views/admin-*.php       protected admin shell and footer
config/
  config.example.php      tracked placeholders
  local.php               your settings; ignored by Git
database/
  schema.sql              five InnoDB tables and constraints
  seed.php                twelve fictional products
docs/                     requirements, test/progress logs, flow, AI disclosure
public/
  index.php               customer home page
  setup.php               preserved Stage 1 setup page
  products.php            combined search/category/sort listing
  product.php             active product details and related products
  cart.php                cart page and reusable item template
  checkout.php            guest details and order review/save
  confirmation.php        session-authorized saved order
  api/cart.php            JSON endpoint that validates and quotes cart items
  api/checkout.php        review/save endpoint with retry protection
  assets/css/app.css      compiled Tailwind stylesheet
  assets/js/cart.js       cart storage, interactions, current-price requests
  assets/js/checkout.js   review/save feedback and post-success cart cleanup
  assets/js/admin.js      prevent repeat clicks and fold mobile navigation
  admin/                  login/logout, dashboard, catalog, orders and customers
  assets/images/          bundled fictional product illustrations
resources/css/app.css     Tailwind input and shared styling
scripts/setup-database.php create tables and seed an empty database
tests/                    foundation/storefront and isolated checkout/admin race checks
tmp/                      ignored session files and local working files
```

Always serve **public/** as the document root. Config, scripts, tests, and documentation must stay outside the web root. Customer pages are simple PHP files inside `public/`; protected admin pages live in `public/admin/`.

## Windows setup

Open this folder in VS Code. Use **Terminal > New Terminal**, with PowerShell. These tools were found on this computer: PHP at `C:\xampp\php\php.exe`, MariaDB client at `C:\xampp\mysql\bin\mysql.exe`, Node, npm, and Git. MariaDB was stopped during the initial inspection. No installation of XAMPP is needed here.

### 1. Check PHP

```powershell
Set-Location 'C:\Users\Ronan\Desktop\e-commerce website'
php -v
php -m
```

Look for `PDO`, `pdo_mysql`, and `session`. If `php` is not on PATH in a new terminal, use `& 'C:\xampp\php\php.exe'` instead of `php` in the commands below.

### 2. Start MariaDB and create a dedicated database

Open XAMPP Control Panel and start its **MySQL** module (XAMPP labels its MariaDB server MySQL). Apache is unnecessary when using PHP's local server below. If another database server already uses port 3306, resolve that first; do not start two on the same port.

Open the database console:

```powershell
& 'C:\xampp\mysql\bin\mysql.exe' --host=127.0.0.1 --port=3306 --user=root --password
```

Enter your local database password when prompted. On an unchanged XAMPP installation it may be blank; do not assume that on another computer. Do not paste passwords into chat or shell command arguments.

At the SQL prompt, run:

```sql
CREATE DATABASE IF NOT EXISTS ronan_tech_shop_dev
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

This creates a new database without deleting other databases. For a local assessment you can connect with your existing development account. A hosted application needs a dedicated account with appropriate permissions; deployment is outside this stage.

### 3. Add local configuration

```powershell
Copy-Item .\config\config.example.php .\config\local.php
```

Run that copy **only if local.php does not already exist**. Open `config/local.php` in VS Code. Keep host `127.0.0.1`, port `3306`, and database `ronan_tech_shop_dev` unless your setup differs. Replace the username/password placeholders with your local database credentials. Do not add this file to Git.

### 4. Create tables and seed

```powershell
php scripts/setup-database.php
```

Expected first-run message: `Seed saved: 3 categories, 12 fictional products, 1 demo admin, 0 orders.` Repeating the command preserves existing rows. A partially populated database is also preserved; inspect it or use the explicit demo reset below. The schema file creates missing tables; it is not a migration tool for changing existing columns in future stages.

### 5. Start PHP

```powershell
php -S 127.0.0.1:8000 -t public
```

Leave that terminal running. Open **http://127.0.0.1:8000/** for the storefront. Visit **http://127.0.0.1:8000/setup.php** for the preserved setup check: expect “Database connected”, 12 seeded products, and 3 categories. Press **Ctrl+C** in the server terminal to stop it. This local server is for development only.

Keep this origin throughout the exercise: switching between `localhost` and `127.0.0.1`, or changing ports, uses different LocalStorage carts.

### 6. Run foundation checks in another terminal

```powershell
php tests/foundation.php
php tests/database.php
```

The first command needs no database. It checks exact centavo calculations, invalid price inputs, escaping, and session CSRF tokens. The second checks seeded data, image references, InnoDB, hashed demo credentials, category constraints, and negative-stock rejection. Its attempted database changes run in a transaction and roll back. These foundation checks alone do not verify the complete customer/admin flow.

### 7. Edit or rebuild CSS when needed

You can run the site using the included compiled CSS. To edit the Tailwind styles:

```powershell
npm.cmd ci
npm.cmd run build:css
```

Use `npm.cmd run watch:css` while editing. Windows `npm.cmd` avoids PowerShell's script execution-policy issue with `npm.ps1`. Commit the input CSS, package files, and rebuilt output CSS together. Never commit `node_modules/`.

## Seed data and reset

Categories: Keyboards, Mice, Accessories. The seed has 12 products: 4 in each category. `Archive 60 Keyboard` is inactive. `Drift Silent Mouse` is active with zero stock. `Desk Cable Clips` costs PHP 120.00 and `Microfiber Screen Cloth` costs PHP 85.50, supporting the required 2 × 120.00 + 85.50 = **PHP 325.50** example. No customer orders are preloaded.

The following command **erases all records in this shop's five tables**, including future orders, and reseeds them. Use it only when you want a fresh fictional development dataset. It refuses other database names and requires the exact confirmation flag. It does not reset auto-increment IDs; application logic must not depend on fixed IDs.

```powershell
php scripts/setup-database.php --reset-demo-data --confirm=ronan_tech_shop_dev
```

Stop the PHP server while resetting. Other databases are not touched. Schema creation occurs before the seed transaction because MySQL table-creation statements commit implicitly. The seed/reset row changes themselves are saved together or rolled back together.

## Routes and demo credentials

| Route | Stage / current state |
| --- | --- |
| `/` | Stage 2 customer home, implemented |
| `/setup.php` | Preserved Stage 1 setup check, implemented |
| `/products.php` | Stage 2 listing, implemented; `q`, `category`, `sort` GET parameters |
| `/product.php?id=...` | Stage 2 active product details, implemented; missing/inactive IDs return 404 |
| `/cart.php` | Stage 2 persistent cart, implemented |
| `/api/cart.php` | Stage 2 POST JSON cart validation/quote; requires session CSRF header |
| `/checkout.php` | Stage 3 guest checkout, implemented |
| `/api/checkout.php` | Stage 3 POST JSON review/save, with CSRF and a session-owned review key |
| `/confirmation.php?order=...` | Stage 3 saved order; original browser-session access required |
| `/admin/login.php`, `/admin/logout.php` | Stage 4 login; logout requires POST and CSRF |
| `/admin/`, `/admin/index.php` | Stage 4 protected dashboard, implemented |
| `/admin/products.php`, `/admin/categories.php` | Stage 4 protected lists, implemented |
| `/admin/product.php`, `/admin/category.php` | Add; use `?id=...` to edit, `&delete=yes` for confirmation |
| `/admin/orders.php`, `/admin/order.php?id=...` | Stage 4 protected orders/statuses, implemented |
| `/admin/customers.php` | Stage 4 protected read-only customer summary, implemented |

Seeded demo admin email: **admin@ronan-tech.test**. Demo password: **DemoOnly!2026**. These are public, fictional assessment credentials, hashed with PHP's `password_hash()` in the database. **Login is implemented.** These credentials are unsuitable for a public deployment.

## Five-stage teaching plan

| Stage | Working result | Checks to focus on |
| --- | --- | --- |
| 1 | Setup docs, schema, seeds, safe money helpers, reusable layout | Foundation checks; all ten full acceptance checks start Not tested |
| 2 | Home, combined product search/filter/sort, details/related products, persistent cart | 01, 02, parts of 03/04/10 |
| 3 | Guest checkout, current-data review, atomic save/stock changes, retry protection, confirmation | 03, 04, 05, parts of 09 |
| 4 | Admin authentication/authorization, product/category CRUD, orders/statuses/cancellation, customer/dashboard summaries | 05-09 |
| 5 | Complete acceptance log, 375px/1280px review, fixes, screenshots, demo video/script, submission draft | All ten; submission checklist |

Pause for Ronan to run and understand each stage before starting the next. The plan follows the PDF's suggested five-day sequence; it is not a record of time spent.

## How Stage 1 works

1. Your browser requests `public/setup.php` (originally `index.php` in Stage 1).
2. It loads `app/bootstrap.php`, which loads shared helpers and starts a PHP session. A **session** stores data on the server, connected to your browser by a cookie.
3. `db()` connects through **PDO**, PHP's interface for database queries. `setup.php` only reads seed counts.
4. The page includes the same header and footer that later pages will reuse. Values from storage must pass through `e()` before entering HTML.
5. A **foreign key** is a database link: for example, each product's `category_id` points to a real category. It prevents orphaned references and blocks deletion of assigned categories.
6. A **transaction** groups database changes so they all succeed together or all undo. Seeding and checkout use one; cancellation also uses one.
7. **CSRF** means cross-site request forgery: a different website tries to submit a change using your browser's session. Cart/checkout requests require a random session token. Every state-changing admin form also enforces it.

Money stays as integers: PHP 85.50 is stored as `8550`. We parse decimal **strings**, never multiply decimal floats by 100. `money()` converts centavos into a display string with two decimal places. Product prices must be positive when saving products; the reusable parser can represent zero, while the schema rejects zero product prices.

## Stage 1 manual learning checks

- Open `/setup.php` and refresh. Confirm counts after seeding. Customer navigation now leads to Home, Products, Cart, and Contact.
- Resize to about 375px and 1280px. Tab through navigation/action links and the skip link; confirm visible focus and readable text with no horizontal page scrolling.
- Run setup twice: the second run must say existing data was preserved, with counts unchanged.
- Follow `setup.php` into `bootstrap.php`, `database.php`, and both view files.
- Explain these before Stage 2: Why are config files outside `public/`? Why store PHP 85.50 as 8550? What prevents deleting a category that still has products?

See [test evidence](docs/test-log.md) and [completed work / next steps / blockers](docs/progress-log.md). Recorded automated verification still needs to be followed by Ronan's own manual run and understanding check.

## How Stage 2 works

1. `index.php`, `products.php`, and `product.php` read the catalog through `app/catalog.php`. Inactive records are excluded in SQL. Active zero-stock products stay visible with disabled purchasing controls.
2. The listing uses a standard HTML GET form: the search, category, and sort values travel together in the URL. SQL uses prepared values, a literal case-insensitive name search, and an allowlist for sort expressions. Invalid filters return a useful 400 page; unknown/inactive product details return 404.
3. `app/views/product-card.php` is reused by home, listing, and related-product sections. Related items are limited to three active products from the same category, excluding the viewed product; no section appears when there are none.
4. `cart.js` saves an array like `[{"id": 7, "quantity": 2}]` in **LocalStorage**, browser storage that survives refresh. IDs are database references, not fixed seed positions. Adding an existing ID merges its quantity.
5. Before an add/update and when loading the cart, JavaScript calls `/api/cart.php`. An **API endpoint** is a URL that returns data to code. Here it returns **JSON**, a structured data format. The request sends only IDs/quantities plus a session CSRF header; PHP reloads current product names/prices/stock and calculates whole-centavo totals. Extra client price fields are ignored.
6. Unavailable items, excessive quantities, invalid records, or unsafe totals block a usable cart total. Invalid saved data is preserved with a clear recovery action. Failed quote/add/update requests do not overwrite saved cart contents. Removal and clearing are deliberate local edits and need no database write.
7. Buttons show a saving state while an add/update is checked. Product additions show feedback near the clicked button and in the shared status area. Cart rows support editing/removal, and storage changes in another tab refresh the header/cart display.

The cart input is limited to 100 entries, positive integer IDs/quantities within unsigned 32-bit bounds, and an exact total within JavaScript's safe integer range. A product's current stock remains the tighter quantity limit. Prices/subtotals/totals are integer centavos. Browser storage is not locked across simultaneous writes in different tabs; use one tab when editing a cart. Final order locking/retry protection belongs to Stage 3.

### Stage 2 checks and manual walkthrough

Keep the PHP server running in one terminal. In another:

```powershell
php tests/storefront.php
node tests/storefront.mjs
```

The PHP checks exercise real database reads and roll back temporary price/active changes. The Node checks exercise cart serialization/merge/error handling with a test storage object and actual HTTP listing/API responses. They do **not** substitute for the following real browser interaction checks:

1. Open `/`. Check the banner, all three category links, featured products, navigation, and fictional contact/social labels.
2. On Products, search `mOuSe`, choose **Mice**, and apply **Price: low to high**. Expect 4 products. Switch to high to low without clearing search/category; results reverse. Search an invented name and check the empty/reset state.
3. Open a product's details. Check its description, available stock, quantity input, and up to three related products. `Archive 60 Keyboard` must not appear. `Drift Silent Mouse` must show Out of Stock with purchasing disabled.
4. Add **Desk Cable Clips** twice and **Microfiber Screen Cloth** once. Open Cart. Expect two rows, Clips quantity 2, Cloth quantity 1, and subtotal/total **PHP 325.50**.
5. Refresh. Those quantities should remain. Set Clips to 3 and click Update: expect **PHP 445.50**. Remove Cloth: expect **PHP 360.00**. Clear the cart and check the empty state. Do this only when you want to remove your own demo cart contents.
6. Try quantity 0, 1.5, and more than the displayed stock. These must be blocked without changing saved contents. Use Tab/Enter on controls and check 375px/1280px layouts, including a populated cart and long product names.

**Understanding check:** Why don't we save prices in LocalStorage? How do all three filters survive an Apply click? Why doesn't adding to the cart reduce database stock?

The Stage 2 checks remain regression checks. Ronan requested Stage 3 progression; that is authorization to continue, not a claim that every manual check above passed.

## How Stage 3 works

1. Cart enables **Continue to checkout** only for a valid nonempty quote. PHP stores reviewed IDs/quantities, prices and stock with a random checkout key in the server session. Client prices/totals are ignored.
2. Guest details require a nonblank name/address, valid email, contact number with 7-15 digits and common formatting, and one allowed payment selection. Notes are optional. Server limits: name 120 characters, email 254, contact 40, address/notes 2000. Emails are trimmed and lowercased. Errors keep form input and cart.
3. Saving checks CSRF, the session-owned checkout key, unchanged cart and completed review. PHP starts a transaction and reads products **FOR UPDATE**, in increasing ID order. A **row lock** makes another checkout wait before changing that product's stock.
4. Current prices/names/active state/stock are compared with the server review. Any change, including lower stock that still covers the quantity, returns an explanation and blocks saving until **Review current order** is clicked. A repeated save without review is rejected.
5. PHP inserts the order and name/unit-price snapshots, then reduces stock using a conditional update. Everything commits together; errors roll back everything. Integer-centavo subtotal equals total. The saved order begins Pending with a unique `RTS-...` number.
6. The unique checkout key makes saving **idempotent**: repeating the request returns the same order without another stock reduction. The PHP session stays locked while checkout state changes; a database unique constraint also protects the key. Retrying after a lost response resolves an existing committed save.
7. Only a saved result permits confirmation and cart cleanup. The browser subtracts saved items while preserving later additions, then removes its per-tab pending record. A completion marker prevents subtracting twice if removing that record fails. Confirmation can finish interrupted cleanup; an old confirmation without matching pending progress does not clear a new cart. A cart-storage failure shows a warning on the saved confirmation.
8. Confirmation uses the original PHP session's access map. A copied number in another session returns 404. Saved item names/prices survive product edits. Dates are stored UTC and displayed in Philippine time.

No migration/reset is required. Keep the existing database and `config/local.php`. Stage 4 now provides protected admin order viewing/status changes.

### Stage 3 automated checks

```powershell
node tests/checkout.mjs
```

MariaDB must run, and local credentials need permission to create/drop a test database. The suite creates a random `ronan_tech_shop_test_<12 hex digits>` database, seeds it, starts temporary PHP servers on ports 18031/18032, tests actual HTTP/database behavior, stops those servers, and removes only that database. Keep those test ports free. Your development products/orders are preserved. If interrupted, the printed test name identifies leftovers; never reset the shop to clean up a test run.

`TECH_SHOP_TEST_DATABASE` is a validated override passed only to child test processes; leave it unset for normal development. The fixture refuses ordinary database names. Tests force a partial-write failure and simultaneous attempts at the last unit through independent PHP processes. Purchased-item subtraction has a separate JavaScript check; Stage 5 also verifies real browser storage/redirect interaction in an isolated demo; Ronan should rehearse the same flow.

### Stage 3 manual walkthrough

1. Start `php -S 127.0.0.1:8000 -t public`. Add two **Desk Cable Clips** and one **Microfiber Screen Cloth**, then continue from Cart. Check **PHP 325.50** before saving.
2. Try blank/invalid fields and check their errors. Then use **Demo Customer**, **demo.customer@example.test**, **0917 000 0000**, **123 Demo Lane, Sample City, Philippines (fictional)**, and **Cash on Delivery**. Notes may stay blank. Use fictional information.
3. Click **Place demo order** once; it should disable while saving. Expect a unique number, Pending, customer/payment details, two saved lines and PHP 325.50. Cart should clear; stock should fall by 2 Clips and 1 Cloth.
4. Refresh confirmation. Number/total must stay the same; stock must not fall again. Open the copied URL in a private browser session: expect unavailable. Keep the original session to view it again.
5. Check empty `/checkout.php`, keyboard navigation, and populated checkout at 375px/1280px. The automated suite covers changed-data and failed-save cases in its separate database; avoid editing/resetting working data to repeat those tests.

**Understanding check:** Why save order/items/stock in one transaction? What prevents duplicate orders? Why require another review after price/stock changes?

Ronan authorized Stage 4. This does not assert that every Stage 3 manual interaction has passed.

## How Stage 4 works

1. Every protected route calls `require_admin()` before reading or changing admin data. Login uses `password_verify()` against the seeded hash, then regenerates the session ID. A **session ID** is the random cookie value linking a browser to its server-side session; rotating it prevents keeping the pre-login identifier. Logging out removes admin authorization but preserves the guest checkout access map.
2. Each mutation requires POST and a valid CSRF token. Admin forms work without JavaScript; the small admin script prevents repeated clicks and folds navigation/filters on phones. Errors retain fields where useful. Redirecting after success means refreshing the result page does not repeat a write.
3. Product forms parse decimal prices through the existing integer-centavo helper, validate whole stock and existing category, and accept existing bundled images or HTTPS URLs. No image upload is needed. HTTPS images load in the browser; the server does not download them. Remote availability/licensing remains the author's responsibility; the initial seed uses only bundled illustrations.
4. An edit includes a signed **revision** of all current product fields. Saving locks the product and compares that revision. If checkout or another editor changed it, the old form cannot overwrite current stock. Use **Reload current values**, review, and save again. Category names are trimmed and use the database's case-insensitive unique constraint.
5. Delete links open confirmation pages; only a confirmed POST deletes. A product used by any saved order cannot be deleted; the error offers **Keep and edit product**, where Active can be set to No. A category with any assigned product, even inactive, cannot be deleted. Database foreign keys enforce these guards under concurrent requests too.
6. `change_order_status()` locks the order and checks both the expected old status and the allowed next status. Only `Pending → Confirmed → Preparing → Shipped → Completed` is allowed. Pending/Confirmed may instead become Cancelled. Completed/Cancelled are final. Cancellation locks products by increasing ID, restores saved quantities (including inactive products), writes Cancelled and `stock_restored_at`, and commits together. A failure undoes the entire change; repeat requests cannot restore again.
7. Dashboard queries count all products/orders, exact Pending/Completed states, normalized unique emails, and Completed sales only. The read-only customer query groups orders by trimmed/lowercase email, uses the latest inserted order's name/contact, counts all orders and sums only Completed spending. Saved orders display their item snapshots, not current catalog names/prices.

### Stage 4 file guide

| Files | Purpose |
| --- | --- |
| `app/admin.php` | Shared access checks, CSRF/form helpers, validation, signed product revision, locked status/cancel logic and summary queries. |
| `app/views/admin-header.php`, `admin-footer.php` | Reused admin navigation, messages, account/logout and responsive shell. |
| `public/admin/login.php`, `logout.php` | Verify the hash, rotate sessions/tokens, grant/remove admin access. |
| `public/admin/index.php` | All-time dashboard, recent orders and active low-stock links. |
| `public/admin/products.php`, `product.php` | Search/filter/list, add/edit and confirmed protected deletion. |
| `public/admin/categories.php`, `category.php` | Category list/counts, add/edit, confirmed deletion and duplicate/assigned-product errors. |
| `public/admin/orders.php`, `order.php` | Newest-first orders, saved details and guarded status/cancellation forms. |
| `public/admin/customers.php` | Read-only normalized-email summaries. |
| `public/assets/js/admin.js`, CSS source/compiled stylesheet | Submit feedback, mobile navigation/filters, responsive tables and fields. |
| `app/catalog.php` | Shared validation of bundled/HTTPS image references for admin and customer display. |
| `tests/admin.mjs`, `tests/checkout-fixture.php` | Isolated HTTP integration, cancellation contention/rollback, summaries and PHP-restart persistence. |

### Stage 4 run and checks

Keep your database/config; **no reset or migration is required**. Start XAMPP's MySQL module if MariaDB is stopped. From PowerShell in the project:

```powershell
Set-Location 'C:\Users\Ronan\Desktop\e-commerce website'
& 'C:\xampp\php\php.exe' -S 127.0.0.1:8000 -t public
```

Open **http://127.0.0.1:8000/admin/login.php**. Use **admin@ronan-tech.test** / **DemoOnly!2026**. Stop the preview with Ctrl+C when finished. If port 8000 is already in use by your preview, use that running preview instead of starting a second copy.

In a second PowerShell terminal, automated tests are optional for your own rerun:

```powershell
$env:SHOP_PHP='C:\xampp\php\php.exe'
node tests/admin.mjs
node tests/checkout.mjs
```

The admin suite creates/removes only its random `ronan_tech_shop_test_<12 hex digits>` database and starts/stops its own PHP servers on **18041/18042**. MariaDB credentials need local create/drop privileges and those ports must be free. It never resets your shop. Checkout uses 18031/18032. `SHOP_PHP` selects the PHP executable; leave `TECH_SHOP_TEST_DATABASE` unset in normal development.

### Stage 4 manual walkthrough

1. Before logging in, visit `/admin/products.php`: expect the login page. Try a wrong password, then log in with the demo credentials. Check all six dashboard values; zero sales is correct if you have no Completed orders.
2. In Categories, add **Demo Cables**, then try `demo cables` and a blank name: both must fail. Rename it to **Demo Connectors**. In Products, add **Demo USB Cable**, description **Fictional cable for assessment testing**, image `/assets/images/accessories.svg`, category Demo Connectors, price `125.50`, stock `3`, Active Yes, Featured No.
3. Refresh the customer Products page and open the new product. Check the name/category/description, PHP 125.50 and stock 3. Edit its price/description, refresh the customer page, then set Active No and verify it disappears and its detail URL returns unavailable. Restore Active Yes. Try invalid price/stock and check messages. Combine name/category/displayed-status filters in admin.
4. Place one fictional customer order for the cable. In admin Orders, find its number and compare customer/address/payment/notes/items/quantities/totals with confirmation. Try deleting that product: expect a blocked deletion and the edit/inactivation option. Try deleting Demo Connectors: assigned products must block it. To test allowed deletion, add a separate never-ordered product, open its delete confirmation, and confirm only that demo product.
5. Record the cable's current stock, cancel its Pending order, and tick the cancellation confirmation. Stock should rise by the purchased quantity exactly once. Refresh both pages: it must not rise again. Cancelled is final. The automated suite checks duplicate/concurrent cancellation and rollback safely in a separate database.
6. Place another fictional order. Advance it one step at a time: Confirmed, Preparing, Shipped, Completed. Only allowed next choices should appear. Total Sales should increase only on Completed. Reuse the same email with different casing/spacing and a newer demo name/contact; Customers should show one customer for that email, the latest name/contact, all-order count and Completed spending.
7. At about 375px and 1280px, try the Admin menu, filters, form errors, delete confirmations and status controls with mouse and Tab/Enter. Log out and revisit protected routes: expect login. Restart PHP, log in and confirm saved rows remain. Stage 5 automation verifies the browser purchase/admin flow and samples keyboard behavior; your personal rehearsal still matters.

**Understanding check:** Why does every admin page check the session on the server? Why must cancellation and stock restoration share one transaction? Why does a stale product form fail after checkout changes stock?

Ronan authorized Stage 5 on November 11, 2026. Earlier walkthroughs remain available for learning and regression checks.

## Limitations and remaining work

All ten acceptance rows now have executed automated Pass evidence. Stage 5 checks browser purchase/cleanup/re-review and admin forms, captures 34 live screenshots at 375px/1280px, and supplies the recording/submission guides. GitHub source/evidence publication and public access are complete. Ronan reports recording is complete; video playback/duration, upload/viewing access and final sending remain to be verified. Confirmation depends on the original browser session; guest accounts/tracking are outside scope. Images remain shared category graphics; footer social links are labelled platform homepages.

Foundation, database, storefront, checkout and admin suites passed again on November 11. The final installed headless-browser run uses live PHP pages and actual mouse/form/storage interaction in a separate test database, not static snapshots. Its report and desktop/mobile images are included in `docs/evidence/`. Physical-phone testing, exhaustive keyboard/screen-reader coverage and Oracle MySQL remain unverified; no such certification is claimed.

Ronan supplied the GitHub repository for source publication. No hosted application deployment or submission email has been performed. Never expose `config/local.php`, session files, real personal data, or database exports. Use `.gitignore` and review `git status` before sharing.

## Documentation and submission

- [Requirements checklist](docs/requirements-checklist.md)
- [Test log](docs/test-log.md)
- [Progress log](docs/progress-log.md)
- [System flow and relationships](docs/system-flow.md)
- [AI assistance and asset sources](docs/ai-disclosure.md)

Stage 5 deliverables: [demo/recording guide](docs/demo-guide.md), [submission checklist](docs/submission-checklist.md), [email draft](docs/submission-email.md), [known limits](docs/known-issues.md), and [desktop/mobile evidence](docs/evidence/README.md). The PDF accepts a local working app plus video. Repository and screenshot links are filled in; the video URL remains a placeholder until supplied and checked. No message is sent automatically.

Implementation references: [official Tailwind CLI setup](https://tailwindcss.com/docs/installation/tailwind-cli), [PHP PDO transactions](https://www.php.net/manual/en/pdo.begintransaction.php), and [MySQL foreign key behavior](https://dev.mysql.com/doc/refman/8.4/en/create-table-foreign-keys.html). These are documentation references, not copied application templates.

## Stage 5 handoff - November 11, 2026

Start with [the demo guide](docs/demo-guide.md): it has exact startup commands, fictional checkout details, a timed 4:45 recording outline and interview explanations. Use [the submission checklist](docs/submission-checklist.md) to finish repository/video access, then fill [the email draft](docs/submission-email.md). Localhost is for running the application on your computer, not a public URL to send the reviewer.

The final test log records all ten checks as automated Pass with evidence and limits. Desktop/mobile captures are under `docs/evidence/screenshots/`; the inventory and browser report are in [evidence](docs/evidence/README.md). Small final fixes correct singular admin counts and keep order numbers separate from readable dates on narrow screens. Existing development data/config remain intact.

No new dependency or framework was added. The optional Node backend suites remain rerunnable with the commands above; final browser checks were run with local installed-browser tooling in a temporary profile and isolated database. Those local tooling scripts are not needed to run the application. The included report/screenshots document that execution; the demo guide provides a manual replay.

Before your demonstration, explain: **Why does checkout calculate money on the server? Why must cancellation restore stock in the same transaction? Why do saved item snapshots survive a product edit?** Trace the answers through `app/catalog.php`, `app/checkout.php` and `app/admin.php`.
