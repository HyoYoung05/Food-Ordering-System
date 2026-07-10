# Savorly Food Ordering System

A responsive online restaurant ordering experience built with HTML, CSS, and JavaScript.

## Run with XAMPP and MySQL

1. Start **Apache** and **MySQL** from the XAMPP Control Panel.
2. Copy this project folder into `C:\xampp\htdocs\savorly` (or create an Apache alias pointing to it).
3. Open `http://localhost/phpmyadmin`.
4. Choose **Import**, select `database/schema.sql`, and run the import. This creates `food_ordering_system_db` and seeds the menu.
5. Open `http://localhost/savorly/`.

## Login websites

- **Customer ordering website:** `http://localhost/savorly/`
- **Admin and Staff portal:** `http://localhost/savorly/admin/`

The customer login stores accounts in `customers`. Administrator accounts are stored separately in `admin_users`, while employee accounts are stored in `staff_users`. Neither can sign in through the customer form.

Initial portal accounts after importing `database/schema.sql`:

| Role | Email | Password |
|---|---|---|
| Admin | `admin@savorly.local` | `Admin@123` |
| Staff | `staff@savorly.local` | `Staff@123` |

Change these demonstration credentials before deploying the project publicly.

The default XAMPP credentials are already configured: MySQL user `root` with an empty password. If yours differ, update `config/database.php` or set the `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD` environment variables.

## Project structure

```text
Food Ordering System/
├── app/
│   ├── controllers/
│   │   ├── api.php
│   │   ├── staff-api.php
│   │   └── AppController.js
│   ├── models/
│   │   ├── AdminUser.php
│   │   ├── AdminOrder.php
│   │   ├── Cart.php
│   │   ├── Customer.php
│   │   ├── Menu.php
│   │   ├── MenuModel.js
│   │   ├── Order.php
│   │   ├── StaffUser.php
│   │   └── StoreModel.js
│   └── views/
│       └── index.html
├── assets/
│   ├── css/
│   │   └── styles.css
│   ├── images/
│   └── js/
├── config/
│   └── database.php
├── database/
│   └── schema.sql
├── admin/
│   └── index.php
├── index.php
├── server.js
└── README.md
```

- **Models** manage customers, menu items, carts, and orders in MySQL.
- **Views** contain the application markup.
- **Controllers** coordinate user actions, models, API requests, and dynamic rendering.
- **Assets** contain stylesheets, images, and reusable client-side resources.

## Included features

- Customer login stored locally in the browser
- Searchable, category-filtered food menu
- Shopping cart with quantity controls and persistent contents
- Checkout and order management
- Live order status tracking (demo statuses advance over time)
- Downloadable PDF order receipt generated in the browser
- Email order confirmation through the customer's configured email application

## Demo notes

Customer accounts, menu items, carts, orders, line items, and status history are persisted in MySQL. PHP sessions keep customers authenticated, passwords are hashed, and database queries use prepared PDO statements. Browser storage remains only as a temporary offline fallback. The email confirmation button opens a pre-addressed message through `mailto:`; a production deployment can replace this with a transactional email API.

`server.js` can still preview the visual interface with Node, but PHP database requests require Apache through XAMPP.
