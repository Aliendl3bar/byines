# Byines — Project Summary

## What It Is
Custom e-commerce platform for a luxury modest fashion boutique (abayas, kimonos, scarves, niqabs). Replaces manual Instagram/WhatsApp sales with a full digital storefront.

## Tech Stack
PHP 7.4+ (no framework) · MySQL (PDO) · Vanilla JS · Custom CSS · Apache (XAMPP)

## Architecture
- **OOP models** in `classes/` (one class per DB table)
- **Thin pages** in `pages/` (fetch data, render HTML)
- **AJAX handlers** for cart, checkout, admin operations
- **Singleton PDO** via `Database::getInstance()`
- **8 stylesheets** loaded per-page, zero inline CSS (except dynamic PHP values)

## Implemented Features
| Area | What Works |
|------|-----------|
| **Auth** | Register, login, logout, password hashing |
| **Shop** | Category filter, price sort, pagination (12/page) |
| **Product** | Image gallery, color/size select, dynamic price, stock check |
| **Cart** | Session-based, AJAX add/remove/update, live badge count |
| **Checkout** | Shipping form, COD/PayPal, tax 10%, shipping calc, AJAX submit |
| **Post-Order** | Order summary, WhatsApp share button |
| **Dashboard** | Order history, profile edit, password change, delete account |
| **Reviews** | Read & submit with star ratings |
| **Wishlist** | AJAX add/remove |
| **Admin** | Stats dashboard, product/category/collection CRUD, order mgmt |
| **Admin Images** | Drag-drop upload, color tagging, reorder, set main |
| **Admin Stock** | Variant table, add new, inline edit modal |

## Not Yet Implemented
- Search / contact form / address book / password reset
- Admin review moderation & user management
- Sales charts (Chart.js not wired)
- CSRF tokens
- Email notifications
- Real payment gateway
- PWA / loyalty program

## Key Files
- `Graduation_Project_Report.md` — full 5-chapter report
- `RULES.md` — development conventions
- `ANALYSIS.md` — detailed codebase analysis
- `setup-database.sql` — full schema + seed data
