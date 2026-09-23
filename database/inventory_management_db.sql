CREATE DATABASE IF NOT EXISTS inventory_management_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE inventory_management_db;

-- -------------------------------------------------------------------
-- Roles table ni — diri gi-store ang access roles sa system.
-- -------------------------------------------------------------------
CREATE TABLE roles (
    id INT NOT NULL AUTO_INCREMENT,
    role_name VARCHAR(50) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_roles_name (role_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- Permissions ni — defines unsay pwede buhaton per module.
-- -------------------------------------------------------------------
CREATE TABLE permissions (
    id INT NOT NULL AUTO_INCREMENT,
    permission_name VARCHAR(100) NOT NULL,
    module_name VARCHAR(100) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_permissions_name (permission_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- Role permissions mapping ni — mao ni ang link sa roles ug permissions.
-- -------------------------------------------------------------------
CREATE TABLE role_permissions (
    id INT NOT NULL AUTO_INCREMENT,
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_role_permission (role_id, permission_id),
    KEY idx_role_permissions_role (role_id),
    KEY idx_role_permissions_permission (permission_id),
    CONSTRAINT fk_role_permissions_role
        FOREIGN KEY (role_id) REFERENCES roles(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_role_permissions_permission
        FOREIGN KEY (permission_id) REFERENCES permissions(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- Users table ni — account ug basic profile data diri ma-store.
-- -------------------------------------------------------------------
CREATE TABLE users (
    id INT NOT NULL AUTO_INCREMENT,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) DEFAULT NULL,
    last_name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username),
    KEY idx_users_role (role_id),
    CONSTRAINT fk_users_role
        FOREIGN KEY (role_id) REFERENCES roles(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- Categories ni — para organized ra ang product grouping.
-- -------------------------------------------------------------------
CREATE TABLE categories (
    id INT NOT NULL AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_categories_name (category_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- Suppliers table ni — contact ug supplier details diri.
-- -------------------------------------------------------------------
CREATE TABLE suppliers (
    id INT NOT NULL AUTO_INCREMENT,
    supplier_name VARCHAR(150) NOT NULL,
    contact_person VARCHAR(100) DEFAULT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_suppliers_name (supplier_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- Products ni — core inventory item data mao ni diri.
-- -------------------------------------------------------------------
CREATE TABLE products (
    id INT NOT NULL AUTO_INCREMENT,
    category_id INT NOT NULL,
    supplier_id INT DEFAULT NULL,
    product_code VARCHAR(50) NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    unit VARCHAR(50) DEFAULT NULL,
    cost_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    selling_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    stock INT NOT NULL DEFAULT 0,
    reorder_level INT NOT NULL DEFAULT 0,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_products_code (product_code),
    KEY idx_products_category (category_id),
    KEY idx_products_supplier (supplier_id),
    KEY idx_products_name (product_name),
    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id) REFERENCES categories(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_products_supplier
        FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- Stock transactions ni — every stock in/out/adjustment naa diri ang header record.
-- -------------------------------------------------------------------
CREATE TABLE stock_transactions (
    id INT NOT NULL AUTO_INCREMENT,
    transaction_no VARCHAR(50) NOT NULL,
    type ENUM('stock_in', 'stock_out', 'adjustment') NOT NULL,
    supplier_id INT DEFAULT NULL,
    remarks TEXT DEFAULT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_transaction_no (transaction_no),
    KEY idx_transactions_type (type),
    KEY idx_transactions_supplier (supplier_id),
    KEY idx_transactions_created_by (created_by),
    CONSTRAINT fk_stock_transactions_supplier
        FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,
    CONSTRAINT fk_stock_transactions_user
        FOREIGN KEY (created_by) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- Transaction items ni — detailed product rows per stock transaction.
-- -------------------------------------------------------------------
CREATE TABLE stock_transaction_items (
    id INT NOT NULL AUTO_INCREMENT,
    transaction_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    cost_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_items_transaction (transaction_id),
    KEY idx_items_product (product_id),
    CONSTRAINT fk_transaction_items_transaction
        FOREIGN KEY (transaction_id) REFERENCES stock_transactions(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_transaction_items_product
        FOREIGN KEY (product_id) REFERENCES products(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- Stock adjustments ni — diri ma-track ang manual corrections sa stock.
-- -------------------------------------------------------------------
CREATE TABLE stock_adjustments (
    id INT NOT NULL AUTO_INCREMENT,
    product_id INT NOT NULL,
    system_stock INT NOT NULL,
    actual_stock INT NOT NULL,
    difference INT NOT NULL,
    reason VARCHAR(255) NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_adjustments_product (product_id),
    KEY idx_adjustments_user (created_by),
    CONSTRAINT fk_stock_adjustments_product
        FOREIGN KEY (product_id) REFERENCES products(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_stock_adjustments_user
        FOREIGN KEY (created_by) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- Activity logs ni — para traceable ra kung kinsa ug unsay gibuhat.
-- -------------------------------------------------------------------
CREATE TABLE activity_logs (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_activity_user (user_id),
    KEY idx_activity_action (action),
    CONSTRAINT fk_activity_logs_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------
-- Seed data ni for roles ug permissions para ready-to-use dayon ang fresh setup.
-- -------------------------------------------------------------------
INSERT INTO roles (role_name, description, status) VALUES
('admin', 'Full system access', 1),
('staff', 'Limited inventory access', 1)
ON DUPLICATE KEY UPDATE role_name = role_name;

INSERT INTO permissions (permission_name, module_name, description, status) VALUES
('view_dashboard', 'dashboard', 'View dashboard overview', 1),
('manage_products', 'products', 'Create, edit, and delete products', 1),
('manage_suppliers', 'suppliers', 'Create, edit, and delete suppliers', 1),
('manage_stock_in', 'stock_in', 'Process stock-in transactions', 1),
('manage_stock_out', 'stock_out', 'Process stock-out transactions', 1),
('manage_adjustments', 'stock_adjustment', 'Adjust inventory records', 1),
('view_reports', 'reports', 'View and export reports', 1),
('manage_users', 'users', 'Manage system users and roles', 1)
ON DUPLICATE KEY UPDATE permission_name = permission_name;

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON 1=1
WHERE r.role_name = 'admin'
ON DUPLICATE KEY UPDATE role_id = role_id;

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.permission_name IN ('view_dashboard', 'manage_products', 'manage_suppliers', 'manage_stock_in', 'manage_stock_out', 'view_reports')
WHERE r.role_name = 'staff'
ON DUPLICATE KEY UPDATE role_id = role_id;

-- -------------------------------------------------------------------
-- Default admin account ni for initial setup; ilisi dayon ang password sa real deployment.
-- -------------------------------------------------------------------
INSERT INTO users (first_name, middle_name, last_name, username, password, role_id, status)
SELECT 'System', NULL, 'Administrator', 'admin', '$2y$12$sDusIfJlzofgxJf6D7cAbetKPOSUzw.CpIxK/kVxXLSWESpynCEtm', r.id, 1
FROM roles r
WHERE r.role_name = 'admin'
ON DUPLICATE KEY UPDATE username = username;

-- -------------------------------------------------------------------
-- Optional starter categories ni; pwede ra nimo ilisan based sa actual inventory.
-- -------------------------------------------------------------------
INSERT INTO categories (category_name, status) VALUES
('General', 1),
('Electronics', 1),
('Office Supplies', 1)
ON DUPLICATE KEY UPDATE category_name = category_name;

-- Quick notes lang bai:
-- Default admin password kay admin123.
-- Sample hash ra ni; for production, gamit ug imong own secure password hash.
