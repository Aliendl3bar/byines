# Byines — Project Analysis

## Overview

**Byines** is a custom-built e-commerce application for a luxury modest fashion boutique. It sells Abayas, Kimonos, Scarves, and Niqabs under the tagline *"Timeless Elegance"*, targeting women seeking contemporary modest wear.

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP 7.4+ (procedural + OOP, no framework) |
| Database | MySQL / MariaDB via PDO |
| Frontend | Vanilla JavaScript (no framework) |
| CSS | Custom CSS (no preprocessor, no Tailwind/Bootstrap) |
| Fonts | Google Noto Serif, Manrope, Material Symbols |
| Server | Apache (XAMPP) |
| OS | Windows |

---

## Directory Structure

```
byines/
├── index.php                        # Redirects to pages/index.php
├── setup-database.sql               # Full schema + seed data
├── ANALYSIS.md                      # This file
├── assets/                          # 48 static images (products, categories, hero)
├── classes/                         # OOP domain models
│   ├── Database.php                 # Singleton PDO wrapper
│   ├── User.php                     # Auth, profile, admin checks
│   ├── Product.php                  # CRUD, images, variants, ordering
│   ├── Category.php                 # CRUD for categories
│   ├── Cart.php                     # Session-based shopping cart
│   └── Order.php                    # Orders, status updates
├── includes/
│   ├── header.php                   # DOCTYPE, nav, autoloader
│   └── footer.php                   # Footer, newsletter, copyright
├── pages/                           # 21 PHP pages
│   ├── index.php                    # Homepage
│   ├── shop.php                     # Collections with filters & pagination
│   ├── product.php                  # Product detail page
│   ├── cart.php                     # Shopping cart
│   ├── cart_action.php              # AJAX cart handler
│   ├── checkout.php                 # Checkout form
│   ├── process_order.php            # AJAX order submission
│   ├── order_success.php            # Order confirmation
│   ├── login.php / signup.php / logout.php
│   ├── dashboard.php                # User dashboard
│   ├── delete_account.php           # Account deletion handler
│   ├── admin_dashboard.php          # Single-page admin panel
│   ├── admin_manage_product.php     # Product CRUD handler
│   ├── admin_manage_category.php    # Category CRUD handler
│   ├── admin_manage_collection.php  # Collection CRUD handler
│   ├── admin_view_order.php         # Legacy order detail view
│   └── admin_manage_order.php       # AJAX order status updates
├── css/                             # 7 CSS files (style, cstyle, product, cart-checkout, dashboard, admin, login, signup)
├── scripts/                         # 6 JS files (product, cart, checkout, dashboard, admin, toggle_password)
├── products/                        # Per-product image directories (7/img/, 8/img/, etc.)
└── archive/                         # Original static HTML prototype
```

---

## Database Schema (11 tables)

| Table | Purpose |
|-------|---------|
| `users` | Customers & admins (role: user/admin) |
| `categories` | Product categories (Abayas, Kimonos, Scarfs, Niqab) |
| `products` | Core product data (name, slug, SKU, price, old_price) |
| `product_images` | Per-color images linked to products |
| `product_variants` | Color/size/stock/price_modifier combinations |
| `addresses` | User shipping & billing addresses |
| `orders` | Order header (status, totals, shipping snapshot, payment) |
| `order_items` | Line items linked to variants |
| `wishlist` | User-product favourites (unique constraint) |
| `reviews` | Ratings 1–5 with approval workflow |
| `collections` | Custom groupings (title, image, comma-separated product IDs) |

---

## Features

### Customer-facing
- **Homepage** — Hero banner, popular picks, category grid
- **Shop** — Category filter, price slider, sorting, pagination (12/page)
- **Product Detail** — Image gallery, color/size selection, dynamic pricing, stock check, reviews, related products
- **Cart** — Session-based, AJAX quantity/removal, tax/shipping calculation
- **Checkout** — Shipping form (Morocco-only), payment method (COD/PayPal), AJAX submission
- **Auth** — Register, login, logout, password visibility toggle
- **Dashboard** — Order history, profile editing, account deletion

### Admin (single-page tabbed interface)
- **Overview** — Total orders, revenue, user count
- **Products** — CRUD with image gallery (drag-drop upload, reorder, color tagging), variant/stock management, visibility toggle
- **Categories** — CRUD with image
- **Collections** — CRUD with product assignment
- **Orders** — Status filter, detail modal, status update, deletion

---

## Architecture & Patterns

### Database
- Singleton `Database::getInstance()` via `classes/Database.php`
- PDO with prepared statements, `ERRMODE_EXCEPTION`, real prepares, `utf8mb4`
- Transactions on multi-step writes (orders, product deletes)

### Autoloading
- Custom `spl_autoload_register` in `includes/header.php` maps class names to `classes/{Name}.php`

### Session
- Cart in `$_SESSION['cart']` — keyed by `productId_md5(color_size)`
- Auth in `$_SESSION['user_id']`, `$_SESSION['user_name']`, `$_SESSION['user_role']`

### Security
- Prepared statements throughout (no raw SQL concatenation)
- `htmlspecialchars()` on all output
- `password_hash()` / `password_verify()` for auth
- Role check on admin routes

### AJAX
- `fetch()` + `FormData` for cart, checkout, admin operations
- JSON responses: `{ success, message, ...data }`

### CSS
- Custom properties (`--brand-cream`, `--brand-earth`, `--brand-dark`, etc.)
- Kebab-case classes, responsive breakpoints (640–1280px)
- Earthy tones, serif typography

---

## Observations

### Strengths
- Solid no-framework architecture with clean OOP models
- Consistent security practices (prepared statements, escaped output, hashed passwords)
- Good separation of concerns (classes / pages / scripts / css)
- Sophisticated admin panel with modals, drag-drop uploads, and inline editing
- Consistent naming conventions across PHP, JS, CSS, and DB

### Issues / Inconsistencies
1. **Shipping cost mismatch** — `Order.php` applies $15 flat or free over $100; `process_order.php` applies $3 or free for Tangier — two different logics.
2. **Order number format** — Mix of `BYINES-XXXXXXXX` and `ORD-...` prefixes.
3. **Dashboard profile save** — Writes to `localStorage` instead of sending to the server (stub).
4. **No CSRF tokens** — Forms are vulnerable to cross-site request forgery.
5. **`collections` table** — Used in code but missing from `setup-database.sql`.
6. **Size filter** — Labeled "Coming soon" on the shop page.
7. **Passwords in logs** — `password_hash` result could be logged in error scenarios.
8. **Error handling** — Some PDO exceptions are caught with generic messages; could leak schema info in edge cases.

---

## Recommendations

| Priority | Suggestion |
|----------|------------|
| High | Unify shipping logic into `Order.php` and remove inline calculation from `process_order.php` |
| High | Add CSRF tokens to all state-changing forms |
| Medium | Add `collections` table DDL to `setup-database.sql` |
| Medium | Implement server-side profile update in dashboard |
| Medium | Unify order number format |
| Low | Add size filter functionality to shop page |
| Low | Move inline JS from PHP files into `scripts/` modules |
| Low | Consider input validation library / stricter validation on all forms |

---

## Summary

Byines is a well-structured, early-to-mid stage custom e-commerce platform built for a Moroccan modest fashion brand. It demonstrates strong foundational practices (OOP, prepared statements, AJAX interactivity) and a cohesive design aesthetic. The main areas for improvement are resolving inconsistencies between duplicate implementations and hardening security with CSRF protection.
