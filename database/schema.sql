CREATE DATABASE IF NOT EXISTS food_ordering_system_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE food_ordering_system_db;

CREATE TABLE IF NOT EXISTS customers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  first_name VARCHAR(60) NULL,
  surname VARCHAR(60) NULL,
  username VARCHAR(60) NULL UNIQUE,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  email_verified_at DATETIME NULL,
  email_verification_token CHAR(64) NULL,
  email_verification_expires_at DATETIME NULL,
  email_verification_sent_at DATETIME NULL,
  password_reset_token CHAR(64) NULL,
  password_reset_expires_at DATETIME NULL,
  password_reset_sent_at DATETIME NULL,
  phone VARCHAR(30) NULL,
  phone_country VARCHAR(8) NULL,
  delivery_address VARCHAR(255) NULL,
  country VARCHAR(80) NULL,
  zip_code VARCHAR(20) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Adds usernames when upgrading an existing customer table.
ALTER TABLE customers ADD COLUMN IF NOT EXISTS username VARCHAR(60) NULL AFTER full_name;
ALTER TABLE customers ADD UNIQUE INDEX IF NOT EXISTS uq_customer_username (username);
ALTER TABLE customers ADD COLUMN IF NOT EXISTS first_name VARCHAR(60) NULL AFTER full_name;
ALTER TABLE customers ADD COLUMN IF NOT EXISTS surname VARCHAR(60) NULL AFTER first_name;
ALTER TABLE customers ADD COLUMN IF NOT EXISTS delivery_address VARCHAR(255) NULL AFTER phone;
ALTER TABLE customers ADD COLUMN IF NOT EXISTS phone_country VARCHAR(8) NULL AFTER phone;
ALTER TABLE customers ADD COLUMN IF NOT EXISTS country VARCHAR(80) NULL AFTER delivery_address;
ALTER TABLE customers ADD COLUMN IF NOT EXISTS zip_code VARCHAR(20) NULL AFTER country;
ALTER TABLE customers ADD COLUMN IF NOT EXISTS email_verified_at DATETIME NULL AFTER password_hash;
ALTER TABLE customers ADD COLUMN IF NOT EXISTS email_verification_token CHAR(64) NULL AFTER email_verified_at;
ALTER TABLE customers ADD COLUMN IF NOT EXISTS email_verification_expires_at DATETIME NULL AFTER email_verification_token;
ALTER TABLE customers ADD COLUMN IF NOT EXISTS email_verification_sent_at DATETIME NULL AFTER email_verification_expires_at;
ALTER TABLE customers ADD COLUMN IF NOT EXISTS password_reset_token CHAR(64) NULL AFTER email_verification_sent_at;
ALTER TABLE customers ADD COLUMN IF NOT EXISTS password_reset_expires_at DATETIME NULL AFTER password_reset_token;
ALTER TABLE customers ADD COLUMN IF NOT EXISTS password_reset_sent_at DATETIME NULL AFTER password_reset_expires_at;

CREATE TABLE IF NOT EXISTS staff_users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('staff','manager') NOT NULL DEFAULT 'staff',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS admin_users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

ALTER TABLE staff_users ADD COLUMN IF NOT EXISTS role ENUM('staff','manager') NOT NULL DEFAULT 'staff' AFTER password_hash;

