# Desktop/mobile evidence - Ronan Antoque

Captured November 11, 2026 using live PHP pages in an isolated fictional test database. These are unaltered browser PNG captures, not static HTML mockups. Each name begins with its viewport width: **1280** (desktop) or **375** (mobile). Full-page captures include scrolling content. The browser used its reduced-motion preference for stable hit-tested input.

`browser-results.json` records ten checks, per-page width/image/label measurements and runtime exceptions. All ten checks passed in the final run; its temporary database and PHP server were removed. Screenshot data will differ from Ronan's own local database because tests preserve his working orders.

## Screenshot checklist

Each of the following has both desktop and mobile PNGs in `screenshots/`:

| Completed | Filename suffix | What it demonstrates |
| --- | --- | --- |
| Yes | `home.png` | Branding, banner, category navigation and featured products |
| Yes | `products.png` | Product grid, filters, prices, availability and actions |
| Yes | `product.png` | Product detail, stock, quantity and related items |
| Yes | `zero-stock.png` | Visible zero-stock detail with purchasing unavailable |
| Yes | `cart.png` | Populated cart, merged quantity and exact PHP 325.50 |
| Yes | `empty-cart.png` | Useful empty state |
| Yes | `checkout.png` | Populated guest form and PHP 325.50 review |
| Yes | `confirmation.png` | Saved order number, customer/payment/items and total |
| Yes | `admin-login.png` | Demo admin login |
| Yes | `admin-dashboard.png` | Shared real totals, recent orders and low stock |
| Yes | `admin-products.png` | Admin catalog and displayed statuses |
| Yes | `admin-product-form.png` | Product fields, validation help and delete action |
| Yes | `admin-categories.png` | Categories and assigned product counts |
| Yes | `admin-category-form.png` | Category editing and deletion entry point |
| Yes | `admin-orders.png` | Saved orders newest first and statuses |
| Yes | `admin-order.png` | Completed saved detail and immutable snapshots |
| Yes | `admin-customers.png` | Normalized-email summaries and Completed spending |

Total: **34 PNGs**. A filename example is `1280-cart.png`; its mobile counterpart is `375-cart.png`.

## Scope and review limits

Browser interaction covers the purchase flow, validation/re-review/failure recovery, admin forms/statuses, cart refresh, mobile menu/filter disclosure, logout, and sampled Tab/Enter skip-link focus. Backend suites additionally test forged requests, stock/cancellation races, rollback and restart persistence. Full keyboard traversal, screen readers, physical phones and Oracle MySQL are not certified by these checks. No video recording or personal test by Ronan is implied.

For a concise submission preview, show home, populated cart, checkout, confirmation, dashboard and order detail at both widths; the remaining images are supporting evidence. The complete set can stay in the repository.
