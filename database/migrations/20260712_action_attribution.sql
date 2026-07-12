USE food_ordering_system_db;

ALTER TABLE orders
  ADD COLUMN IF NOT EXISTS updated_by_admin_id INT UNSIGNED NULL AFTER status,
  ADD COLUMN IF NOT EXISTS updated_by_staff_id INT UNSIGNED NULL AFTER updated_by_admin_id;

ALTER TABLE order_status_history
  ADD COLUMN IF NOT EXISTS changed_by_admin_id INT UNSIGNED NULL AFTER status,
  ADD COLUMN IF NOT EXISTS changed_by_staff_id INT UNSIGNED NULL AFTER changed_by_admin_id;

ALTER TABLE menu_items
  ADD COLUMN IF NOT EXISTS created_by_admin_id INT UNSIGNED NULL AFTER is_available,
  ADD COLUMN IF NOT EXISTS created_by_staff_id INT UNSIGNED NULL AFTER created_by_admin_id,
  ADD COLUMN IF NOT EXISTS updated_by_admin_id INT UNSIGNED NULL AFTER created_by_staff_id,
  ADD COLUMN IF NOT EXISTS updated_by_staff_id INT UNSIGNED NULL AFTER updated_by_admin_id,
  ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

DELIMITER //
CREATE PROCEDURE add_action_attribution_constraints()
BEGIN
  IF NOT EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND CONSTRAINT_NAME='fk_order_admin') THEN
    ALTER TABLE orders ADD CONSTRAINT fk_order_admin FOREIGN KEY (updated_by_admin_id) REFERENCES admin_users(id) ON DELETE SET NULL;
  END IF;
  IF NOT EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND CONSTRAINT_NAME='fk_order_staff') THEN
    ALTER TABLE orders ADD CONSTRAINT fk_order_staff FOREIGN KEY (updated_by_staff_id) REFERENCES staff_users(id) ON DELETE SET NULL;
  END IF;
  IF NOT EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND CONSTRAINT_NAME='chk_order_actor') THEN
    ALTER TABLE orders ADD CONSTRAINT chk_order_actor CHECK (updated_by_admin_id IS NULL OR updated_by_staff_id IS NULL);
  END IF;
  IF NOT EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='order_status_history' AND CONSTRAINT_NAME='fk_history_admin') THEN
    ALTER TABLE order_status_history ADD CONSTRAINT fk_history_admin FOREIGN KEY (changed_by_admin_id) REFERENCES admin_users(id) ON DELETE SET NULL;
  END IF;
  IF NOT EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='order_status_history' AND CONSTRAINT_NAME='fk_history_staff') THEN
    ALTER TABLE order_status_history ADD CONSTRAINT fk_history_staff FOREIGN KEY (changed_by_staff_id) REFERENCES staff_users(id) ON DELETE SET NULL;
  END IF;
  IF NOT EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='order_status_history' AND CONSTRAINT_NAME='chk_history_actor') THEN
    ALTER TABLE order_status_history ADD CONSTRAINT chk_history_actor CHECK (changed_by_admin_id IS NULL OR changed_by_staff_id IS NULL);
  END IF;
  IF NOT EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='menu_items' AND CONSTRAINT_NAME='fk_menu_created_admin') THEN
    ALTER TABLE menu_items ADD CONSTRAINT fk_menu_created_admin FOREIGN KEY (created_by_admin_id) REFERENCES admin_users(id) ON DELETE SET NULL;
  END IF;
  IF NOT EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='menu_items' AND CONSTRAINT_NAME='fk_menu_created_staff') THEN
    ALTER TABLE menu_items ADD CONSTRAINT fk_menu_created_staff FOREIGN KEY (created_by_staff_id) REFERENCES staff_users(id) ON DELETE SET NULL;
  END IF;
  IF NOT EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='menu_items' AND CONSTRAINT_NAME='fk_menu_updated_admin') THEN
    ALTER TABLE menu_items ADD CONSTRAINT fk_menu_updated_admin FOREIGN KEY (updated_by_admin_id) REFERENCES admin_users(id) ON DELETE SET NULL;
  END IF;
  IF NOT EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='menu_items' AND CONSTRAINT_NAME='fk_menu_updated_staff') THEN
    ALTER TABLE menu_items ADD CONSTRAINT fk_menu_updated_staff FOREIGN KEY (updated_by_staff_id) REFERENCES staff_users(id) ON DELETE SET NULL;
  END IF;
  IF NOT EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='menu_items' AND CONSTRAINT_NAME='chk_menu_created_actor') THEN
    ALTER TABLE menu_items ADD CONSTRAINT chk_menu_created_actor CHECK (created_by_admin_id IS NULL OR created_by_staff_id IS NULL);
  END IF;
  IF NOT EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='menu_items' AND CONSTRAINT_NAME='chk_menu_updated_actor') THEN
    ALTER TABLE menu_items ADD CONSTRAINT chk_menu_updated_actor CHECK (updated_by_admin_id IS NULL OR updated_by_staff_id IS NULL);
  END IF;
END//
DELIMITER ;

CALL add_action_attribution_constraints();
DROP PROCEDURE add_action_attribution_constraints;