CREATE TABLE IF NOT EXISTS menu_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(140) NOT NULL,
  category VARCHAR(60) NOT NULL,
  description VARCHAR(255) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  color VARCHAR(20) NOT NULL DEFAULT '#e9d5b5',
  image_path VARCHAR(255) NULL,
  badge VARCHAR(30) NULL,
  is_available TINYINT(1) NOT NULL DEFAULT 1,
  created_by_admin_id INT UNSIGNED NULL,
  created_by_staff_id INT UNSIGNED NULL,
  updated_by_admin_id INT UNSIGNED NULL,
  updated_by_staff_id INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_menu_created_admin FOREIGN KEY (created_by_admin_id) REFERENCES admin_users(id) ON DELETE SET NULL,
  CONSTRAINT fk_menu_created_staff FOREIGN KEY (created_by_staff_id) REFERENCES staff_users(id) ON DELETE SET NULL,
  CONSTRAINT fk_menu_updated_admin FOREIGN KEY (updated_by_admin_id) REFERENCES admin_users(id) ON DELETE SET NULL,
  CONSTRAINT fk_menu_updated_staff FOREIGN KEY (updated_by_staff_id) REFERENCES staff_users(id) ON DELETE SET NULL,
  CONSTRAINT chk_menu_created_actor CHECK (created_by_admin_id IS NULL OR created_by_staff_id IS NULL),
  CONSTRAINT chk_menu_updated_actor CHECK (updated_by_admin_id IS NULL OR updated_by_staff_id IS NULL)
) ENGINE=InnoDB;

ALTER TABLE menu_items DROP COLUMN IF EXISTS emoji;

CREATE TABLE IF NOT EXISTS cart_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id INT UNSIGNED NOT NULL,
  menu_item_id INT UNSIGNED NOT NULL,
  quantity INT UNSIGNED NOT NULL DEFAULT 1,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_customer_item (customer_id, menu_item_id),
  CONSTRAINT fk_cart_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
  CONSTRAINT fk_cart_menu FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_number VARCHAR(30) NOT NULL UNIQUE,
  customer_id INT UNSIGNED NOT NULL,
  delivery_address VARCHAR(255) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  payment_method VARCHAR(60) NOT NULL,
  notes VARCHAR(255) NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 49.00,
  total DECIMAL(10,2) NOT NULL,
  status ENUM('Order placed','Preparing','Out for delivery','Delivered','Cancelled') NOT NULL DEFAULT 'Order placed',
  updated_by_admin_id INT UNSIGNED NULL,
  updated_by_staff_id INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  delivered_email_sent_at DATETIME NULL,
  CONSTRAINT fk_order_customer FOREIGN KEY (customer_id) REFERENCES customers(id),
  CONSTRAINT fk_order_admin FOREIGN KEY (updated_by_admin_id) REFERENCES admin_users(id) ON DELETE SET NULL,
  CONSTRAINT fk_order_staff FOREIGN KEY (updated_by_staff_id) REFERENCES staff_users(id) ON DELETE SET NULL,
  CONSTRAINT chk_order_actor CHECK (updated_by_admin_id IS NULL OR updated_by_staff_id IS NULL)
) ENGINE=InnoDB;

ALTER TABLE orders ADD COLUMN IF NOT EXISTS delivered_email_sent_at DATETIME NULL AFTER updated_at;

CREATE TABLE IF NOT EXISTS order_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  menu_item_id INT UNSIGNED NULL,
  item_name VARCHAR(140) NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  quantity INT UNSIGNED NOT NULL,
  CONSTRAINT fk_item_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_item_menu FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_status_history (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  status VARCHAR(40) NOT NULL,
  changed_by_admin_id INT UNSIGNED NULL,
  changed_by_staff_id INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_history_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_history_admin FOREIGN KEY (changed_by_admin_id) REFERENCES admin_users(id) ON DELETE SET NULL,
  CONSTRAINT fk_history_staff FOREIGN KEY (changed_by_staff_id) REFERENCES staff_users(id) ON DELETE SET NULL,
  CONSTRAINT chk_history_actor CHECK (changed_by_admin_id IS NULL OR changed_by_staff_id IS NULL)
) ENGINE=InnoDB;

