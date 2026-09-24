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


-- ============================================================
-- MODULES
-- Parent/group of permissions
-- ============================================================

CREATE TABLE modules (
    id INT NOT NULL AUTO_INCREMENT,
    module_name VARCHAR(100) NOT NULL,
    module_key VARCHAR(100) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_modules_name (module_name),
    UNIQUE KEY uq_modules_key (module_key)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- PERMISSIONS
-- Child actions belonging to a module
-- ============================================================

CREATE TABLE permissions (
    id INT NOT NULL AUTO_INCREMENT,
    module_id INT NOT NULL,

    permission_name VARCHAR(100) NOT NULL,
    permission_key VARCHAR(100) NOT NULL,
    action VARCHAR(50) NOT NULL,

    description VARCHAR(255) DEFAULT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_permissions_key (permission_key),
    UNIQUE KEY uq_module_action (module_id, action),

    KEY idx_permissions_module (module_id),

    CONSTRAINT fk_permissions_module
        FOREIGN KEY (module_id)
        REFERENCES modules(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- ROLE PERMISSIONS
-- Assign permissions to roles
-- ============================================================

CREATE TABLE role_permissions (
    id INT NOT NULL AUTO_INCREMENT,

    role_id INT NOT NULL,
    permission_id INT NOT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_role_permission (role_id, permission_id),

    KEY idx_role_permissions_role (role_id),
    KEY idx_role_permissions_permission (permission_id),

    CONSTRAINT fk_role_permissions_role
        FOREIGN KEY (role_id)
        REFERENCES roles(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_role_permissions_permission
        FOREIGN KEY (permission_id)
        REFERENCES permissions(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;



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
-- Seed data ni for roles, modules, and permissions
-- para ready-to-use dayon ang fresh setup.
-- -------------------------------------------------------------------

-- -------------------------------------------------------------------
-- Roles
-- -------------------------------------------------------------------
INSERT INTO roles (role_name, description, status) VALUES
('admin', 'Full system access', 1),
('staff', 'Limited inventory access', 1)
ON DUPLICATE KEY UPDATE
    description = VALUES(description),
    status = VALUES(status);


-- -------------------------------------------------------------------
-- Modules
-- -------------------------------------------------------------------
INSERT INTO modules
(module_name, module_key, description, status, sort_order)
VALUES
('Dashboard',  'dashboard',  'Dashboard overview', 1, 10),
('Products',   'products',   'Product management', 1, 20),
('Categories', 'categories', 'Category management', 1, 30),
('Suppliers',  'suppliers',  'Supplier management', 1, 40),
('Stock',      'stock',      'Inventory stock management', 1, 50),
('Reports',    'reports',    'Inventory reports', 1, 60),
('Users',      'users',      'User management', 1, 70),
('Roles',      'roles',      'Role and permission management', 1, 80)
ON DUPLICATE KEY UPDATE
    description = VALUES(description),
    status = VALUES(status),
    sort_order = VALUES(sort_order);


-- -------------------------------------------------------------------
-- Dashboard permissions
-- -------------------------------------------------------------------
INSERT INTO permissions
(module_id, permission_name, permission_key, action, description, status)
SELECT
    id,
    'View Dashboard',
    'dashboard.view',
    'view',
    'View dashboard overview',
    1
FROM modules
WHERE module_key = 'dashboard'
ON DUPLICATE KEY UPDATE
    description = VALUES(description),
    status = VALUES(status);


-- -------------------------------------------------------------------
-- Product permissions
-- -------------------------------------------------------------------
INSERT INTO permissions
(module_id, permission_name, permission_key, action, description, status)
SELECT id, 'View Products', 'products.view', 'view',
       'View products', 1
FROM modules WHERE module_key = 'products'
ON DUPLICATE KEY UPDATE description = VALUES(description), status = VALUES(status);

INSERT INTO permissions
(module_id, permission_name, permission_key, action, description, status)
SELECT id, 'Create Products', 'products.create', 'create',
       'Add products', 1
FROM modules WHERE module_key = 'products'
ON DUPLICATE KEY UPDATE description = VALUES(description), status = VALUES(status);

INSERT INTO permissions
(module_id, permission_name, permission_key, action, description, status)
SELECT id, 'Edit Products', 'products.edit', 'edit',
       'Edit products', 1
FROM modules WHERE module_key = 'products'
ON DUPLICATE KEY UPDATE description = VALUES(description), status = VALUES(status);

INSERT INTO permissions
(module_id, permission_name, permission_key, action, description, status)
SELECT id, 'Delete Products', 'products.delete', 'delete',
       'Delete products', 1
FROM modules WHERE module_key = 'products'
ON DUPLICATE KEY UPDATE description = VALUES(description), status = VALUES(status);


-- -------------------------------------------------------------------
-- Category permissions
-- -------------------------------------------------------------------
INSERT INTO permissions
(module_id, permission_name, permission_key, action, description, status)
SELECT id, 'View Categories', 'categories.view', 'view',
       'View categories', 1
FROM modules WHERE module_key = 'categories'
ON DUPLICATE KEY UPDATE description = VALUES(description), status = VALUES(status);

INSERT INTO permissions
(module_id, permission_name, permission_key, action, description, status)
SELECT id, 'Create Categories', 'categories.create', 'create',
       'Add categories', 1
FROM modules WHERE module_key = 'categories'
ON DUPLICATE KEY UPDATE description = VALUES(description), status = VALUES(status);

INSERT INTO permissions
(module_id, permission_name, permission_key, action, description, status)
SELECT id, 'Edit Categories', 'categories.edit', 'edit',
       'Edit categories', 1
FROM modules WHERE module_key = 'categories'
ON DUPLICATE KEY UPDATE description = VALUES(description), status = VALUES(status);

INSERT INTO permissions
(module_id, permission_name, permission_key, action, description, status)
SELECT id, 'Delete Categories', 'categories.delete', 'delete',
       'Delete categories', 1
FROM modules WHERE module_key = 'categories'
ON DUPLICATE KEY UPDATE description = VALUES(description), status = VALUES(status);


-- -------------------------------------------------------------------
-- Supplier permissions
-- -------------------------------------------------------------------
INSERT INTO permissions
(module_id, permission_name, permission_key, action, description, status)
SELECT id, 'View Suppliers', 'suppliers.view', 'view',
       'View suppliers', 1
FROM modules WHERE module_key = 'suppliers'
ON DUPLICATE KEY UPDATE description = VALUES(description), status = VALUES(status);

INSERT INTO permissions
(module_id, permission_name, permission_key, action, description, status)
SELECT id, 'Create Suppliers', 'suppliers.create', 'create',
       'Add suppliers', 1
FROM modules WHERE module_key = 'suppliers'
ON DUPLICATE KEY UPDATE description = VALUES(description), status = VALUES(status);

INSERT INTO permissions
(module_id, permission_name, permission_key, action, description, status)
SELECT id, 'Edit Suppliers', 'suppliers.edit', 'edit',
       'Edit suppliers', 1
FROM modules WHERE module_key = 'suppliers'
ON DUPLICATE KEY UPDATE description = VALUES(description), status = VALUES(status);

INSERT INTO permissions
(module_id, permission_name, permission_key, action, description, status)
SELECT id, 'Delete Suppliers', 'suppliers.delete', 'delete',
       'Delete suppliers', 1
FROM modules WHERE module_key = 'suppliers'
ON DUPLICATE KEY UPDATE description = VALUES(description), status = VALUES(status);


-- -------------------------------------------------------------------
-- Stock permissions
-- -------------------------------------------------------------------
INSERT INTO permissions
(module_id, permission_name, permission_key, action, description, status)
SELECT id, 'View Stock', 'stock.view', 'view',
       'View current stock', 1
FROM modules WHERE module_key = 'stock'
ON DUPLICATE KEY UPDATE description = VALUES(description), status = VALUES(status);

INSERT INTO permissions
(module_id, permission_name, permission_key, action, description, status)
SELECT id, 'Stock In', 'stock.stock_in', 'stock_in',
       'Process stock-in transactions', 1
FROM modules WHERE module_key = 'stock'
ON DUPLICATE KEY UPDATE description = VALUES(description), status = VALUES(status);

INSERT INTO permissions
(module_id, permission_name, permission_key, action, description, status)
SELECT id, 'Stock Out', 'stock.stock_out', 'stock_out',
       'Process stock-out transactions', 1
FROM modules WHERE module_key = 'stock'
ON DUPLICATE KEY UPDATE description = VALUES(description), status = VALUES(status);

INSERT INTO permissions
(module_id, permission_name, permission_key, action, description, status)
SELECT id, 'Adjust Stock', 'stock.adjust', 'adjust',
       'Adjust inventory records', 1
FROM modules WHERE module_key = 'stock'
ON DUPLICATE KEY UPDATE description = VALUES(description), status = VALUES(status);

INSERT INTO permissions
(module_id, permission_name, permission_key, action, description, status)
SELECT id, 'View Stock History', 'stock.history', 'history',
       'View stock transaction history', 1
FROM modules WHERE module_key = 'stock'
ON DUPLICATE KEY UPDATE description = VALUES(description), status = VALUES(status);


-- -------------------------------------------------------------------
-- Report permissions
-- -------------------------------------------------------------------
INSERT INTO permissions
(module_id, permission_name, permission_key, action, description, status)
SELECT id, 'View Reports', 'reports.view', 'view',
       'View reports', 1
FROM modules WHERE module_key = 'reports'
ON DUPLICATE KEY UPDATE description = VALUES(description), status = VALUES(status);

INSERT INTO permissions
(module_id, permission_name, permission_key, action, description, status)
SELECT id, 'Export Reports', 'reports.export', 'export',
       'Export reports', 1
FROM modules WHERE module_key = 'reports'
ON DUPLICATE KEY UPDATE description = VALUES(description), status = VALUES(status);


-- -------------------------------------------------------------------
-- User permissions
-- -------------------------------------------------------------------
INSERT INTO permissions
(module_id, permission_name, permission_key, action, description, status)
SELECT id, 'View Users', 'users.view', 'view',
       'View system users', 1
FROM modules WHERE module_key = 'users'
ON DUPLICATE KEY UPDATE description = VALUES(description), status = VALUES(status);

INSERT INTO permissions
(module_id, permission_name, permission_key, action, description, status)
SELECT id, 'Create Users', 'users.create', 'create',
       'Create system users', 1
FROM modules WHERE module_key = 'users'
ON DUPLICATE KEY UPDATE description = VALUES(description), status = VALUES(status);

INSERT INTO permissions
(module_id, permission_name, permission_key, action, description, status)
SELECT id, 'Edit Users', 'users.edit', 'edit',
       'Edit system users', 1
FROM modules WHERE module_key = 'users'
ON DUPLICATE KEY UPDATE description = VALUES(description), status = VALUES(status);

INSERT INTO permissions
(module_id, permission_name, permission_key, action, description, status)
SELECT id, 'Delete Users', 'users.delete', 'delete',
       'Delete system users', 1
FROM modules WHERE module_key = 'users'
ON DUPLICATE KEY UPDATE description = VALUES(description), status = VALUES(status);


-- -------------------------------------------------------------------
-- Role permissions: ADMIN
-- Admin gets all active permissions.
-- -------------------------------------------------------------------
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
CROSS JOIN permissions p
WHERE r.role_name = 'admin'
  AND p.status = 1
ON DUPLICATE KEY UPDATE
    role_id = VALUES(role_id);


-- -------------------------------------------------------------------
-- Role permissions: STAFF
-- Staff gets limited inventory permissions.
-- -------------------------------------------------------------------
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p
    ON p.permission_key IN (
        'dashboard.view',

        'products.view',

        'categories.view',

        'suppliers.view',

        'stock.view',
        'stock.stock_in',
        'stock.stock_out',
        'stock.history',

        'reports.view'
    )
WHERE r.role_name = 'staff'
ON DUPLICATE KEY UPDATE
    role_id = VALUES(role_id);


-- -------------------------------------------------------------------
-- Default admin account ni for initial setup.
-- Ilisi dayon ang password sa real deployment.
-- -------------------------------------------------------------------
INSERT INTO users (
    first_name,
    middle_name,
    last_name,
    username,
    password,
    role_id,
    status
)
SELECT
    'System',
    NULL,
    'Administrator',
    'admin',
    '$2y$12$sDusIfJlzofgxJf6D7cAbetKPOSUzw.CpIxK/kVxXLSWESpynCEtm',
    r.id,
    1
FROM roles r
WHERE r.role_name = 'admin'
ON DUPLICATE KEY UPDATE
    username = username;


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
