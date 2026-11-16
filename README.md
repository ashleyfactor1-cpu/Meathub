# MeatHub

MeatHub is a web-based e-commerce marketplace that connects local meat producers and suppliers with customers who want to buy meat products online. The platform aims to help producers reach more customers while making it easier to browse and order local meat.

## Current features

- Browse, search, and filter meat products.
- Manage product listings, prices, inventory, and producer profiles from the admin panel.
- View active producer profiles on the storefront.
- Add products to a cart and place an order using the configured GCash QR code.
- Submit a GCash transaction reference and receipt screenshot for manual payment verification.
- Reserve stock while an order is pending; admins can mark verified orders as paid or cancel pending orders to release stock.
- Customer accounts have a navbar notification bell with an unread count and dropdown; the My Orders page focuses on order history and status badges.
- Review revenue, order counts, and low-stock products on the admin dashboard.

The current checkout requires a delivery address. It does not collect a customer name or phone number, and it does not yet support pickup-date selection.

## Business model

MeatHub's planned revenue model is commission from successful transactions, with featured product listings as an additional revenue stream. This can support the cost of operating and maintaining the marketplace while helping producers earn from their sales. Commission calculation and paid featured listings are not yet implemented.

## GCash setup

The merchant QR image is stored at `public/images/gcash-qr.png`. Set the following in `.env`:

```env
GCASH_QR_IMAGE=images/gcash-qr.png
```

Apply database changes with `php artisan migrate`. Customers place an order before paying, then submit their transaction reference from the signed order link. In `/admin/orders`, verify the transfer before marking an order as paid.

Customers can create an account from the storefront and sign in to associate future orders with their account. Guests can browse and add products to their cart, but must sign in to complete checkout. Customer order-status notifications are stored in the `notifications` table and shown in the navbar bell dropdown; the Filament admin panel checks its database notification bell every 30 seconds.

Run `php artisan storage:link` to serve producer avatars and uploaded receipts from the public storage disk.

## Built with

- Laravel 12
- PHP 8.2+
- Filament 3
- Vite
