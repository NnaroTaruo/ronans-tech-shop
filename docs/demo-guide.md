# Demo guide - Ronan Antoque

Assessment deadline supplied by Ronan: **November 12, 2026**. This guide prepares the required **3-5 minute video**. The video has not been recorded by this project. Record and review your own demonstration before submission.

## Before recording

1. Start XAMPP's **MySQL** module. Apache is unnecessary for PHP's development server.
2. Open PowerShell in the project and run:

   ```powershell
   Set-Location 'C:\Users\Ronan\Desktop\e-commerce website'
   & 'C:\xampp\php\php.exe' -S 127.0.0.1:8000 -t public
   ```

3. Open the customer store at **http://127.0.0.1:8000/**. Open admin in a separate private window/profile at **http://127.0.0.1:8000/admin/login.php**. This keeps admin login session rotation separate from an already-open customer form. Keep confirmation in its original customer session.
4. Demo admin: `admin@ronan-tech.test` / `DemoOnly!2026`. These are fictional public assessment credentials.
5. Verify **Desk Cable Clips** is active, price **120.00**, stock at least **2**; **Microfiber Screen Cloth** is active, price **85.50**, stock at least **1**. Verify the inactive Archive 60 and active zero-stock Drift Silent examples still exist. If your own edits changed them, deliberately restore the needed demo values through admin; do not reset your database just to record.
6. Use **Demo Customer**, `demo.customer@example.test`, `0917 000 0000`, and **123 Demo Lane, Sample City, Philippines (fictional)**. Never use a real address, phone, payment account or customer record.
7. For the short cancellation segment, prepare one separate Pending demo order before recording and note its order number/product stock. Clearly call it a previously prepared demo order. Keep the main recorded purchase separate and advance it to Completed.
8. Note the dashboard's starting Total Sales. Clear only the demo cart you intend to replace. Close unrelated tabs/notifications. Keep config files, session files and real account details off screen.
9. Choose the screen recorder already available on your computer. Record the browser, enable your microphone, and rehearse once. Aim for **4 minutes 45 seconds**, leaving room under the five-minute limit. Do not show all ten tests in the video; the test log carries that evidence.

## Recording script

| Time | What to show | Suggested narration |
| --- | --- | --- |
| 0:00-0:20 | Home, store identity, categories and featured products | "I'm Ronan Antoque, a fourth-year BSIT student. This is Ronan's Tech Shop, my fictional e-commerce assessment. It uses PHP, PDO, a MySQL-compatible InnoDB database, Tailwind CSS and vanilla JavaScript. All customer details and payments are demo data." |
| 0:20-0:50 | Search `mOuSe`, choose Mice, apply low-to-high then high-to-low; show Out of Stock | "Search ignores case, and search, category and sorting work together. Inactive products are hidden. Active products with zero stock remain visible, but cannot be added." |
| 0:50-1:25 | Add Cable Clips twice and Screen Cloth once; open cart; refresh | "The same product merges into one line. Two at PHP 120.00 plus one at PHP 85.50 equals PHP 325.50. Quantities remain after refresh. Browser storage contains IDs and quantities; PHP reloads prices and stock from the database." |
| 1:25-2:05 | Continue to checkout, briefly submit blank fields, then fictional details/Cash on Delivery; save; confirmation and empty cart | "The server validates the checkout and checks current stock and prices again. Saving the order, its lines and stock changes is one transaction. The confirmation appears after saving, with a unique number and Pending status. The purchased cart has cleared." |
| 2:05-2:50 | Admin login, find that exact number, inspect items/customer/total; advance Confirmed, Preparing, Shipped, Completed; dashboard | "Customer and admin use the same database. These saved lines match the confirmation. Only the next allowed status is available. Completed and Cancelled are final. Sales increase by PHP 325.50 only when this order becomes Completed." |
| 2:50-3:20 | Edit a product description, refresh its customer detail; show original order snapshot; try referenced product deletion | "Admin edits appear on customer refresh. Order lines retain their saved names and prices. A product used in an order cannot be deleted; it can be made inactive. Categories with assigned products are protected too." |
| 3:20-3:50 | Open the separate prepared Pending order, show stock before, cancel with confirmation, refresh stock | "This is a separate demo order I prepared earlier. Cancelling restores its purchased quantity once. Refreshing does not restore it again. The test suite also checks repeated and simultaneous cancellation requests." |
| 3:50-4:20 | Browser responsive mode at about 375px: products/cart and admin menu/order detail | "Both customer and admin layouts adapt to a phone width. Navigation folds, forms stack, and admin tables become labelled rows. Required fields have labels and keyboard focus is visible." |
| 4:20-4:45 | README, test log and system-flow summary; briefly point to `app/checkout.php` or `app/admin.php` | "Products belong to categories. Orders have item snapshots linked to products. Customers are derived from normalized order emails. Prices are integer centavos. Local development uses XAMPP MariaDB; Oracle MySQL was not tested. AI assistance is disclosed, and I reviewed the flow and testing. There are no real payments or customer accounts." |

Use your own words. Demonstrate only behavior you understand. If a step fails, fix or accurately disclose it; do not narrate a success that is not visible. If time runs long, shorten narration and filtering examples while retaining the purchase and its admin management.

## Be ready to explain or change

- **PDO/prepared queries:** PHP's database interface sends submitted values separately from SQL syntax.
- **Session/authentication:** a cookie identifies server-side session data; every admin route checks authorization, not just the menu.
- **CSRF:** each state-changing form needs the session's random token to prevent another site submitting changes with that login.
- **Transaction/lock:** checkout and cancellation change related rows together; locking stops competing requests using the same stock simultaneously.
- **Foreign keys/snapshots:** references preserve relationships and prevent deleting product history; saved name/price fields keep old orders stable.
- **Centavos:** `12000 * 2 + 8550 = 32550`; formatting displays PHP 325.50 without floating-point rounding.
- **Retry key:** a repeated checkout request resolves to the original saved order rather than decrementing stock again.
- **Small change practice:** change the product description or home banner and refresh. Change the low-stock threshold in the dashboard query only if you can explain its effect. Rebuild CSS with `npm.cmd run build:css` after adding new utility classes.

## After recording

- [ ] Watch the entire video: readable text, audible narration, unique order number matches in customer/admin, length between 3 and 5 minutes.
- [ ] Upload the video to a service you control and set reviewer-accessible viewing permissions.
- [ ] Open its link in a private/logged-out window to verify access.
- [ ] Put that actual link in `submission-email.md`; do not send a local filesystem path or localhost as a public demo URL.
- [ ] Complete `submission-checklist.md` before sending the assessment email yourself.