INSERT INTO menu_items (id, name, category, description, price, color, badge) VALUES
  (1,'Truffle Cream Pasta','Pasta','Silky cream sauce, mushrooms, parmesan, and truffle oil.',289,'#e9d5b5','BESTSELLER'),
  (2,'Crispy Chicken Bowl','Bowls','Golden chicken, garlic rice, fresh greens, and house sauce.',249,'#e4c69e','POPULAR'),
  (3,'Garden Pesto Pasta','Pasta','Basil pesto, cherry tomatoes, greens, and toasted seeds.',259,'#bfceb0',NULL),
  (4,'Smoky Beef Burger','Burgers','Smashed beef, cheddar, caramelized onions, and smoky mayo.',279,'#dfb792',NULL),
  (5,'Honey Garlic Wings','Sides','Crispy wings glazed with sweet garlic and sesame.',229,'#dba980','NEW'),
  (6,'Mango Cloud Shake','Drinks','Fresh mango, creamy milk, and a soft whipped finish.',149,'#f4ce83',NULL),
  (7,'Classic Tiramisu','Desserts','Espresso-soaked layers with mascarpone and cocoa.',179,'#caa889',NULL),
  (8,'Citrus Garden Fizz','Drinks','Calamansi, lemon, sparkling water, and fresh mint.',129,'#c8d8a8',NULL),
  (9,'Four Cheese Pizza','Pizza','Mozzarella, cheddar, parmesan, and creamy blue cheese.',329,'#edc989','POPULAR'),
  (10,'Teriyaki Salmon Bowl','Bowls','Glazed salmon, steamed rice, edamame, and sesame.',349,'#d6bd9b',NULL),
  (11,'Spicy Chicken Burger','Burgers','Crispy chicken, chili glaze, slaw, and cooling ranch.',269,'#d9a074',NULL),
  (12,'Roasted Tomato Pasta','Pasta','Slow-roasted tomato sauce, garlic, basil, and parmesan.',239,'#dca27d',NULL),
  (13,'Loaded Potato Wedges','Sides','Crisp potato wedges, cheese sauce, herbs, and bacon.',189,'#eccd8f',NULL),
  (14,'Matcha Cream Latte','Drinks','Ceremonial matcha, fresh milk, and vanilla cream.',159,'#b9c99d',NULL),
  (15,'Chocolate Lava Cake','Desserts','Warm chocolate cake with a rich molten center.',199,'#b9957d','NEW'),
  (16,'Margherita Pizza','Pizza','Tomatoes, fresh mozzarella, basil, and olive oil.',289,'#dfb47d',NULL),
  (17,'Korean Beef Bowl','Bowls','Savory beef, kimchi, steamed rice, and a fried egg.',279,'#c69c78',NULL),
  (18,'Caesar Garden Salad','Salads','Crisp romaine, parmesan, croutons, and Caesar dressing.',219,'#b8c995',NULL),
  (19,'Strawberry Cheesecake','Desserts','Creamy cheesecake with a bright strawberry topping.',189,'#e6b5b2',NULL),
  (20,'Cold Brew Caramel','Drinks','Slow-steeped coffee, caramel, milk, and soft cream.',149,'#c3a486',NULL)
ON DUPLICATE KEY UPDATE name=VALUES(name), category=VALUES(category), description=VALUES(description),
  price=VALUES(price), color=VALUES(color), badge=VALUES(badge);

-- Demo portal accounts. Change these passwords after your first login.
-- Admin password: Admin@123 | Staff password: Staff@123
INSERT INTO admin_users (full_name, email, password_hash) VALUES
  ('System Administrator','admin@savorly.local','$2y$12$cj/doLA6fR829EB78fNOA.5znQxPi21dEwCY5yvpm0LkWqlvPe0vm')
ON DUPLICATE KEY UPDATE full_name=VALUES(full_name), is_active=1;

INSERT INTO staff_users (full_name, email, password_hash) VALUES
  ('Kitchen Staff','staff@savorly.local','$2y$12$MYIocFuxq8be1Bcb8JFcEu3VSHeGd3H9xAaTgaAoRrye0flSp.Lj.')
ON DUPLICATE KEY UPDATE full_name=VALUES(full_name), role='staff', is_active=1;

-- Migrates installations that previously stored administrators in staff_users.
DELETE FROM staff_users WHERE email='admin@savorly.local';
