# Savorly Food Ordering System

Savorly is a responsive restaurant ordering and order-management website built with PHP, MySQL, HTML, CSS, and JavaScript. It provides separate customer and operations portals, persistent carts and orders, email verification, PDF receipts, cloud-hosted product images, and role-based administration.

## Main features

### Customer website

- Customer registration with first name, surname, username, email, international phone number, delivery address, country, and postal code
- Login using either username or email
- Email verification, resend verification, and forgot-password flows
- Responsive storefront, full menu, category/search filters, pagination, and product-detail modal
- Shopping cart and Cash on Delivery checkout
- Order history and status tracking
- Customer cancellation while an order is still `Order Placed`
- Email order confirmation with an attached PDF receipt
- Email notification when an order is delivered

### Admin and staff portal

- Separate authentication for administrators and staff
- Dashboard and order management
- Customer details, order history, and customer notes
- Product creation and editing with category, badge, price, notes, image, and availability
- Cloudinary product-image storage
- Terminal `Delivered` and `Cancelled` statuses that cannot be changed again
- Admin-only delivered-order revenue dashboard with daily, weekly, and monthly views
- Admin-only revenue PDF export; staff cannot view sales revenue

## Technology

- PHP 8 and PDO
- MariaDB/MySQL through XAMPP
- Vanilla HTML, CSS, and JavaScript
- PHPMailer through Composer for Gmail SMTP
- Cloudinary Upload API for product images
- Server-generated PDF receipts and revenue reports

## Requirements

- XAMPP with Apache, PHP, and MySQL/MariaDB
- Composer
- Internet access for Gmail SMTP and Cloudinary
- A Cloudinary product environment
- A Google account with 2-Step Verification and an app password

## Installation

1. Clone the repository into XAMPP's document root:

   ```powershell
   cd C:\xampp\htdocs
   git clone https://github.com/HyoYoung05/Food-Ordering-System.git food_ordering_system
   cd food_ordering_system
   ```

2. Install PHP dependencies:

   ```powershell
   composer install
   ```

3. Copy the environment template:

   ```powershell
   Copy-Item .env.example .env
   ```

4. Start **Apache** and **MySQL** in the XAMPP Control Panel.

5. Open `http://localhost/phpmyadmin`, select **Import**, and import `database/schema.sql`.

6. Configure `.env` with your local URL, database, Cloudinary, and Gmail SMTP values:

   ```env
   APP_ENV=local
   APP_DEBUG=false
   APP_TIMEZONE=Asia/Manila
   APP_URL=http://localhost/food_ordering_system

   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=food_ordering_system_db
   DB_USER=root
   DB_PASSWORD=""

   CLOUDINARY_CLOUD_NAME=your_cloud_name
   CLOUDINARY_API_KEY=your_api_key
   CLOUDINARY_API_SECRET=your_api_secret

   SMTP_HOST=smtp.gmail.com
   SMTP_PORT=587
   SMTP_USERNAME=your_google_email@example.com
   SMTP_APP_PASSWORD=your_16_character_app_password
   SMTP_FROM_NAME="Savorly Kitchen"
   ```

7. Open the websites:

   - Customer: `http://localhost/food_ordering_system/`
   - Admin and staff: `http://localhost/food_ordering_system/admin/`

Never commit `.env` or expose SMTP and Cloudinary secrets. If a secret is accidentally shared, revoke and replace it immediately.

## Demo operations accounts

After importing `database/schema.sql`:

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@savorly.local` | `Admin@123` |
| Staff | `staff@savorly.local` | `Staff@123` |

Change these credentials before making the application publicly accessible. Customer, staff, and administrator accounts are stored separately in `customers`, `staff_users`, and `admin_users`. Passwords are stored as secure hashes.

## Email verification on localhost

Localhost can send real email through Gmail SMTP. A verification URL containing `localhost` only works on the computer running XAMPP. For customers on another device, set `APP_URL` to a reachable LAN, ngrok, Cloudflare Tunnel, or deployed HTTPS URL before sending the verification message.

After changing `APP_URL`, restart Apache and request a new verification email because previously sent messages keep their original URL.

## Sharing through ngrok

Start Apache and MySQL, then forward Apache's port:

```powershell
ngrok http 80
```

Set the generated address in `.env`, including the project path:

```env
APP_URL=https://your-subdomain.ngrok-free.app/food_ordering_system
```

The public customer and operations URLs will then be:

- `https://your-subdomain.ngrok-free.app/food_ordering_system/`
- `https://your-subdomain.ngrok-free.app/food_ordering_system/admin/`

Free ngrok URLs may change after restarting the tunnel. Update `APP_URL` and resend verification emails whenever the URL changes.

## JSON API and Postman

The customer API controller is:

```text
http://localhost/food_ordering_system/app/controllers/api.php
```

Examples:

```text
GET ?action=bootstrap
GET ?action=product&id=1
```

`bootstrap` returns the current user (or `null` when logged out), menu data, categories, and other initial application state. Use `product&id=1` to retrieve one product instead of appending `/1` to the action name.

Authenticated actions rely on the PHP session cookie. In Postman, log in first and keep cookies enabled for later cart, profile, and order requests.

## Order workflow

```text
Order Placed -> Preparing -> Out for Delivery -> Delivered
       |
       +-> Cancelled
```

- Customers may cancel only during `Order Placed`.
- Admin or staff may update active orders.
- `Delivered` and `Cancelled` are terminal statuses.
- Revenue includes only delivered orders and today's revenue includes only orders delivered today.

## Project structure

```text
Food Ordering System/
|-- admin/                  Admin and staff portal entry point
|-- app/
|   |-- controllers/       PHP API endpoints and browser controller
|   |-- models/            Customer, cart, menu, order, and staff data access
|   |-- services/          Cloudinary, email, and PDF services
|   `-- views/             Customer application markup
|-- assets/
|   |-- css/               Customer/admin layouts and responsive styles
|   |-- images/            Static fallback assets
|   `-- js/                Admin UI and reusable browser utilities
|-- config/                Environment loader and database connection
|-- database/              Schema and local recovery backups
|-- vendor/                Composer dependencies
|-- .env.example           Safe configuration template
|-- composer.json
|-- index.php              Customer entry point
`-- server.js              Optional static preview server
```

The Node preview server does not replace Apache: PHP, MySQL, sessions, email, uploads, and PDFs require the XAMPP/Apache URL.

## Troubleshooting

### `SQLSTATE[HY000] [2002] Connection refused`

Start MySQL in XAMPP and confirm `.env` uses the correct host and port. The default configuration uses `127.0.0.1:3306`.

### MySQL shuts down unexpectedly

Read `C:\xampp\mysql\data\mysql_error.log`. Always export the database before repairing InnoDB files. Stop MySQL through XAMPP before shutting down Windows and never copy live `ibdata1` or redo-log files individually.

### Verification link cannot be reached

Confirm Apache is running and that `APP_URL` is accessible from the device opening the email. `localhost` refers to the device opening the link, not automatically to the XAMPP computer.

### Email is not received

Verify SMTP credentials, use a Google app password rather than the normal account password, check spam, and confirm the computer has internet access. Restart Apache after modifying `.env`.

## Security notes

- Passwords are hashed and verified through PHP password APIs.
- Database operations use prepared PDO statements.
- Role checks protect admin-only revenue functions.
- Secrets belong only in ignored `.env` files.
- XAMPP is intended for development; use hardened hosting and HTTPS for production.
