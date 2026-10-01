USE inventory_management_db;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 1;

-- Development/demo seed only. All demo accounts use the bcrypt hash for: password
-- This file is deliberately date-rich so dashboard and custom report ranges are useful.
START TRANSACTION;

-- -----------------------------------------------------------------------------
-- 1. Access-control foundation
-- -----------------------------------------------------------------------------
INSERT INTO roles (role_name, description, status, created_at, updated_at) VALUES
('admin', 'Full system access', 1, '2025-11-01 08:00:00', '2025-11-01 08:00:00'),
('staff', 'Limited inventory access', 1, '2025-11-01 08:05:00', '2025-11-01 08:05:00')
ON DUPLICATE KEY UPDATE
    description = VALUES(description),
    status = VALUES(status),
    updated_at = VALUES(updated_at);

INSERT INTO modules (module_name, module_key, description, status, sort_order, created_at, updated_at) VALUES
('Dashboard', 'dashboard', 'Dashboard overview', 1, 10, '2025-11-01 08:10:00', '2025-11-01 08:10:00'),
('Products', 'products', 'Product management', 1, 20, '2025-11-01 08:10:00', '2025-11-01 08:10:00'),
('Categories', 'categories', 'Category management', 1, 30, '2025-11-01 08:10:00', '2025-11-01 08:10:00'),
('Suppliers', 'suppliers', 'Supplier management', 1, 40, '2025-11-01 08:10:00', '2025-11-01 08:10:00'),
('Stock', 'stock', 'Inventory stock management', 1, 50, '2025-11-01 08:10:00', '2025-11-01 08:10:00'),
('Reports', 'reports', 'Inventory reports', 1, 60, '2025-11-01 08:10:00', '2025-11-01 08:10:00'),
('Users', 'users', 'User management', 1, 70, '2025-11-01 08:10:00', '2025-11-01 08:10:00'),
('Activity Logs', 'activity_logs', 'Audit and activity history', 1, 75, '2025-11-01 08:10:00', '2025-11-01 08:10:00'),
('Roles', 'roles', 'Role and permission management', 1, 80, '2025-11-01 08:10:00', '2025-11-01 08:10:00')
ON DUPLICATE KEY UPDATE
    description = VALUES(description),
    status = VALUES(status),
    sort_order = VALUES(sort_order),
    updated_at = VALUES(updated_at);

INSERT INTO permissions (module_id, permission_name, permission_key, action, description, status, created_at, updated_at)
SELECT m.id, p.permission_name, p.permission_key, p.action, p.description, 1, '2025-11-01 08:20:00', '2025-11-01 08:20:00'
FROM modules m
JOIN (
    SELECT 'dashboard' module_key, 'View Dashboard' permission_name, 'dashboard.view' permission_key, 'view' action, 'View dashboard overview' description
    UNION ALL SELECT 'products', 'View Products', 'products.view', 'view', 'View products'
    UNION ALL SELECT 'products', 'Create Products', 'products.create', 'create', 'Add products'
    UNION ALL SELECT 'products', 'Edit Products', 'products.edit', 'edit', 'Edit products'
    UNION ALL SELECT 'products', 'Delete Products', 'products.delete', 'delete', 'Delete products'
    UNION ALL SELECT 'categories', 'View Categories', 'categories.view', 'view', 'View categories'
    UNION ALL SELECT 'categories', 'Create Categories', 'categories.create', 'create', 'Add categories'
    UNION ALL SELECT 'categories', 'Edit Categories', 'categories.edit', 'edit', 'Edit categories'
    UNION ALL SELECT 'categories', 'Delete Categories', 'categories.delete', 'delete', 'Delete categories'
    UNION ALL SELECT 'suppliers', 'View Suppliers', 'suppliers.view', 'view', 'View suppliers'
    UNION ALL SELECT 'suppliers', 'Create Suppliers', 'suppliers.create', 'create', 'Add suppliers'
    UNION ALL SELECT 'suppliers', 'Edit Suppliers', 'suppliers.edit', 'edit', 'Edit suppliers'
    UNION ALL SELECT 'suppliers', 'Delete Suppliers', 'suppliers.delete', 'delete', 'Delete suppliers'
    UNION ALL SELECT 'stock', 'View Stock', 'stock.view', 'view', 'View current stock'
    UNION ALL SELECT 'stock', 'Stock In', 'stock.stock_in', 'stock_in', 'Process stock-in transactions'
    UNION ALL SELECT 'stock', 'Stock Out', 'stock.stock_out', 'stock_out', 'Process stock-out transactions'
    UNION ALL SELECT 'stock', 'Adjust Stock', 'stock.adjust', 'adjust', 'Adjust inventory records'
    UNION ALL SELECT 'stock', 'View Stock History', 'stock.history', 'history', 'View stock transaction history'
    UNION ALL SELECT 'reports', 'View Reports', 'reports.view', 'view', 'View reports'
    UNION ALL SELECT 'reports', 'Export Reports', 'reports.export', 'export', 'Export reports'
    UNION ALL SELECT 'users', 'View Users', 'users.view', 'view', 'View system users'
    UNION ALL SELECT 'users', 'Create Users', 'users.create', 'create', 'Create system users'
    UNION ALL SELECT 'users', 'Edit Users', 'users.edit', 'edit', 'Edit system users'
    UNION ALL SELECT 'users', 'Delete Users', 'users.delete', 'delete', 'Delete system users'
    UNION ALL SELECT 'activity_logs', 'View Activity Logs', 'activity_logs.view', 'view', 'View system activity and audit logs'
    UNION ALL SELECT 'roles', 'View Roles', 'roles.view', 'view', 'View roles and assigned permissions'
    UNION ALL SELECT 'roles', 'Create Roles', 'roles.create', 'create', 'Create system roles'
    UNION ALL SELECT 'roles', 'Edit Roles', 'roles.edit', 'edit', 'Edit role information'
    UNION ALL SELECT 'roles', 'Delete Roles', 'roles.delete', 'delete', 'Delete unused roles'
    UNION ALL SELECT 'roles', 'Manage Role Permissions', 'roles.permissions', 'permissions', 'Assign or remove permissions from roles'
) p ON p.module_key = m.module_key
ON DUPLICATE KEY UPDATE
    module_id = VALUES(module_id),
    permission_name = VALUES(permission_name),
    action = VALUES(action),
    description = VALUES(description),
    status = VALUES(status),
    updated_at = VALUES(updated_at);

INSERT INTO role_permissions (role_id, permission_id, created_at, updated_at)
SELECT r.id, p.id, '2025-11-01 08:30:00', '2025-11-01 08:30:00'
FROM roles r
CROSS JOIN permissions p
WHERE r.role_name = 'admin' AND p.status = 1
ON DUPLICATE KEY UPDATE updated_at = VALUES(updated_at);

INSERT INTO role_permissions (role_id, permission_id, created_at, updated_at)
SELECT r.id, p.id, '2025-11-01 08:31:00', '2025-11-01 08:31:00'
FROM roles r
JOIN permissions p ON p.permission_key IN (
    'dashboard.view', 'products.view', 'categories.view', 'suppliers.view',
    'stock.view', 'stock.stock_in', 'stock.stock_out', 'stock.history', 'reports.view'
)
WHERE r.role_name = 'staff'
ON DUPLICATE KEY UPDATE updated_at = VALUES(updated_at);

-- -----------------------------------------------------------------------------
-- 2. Catalog and account data
-- -----------------------------------------------------------------------------
INSERT INTO categories (category_name, status, created_at, updated_at) VALUES
('General', 1, '2025-11-02 09:00:00', '2025-11-02 09:00:00'),
('Electronics', 1, '2025-11-02 09:01:00', '2025-11-02 09:01:00'),
('Office Supplies', 1, '2025-11-02 09:02:00', '2025-11-02 09:02:00'),
('Computer Accessories', 1, '2025-11-02 09:03:00', '2025-11-02 09:03:00'),
('Cleaning Supplies', 1, '2025-11-02 09:04:00', '2025-11-02 09:04:00'),
('Pantry', 1, '2025-11-02 09:05:00', '2025-11-02 09:05:00'),
('Archived Supplies', 0, '2025-11-02 09:06:00', '2025-11-02 09:06:00')
ON DUPLICATE KEY UPDATE
    status = VALUES(status),
    updated_at = VALUES(updated_at);

INSERT INTO suppliers (supplier_name, contact_person, phone, address, status, created_at, updated_at)
SELECT s.supplier_name, s.contact_person, s.phone, s.address, s.status, s.created_at, s.updated_at
FROM (
    SELECT 'Cebu Office Solutions' supplier_name, 'Juan Dela Cruz' contact_person, '09171234567' phone, 'Cebu City, Cebu, Philippines' address, 1 status, '2025-11-03 09:00:00' created_at, '2025-11-03 09:00:00' updated_at
    UNION ALL SELECT 'TechSource Philippines', 'Maria Santos', '09181234567', 'Mandaue City, Cebu, Philippines', 1, '2025-11-03 09:01:00', '2025-11-03 09:01:00'
    UNION ALL SELECT 'Visayas General Trading', 'Pedro Garcia', '09191234567', 'Lapu-Lapu City, Cebu, Philippines', 1, '2025-11-03 09:02:00', '2025-11-03 09:02:00'
    UNION ALL SELECT 'Prime Cleaning Supplies', 'Ana Reyes', '09201234567', 'Cebu City, Cebu, Philippines', 1, '2025-11-03 09:03:00', '2025-11-03 09:03:00'
    UNION ALL SELECT 'Pacific Pantry Supply', 'Carlos Ramos', '09211234567', 'Mandaue City, Cebu, Philippines', 1, '2025-11-03 09:04:00', '2025-11-03 09:04:00'
    UNION ALL SELECT 'Legacy Office Supplier', 'Ramon Cruz', '09221234567', 'Cebu City, Cebu, Philippines', 0, '2025-11-03 09:05:00', '2025-11-03 09:05:00'
) s
WHERE NOT EXISTS (SELECT 1 FROM suppliers existing WHERE existing.supplier_name = s.supplier_name);

UPDATE suppliers
SET contact_person = CASE supplier_name
        WHEN 'Cebu Office Solutions' THEN 'Juan Dela Cruz'
        WHEN 'TechSource Philippines' THEN 'Maria Santos'
        WHEN 'Visayas General Trading' THEN 'Pedro Garcia'
        WHEN 'Prime Cleaning Supplies' THEN 'Ana Reyes'
        WHEN 'Pacific Pantry Supply' THEN 'Carlos Ramos'
        WHEN 'Legacy Office Supplier' THEN 'Ramon Cruz'
        ELSE contact_person END,
    phone = CASE supplier_name
        WHEN 'Cebu Office Solutions' THEN '09171234567'
        WHEN 'TechSource Philippines' THEN '09181234567'
        WHEN 'Visayas General Trading' THEN '09191234567'
        WHEN 'Prime Cleaning Supplies' THEN '09201234567'
        WHEN 'Pacific Pantry Supply' THEN '09211234567'
        WHEN 'Legacy Office Supplier' THEN '09221234567'
        ELSE phone END,
    status = CASE WHEN supplier_name = 'Legacy Office Supplier' THEN 0 ELSE 1 END,
    updated_at = CASE supplier_name
        WHEN 'Cebu Office Solutions' THEN '2025-11-03 09:00:00'
        WHEN 'TechSource Philippines' THEN '2025-11-03 09:01:00'
        WHEN 'Visayas General Trading' THEN '2025-11-03 09:02:00'
        WHEN 'Prime Cleaning Supplies' THEN '2025-11-03 09:03:00'
        WHEN 'Pacific Pantry Supply' THEN '2025-11-03 09:04:00'
        WHEN 'Legacy Office Supplier' THEN '2025-11-03 09:05:00'
        ELSE updated_at END
WHERE supplier_name IN (
    'Cebu Office Solutions', 'TechSource Philippines', 'Visayas General Trading',
    'Prime Cleaning Supplies', 'Pacific Pantry Supply', 'Legacy Office Supplier'
);

INSERT INTO users
(first_name, middle_name, last_name, username, password, must_change_password, role_id, status, created_at, updated_at)
SELECT u.first_name, u.middle_name, u.last_name, u.username, u.password, u.must_change_password, r.id, u.status, u.created_at, u.updated_at
FROM (
    SELECT 'System' first_name, NULL middle_name, 'Administrator' last_name, 'admin' username, '$2y$12$rKP9AsK52IcOtgAQk4H54.3QivpEL0BVjbZj9SiaB6kyB/Dqav2uO' password, 0 must_change_password, 'admin' role_name, 1 status, '2025-11-04 08:00:00' created_at, '2025-11-04 08:00:00' updated_at
    UNION ALL SELECT 'Inventory', NULL, 'Staff', 'staff', '$2y$12$rKP9AsK52IcOtgAQk4H54.3QivpEL0BVjbZj9SiaB6kyB/Dqav2uO', 0, 'staff', 1, '2025-11-04 08:05:00', '2025-11-04 08:05:00'
    UNION ALL SELECT 'Casey', 'M.', 'Auditor', 'auditor', '$2y$12$rKP9AsK52IcOtgAQk4H54.3QivpEL0BVjbZj9SiaB6kyB/Dqav2uO', 1, 'staff', 1, '2026-01-15 10:00:00', '2026-01-15 10:00:00'
    UNION ALL SELECT 'Former', NULL, 'Staff', 'former_staff', '$2y$12$rKP9AsK52IcOtgAQk4H54.3QivpEL0BVjbZj9SiaB6kyB/Dqav2uO', 0, 'staff', 0, '2026-02-10 10:00:00', '2026-08-01 10:00:00'
) u
JOIN roles r ON r.role_name = u.role_name
ON DUPLICATE KEY UPDATE
    first_name = VALUES(first_name),
    middle_name = VALUES(middle_name),
    last_name = VALUES(last_name),
    password = VALUES(password),
    must_change_password = VALUES(must_change_password),
    role_id = VALUES(role_id),
    status = VALUES(status),
    updated_at = VALUES(updated_at);

-- -----------------------------------------------------------------------------
-- 3. Products: healthy, low, out-of-stock, unassigned, and inactive scenarios
-- -----------------------------------------------------------------------------
INSERT INTO products
(category_id, supplier_id, product_code, product_name, unit, cost_price, selling_price, stock, reorder_level, status, created_at, updated_at)
SELECT c.id, s.id, p.product_code, p.product_name, p.unit, p.cost_price, p.selling_price, p.stock, p.reorder_level, p.status, p.created_at, p.updated_at
FROM categories c
LEFT JOIN suppliers s ON s.supplier_name = 'Cebu Office Solutions'
JOIN (SELECT 'General' category_name, 'GEN-001' product_code, 'Ballpen - Blue' product_name, 'box' unit, 120.00 cost_price, 180.00 selling_price, 130 stock, 30 reorder_level, 1 status, '2025-11-10 09:00:00' created_at, '2026-09-29 10:00:00' updated_at) p ON p.category_name = c.category_name
ON DUPLICATE KEY UPDATE product_name=VALUES(product_name), category_id=VALUES(category_id), supplier_id=VALUES(supplier_id), unit=VALUES(unit), cost_price=VALUES(cost_price), selling_price=VALUES(selling_price), stock=VALUES(stock), reorder_level=VALUES(reorder_level), status=VALUES(status), updated_at=VALUES(updated_at);

INSERT INTO products
(category_id, supplier_id, product_code, product_name, unit, cost_price, selling_price, stock, reorder_level, status, created_at, updated_at)
SELECT c.id, s.id, 'GEN-002', 'Notebook - A5', 'pack', 75.00, 110.00, 15, 20, 1, '2025-11-10 09:05:00', '2026-09-20 10:00:00'
FROM categories c LEFT JOIN suppliers s ON s.supplier_name = 'Cebu Office Solutions' WHERE c.category_name='General'
ON DUPLICATE KEY UPDATE product_name=VALUES(product_name), category_id=VALUES(category_id), supplier_id=VALUES(supplier_id), unit=VALUES(unit), cost_price=VALUES(cost_price), selling_price=VALUES(selling_price), stock=VALUES(stock), reorder_level=VALUES(reorder_level), status=VALUES(status), updated_at=VALUES(updated_at);

INSERT INTO products
(category_id, supplier_id, product_code, product_name, unit, cost_price, selling_price, stock, reorder_level, status, created_at, updated_at)
SELECT c.id, NULL, 'GEN-003', 'Packing Tape - Clear', 'roll', 45.00, 70.00, 0, 10, 1, '2025-12-02 09:00:00', '2026-09-15 10:00:00'
FROM categories c WHERE c.category_name='General'
ON DUPLICATE KEY UPDATE product_name=VALUES(product_name), category_id=VALUES(category_id), supplier_id=VALUES(supplier_id), unit=VALUES(unit), cost_price=VALUES(cost_price), selling_price=VALUES(selling_price), stock=VALUES(stock), reorder_level=VALUES(reorder_level), status=VALUES(status), updated_at=VALUES(updated_at);

INSERT INTO products
(category_id, supplier_id, product_code, product_name, unit, cost_price, selling_price, stock, reorder_level, status, created_at, updated_at)
SELECT c.id, s.id, 'ELEC-001', 'USB Keyboard', 'piece', 650.00, 899.00, 13, 5, 1, '2025-11-12 09:00:00', '2026-04-17 10:00:00'
FROM categories c LEFT JOIN suppliers s ON s.supplier_name='TechSource Philippines' WHERE c.category_name='Electronics'
ON DUPLICATE KEY UPDATE product_name=VALUES(product_name), category_id=VALUES(category_id), supplier_id=VALUES(supplier_id), unit=VALUES(unit), cost_price=VALUES(cost_price), selling_price=VALUES(selling_price), stock=VALUES(stock), reorder_level=VALUES(reorder_level), status=VALUES(status), updated_at=VALUES(updated_at);

INSERT INTO products
(category_id, supplier_id, product_code, product_name, unit, cost_price, selling_price, stock, reorder_level, status, created_at, updated_at)
SELECT c.id, s.id, 'ELEC-002', 'Wireless Mouse', 'piece', 420.00, 699.00, 6, 8, 1, '2025-11-12 09:05:00', '2026-04-17 10:00:00'
FROM categories c LEFT JOIN suppliers s ON s.supplier_name='TechSource Philippines' WHERE c.category_name='Electronics'
ON DUPLICATE KEY UPDATE product_name=VALUES(product_name), category_id=VALUES(category_id), supplier_id=VALUES(supplier_id), unit=VALUES(unit), cost_price=VALUES(cost_price), selling_price=VALUES(selling_price), stock=VALUES(stock), reorder_level=VALUES(reorder_level), status=VALUES(status), updated_at=VALUES(updated_at);

INSERT INTO products
(category_id, supplier_id, product_code, product_name, unit, cost_price, selling_price, stock, reorder_level, status, created_at, updated_at)
SELECT c.id, s.id, 'ELEC-003', 'HDMI Cable 2m', 'piece', 350.00, 550.00, 17, 5, 1, '2025-11-12 09:10:00', '2026-09-27 10:00:00'
FROM categories c LEFT JOIN suppliers s ON s.supplier_name='TechSource Philippines' WHERE c.category_name='Electronics'
ON DUPLICATE KEY UPDATE product_name=VALUES(product_name), category_id=VALUES(category_id), supplier_id=VALUES(supplier_id), unit=VALUES(unit), cost_price=VALUES(cost_price), selling_price=VALUES(selling_price), stock=VALUES(stock), reorder_level=VALUES(reorder_level), status=VALUES(status), updated_at=VALUES(updated_at);

INSERT INTO products
(category_id, supplier_id, product_code, product_name, unit, cost_price, selling_price, stock, reorder_level, status, created_at, updated_at)
SELECT c.id, NULL, 'ELEC-004', 'USB-C Hub 6-in-1', 'piece', 950.00, 1399.00, 9, 4, 1, '2026-01-08 09:00:00', '2026-09-15 10:00:00'
FROM categories c WHERE c.category_name='Computer Accessories'
ON DUPLICATE KEY UPDATE product_name=VALUES(product_name), category_id=VALUES(category_id), supplier_id=VALUES(supplier_id), unit=VALUES(unit), cost_price=VALUES(cost_price), selling_price=VALUES(selling_price), stock=VALUES(stock), reorder_level=VALUES(reorder_level), status=VALUES(status), updated_at=VALUES(updated_at);

INSERT INTO products
(category_id, supplier_id, product_code, product_name, unit, cost_price, selling_price, stock, reorder_level, status, created_at, updated_at)
SELECT c.id, s.id, 'OFF-001', 'Bond Paper A4', 'ream', 210.00, 285.00, 55, 10, 1, '2025-11-15 09:00:00', '2026-06-18 10:00:00'
FROM categories c LEFT JOIN suppliers s ON s.supplier_name='Visayas General Trading' WHERE c.category_name='Office Supplies'
ON DUPLICATE KEY UPDATE product_name=VALUES(product_name), category_id=VALUES(category_id), supplier_id=VALUES(supplier_id), unit=VALUES(unit), cost_price=VALUES(cost_price), selling_price=VALUES(selling_price), stock=VALUES(stock), reorder_level=VALUES(reorder_level), status=VALUES(status), updated_at=VALUES(updated_at);

INSERT INTO products
(category_id, supplier_id, product_code, product_name, unit, cost_price, selling_price, stock, reorder_level, status, created_at, updated_at)
SELECT c.id, s.id, 'OFF-002', 'Stapler - Standard', 'piece', 95.00, 145.00, 4, 5, 1, '2025-11-15 09:05:00', '2026-09-20 10:00:00'
FROM categories c LEFT JOIN suppliers s ON s.supplier_name='Visayas General Trading' WHERE c.category_name='Office Supplies'
ON DUPLICATE KEY UPDATE product_name=VALUES(product_name), category_id=VALUES(category_id), supplier_id=VALUES(supplier_id), unit=VALUES(unit), cost_price=VALUES(cost_price), selling_price=VALUES(selling_price), stock=VALUES(stock), reorder_level=VALUES(reorder_level), status=VALUES(status), updated_at=VALUES(updated_at);

INSERT INTO products
(category_id, supplier_id, product_code, product_name, unit, cost_price, selling_price, stock, reorder_level, status, created_at, updated_at)
SELECT c.id, s.id, 'CLN-001', 'Multi-Purpose Cleaner', 'bottle', 135.00, 195.00, 0, 10, 1, '2025-11-20 09:00:00', '2026-08-06 10:00:00'
FROM categories c LEFT JOIN suppliers s ON s.supplier_name='Prime Cleaning Supplies' WHERE c.category_name='Cleaning Supplies'
ON DUPLICATE KEY UPDATE product_name=VALUES(product_name), category_id=VALUES(category_id), supplier_id=VALUES(supplier_id), unit=VALUES(unit), cost_price=VALUES(cost_price), selling_price=VALUES(selling_price), stock=VALUES(stock), reorder_level=VALUES(reorder_level), status=VALUES(status), updated_at=VALUES(updated_at);

INSERT INTO products
(category_id, supplier_id, product_code, product_name, unit, cost_price, selling_price, stock, reorder_level, status, created_at, updated_at)
SELECT c.id, s.id, 'CLN-002', 'Tissue Paper', 'pack', 85.00, 125.00, 60, 15, 1, '2025-11-20 09:05:00', '2026-08-06 10:00:00'
FROM categories c LEFT JOIN suppliers s ON s.supplier_name='Prime Cleaning Supplies' WHERE c.category_name='Cleaning Supplies'
ON DUPLICATE KEY UPDATE product_name=VALUES(product_name), category_id=VALUES(category_id), supplier_id=VALUES(supplier_id), unit=VALUES(unit), cost_price=VALUES(cost_price), selling_price=VALUES(selling_price), stock=VALUES(stock), reorder_level=VALUES(reorder_level), status=VALUES(status), updated_at=VALUES(updated_at);

INSERT INTO products
(category_id, supplier_id, product_code, product_name, unit, cost_price, selling_price, stock, reorder_level, status, created_at, updated_at)
SELECT c.id, s.id, 'PAN-001', 'Bottled Water 500ml', 'case', 180.00, 240.00, 100, 25, 1, '2025-12-01 09:00:00', '2026-08-28 10:00:00'
FROM categories c LEFT JOIN suppliers s ON s.supplier_name='Pacific Pantry Supply' WHERE c.category_name='Pantry'
ON DUPLICATE KEY UPDATE product_name=VALUES(product_name), category_id=VALUES(category_id), supplier_id=VALUES(supplier_id), unit=VALUES(unit), cost_price=VALUES(cost_price), selling_price=VALUES(selling_price), stock=VALUES(stock), reorder_level=VALUES(reorder_level), status=VALUES(status), updated_at=VALUES(updated_at);

INSERT INTO products
(category_id, supplier_id, product_code, product_name, unit, cost_price, selling_price, stock, reorder_level, status, created_at, updated_at)
SELECT c.id, s.id, 'PAN-002', 'Coffee 3-in-1', 'box', 145.00, 210.00, 40, 10, 1, '2025-12-01 09:05:00', '2026-08-28 10:00:00'
FROM categories c LEFT JOIN suppliers s ON s.supplier_name='Pacific Pantry Supply' WHERE c.category_name='Pantry'
ON DUPLICATE KEY UPDATE product_name=VALUES(product_name), category_id=VALUES(category_id), supplier_id=VALUES(supplier_id), unit=VALUES(unit), cost_price=VALUES(cost_price), selling_price=VALUES(selling_price), stock=VALUES(stock), reorder_level=VALUES(reorder_level), status=VALUES(status), updated_at=VALUES(updated_at);

INSERT INTO products
(category_id, supplier_id, product_code, product_name, unit, cost_price, selling_price, stock, reorder_level, status, created_at, updated_at)
SELECT c.id, s.id, 'GEN-004', 'Legacy Filing Labels', 'pack', 60.00, 90.00, 0, 5, 0, '2025-11-25 09:00:00', '2026-08-01 10:00:00'
FROM categories c LEFT JOIN suppliers s ON s.supplier_name='Legacy Office Supplier' WHERE c.category_name='Archived Supplies'
ON DUPLICATE KEY UPDATE product_name=VALUES(product_name), category_id=VALUES(category_id), supplier_id=VALUES(supplier_id), unit=VALUES(unit), cost_price=VALUES(cost_price), selling_price=VALUES(selling_price), stock=VALUES(stock), reorder_level=VALUES(reorder_level), status=VALUES(status), updated_at=VALUES(updated_at);

-- -----------------------------------------------------------------------------
-- 4. Stock movement history (all rows use natural transaction numbers)
-- -----------------------------------------------------------------------------
INSERT INTO stock_transactions (transaction_no, type, supplier_id, remarks, created_by, created_at, updated_at)
SELECT x.transaction_no, x.type, s.id, x.remarks, u.id, x.created_at, x.created_at
FROM (
    SELECT 'SIN-2025-1101' transaction_no, 'stock_in' type, 'Cebu Office Solutions' supplier_name, 'Initial office supplies replenishment' remarks, 'admin' username, '2025-11-12 10:00:00' created_at
    UNION ALL SELECT 'SOUT-2025-1204', 'stock_out', 'Cebu Office Solutions', 'Issued to front desk and training room', 'staff', '2025-12-04 14:00:00'
    UNION ALL SELECT 'SIN-2026-0108', 'stock_in', 'Cebu Office Solutions', 'January replenishment', 'admin', '2026-01-08 09:30:00'
    UNION ALL SELECT 'SOUT-2026-0219', 'stock_out', 'Cebu Office Solutions', 'Issued for onboarding kits', 'staff', '2026-02-19 15:15:00'
    UNION ALL SELECT 'SIN-2026-0303', 'stock_in', 'TechSource Philippines', 'IT equipment delivery', 'admin', '2026-03-03 11:00:00'
    UNION ALL SELECT 'SOUT-2026-0417', 'stock_out', 'TechSource Philippines', 'Issued to support team', 'staff', '2026-04-17 13:45:00'
    UNION ALL SELECT 'SIN-2026-0509', 'stock_in', 'Visayas General Trading', 'Office paper and stapler delivery', 'admin', '2026-05-09 08:45:00'
    UNION ALL SELECT 'SOUT-2026-0618', 'stock_out', 'Visayas General Trading', 'Monthly office consumption', 'staff', '2026-06-18 16:00:00'
    UNION ALL SELECT 'SIN-2026-0722', 'stock_in', 'Prime Cleaning Supplies', 'Cleaning supplies delivery', 'admin', '2026-07-22 10:15:00'
    UNION ALL SELECT 'SOUT-2026-0806', 'stock_out', 'Prime Cleaning Supplies', 'Facilities usage', 'staff', '2026-08-06 15:30:00'
    UNION ALL SELECT 'SIN-2026-0812', 'stock_in', 'Pacific Pantry Supply', 'Pantry replenishment', 'admin', '2026-08-12 09:00:00'
    UNION ALL SELECT 'SOUT-2026-0828', 'stock_out', 'Pacific Pantry Supply', 'Pantry weekly issue', 'staff', '2026-08-28 17:00:00'
    UNION ALL SELECT 'SIN-2026-0901', 'stock_in', NULL, 'Unassigned Products receipt', 'admin', '2026-09-01 09:00:00'
    UNION ALL SELECT 'SOUT-2026-0915', 'stock_out', NULL, 'Unassigned Products issue', 'staff', '2026-09-15 11:30:00'
    UNION ALL SELECT 'ADJUSTMENT-2026-0920-0001', 'adjustment', NULL, 'Physical count found one extra stapler', 'admin', '2026-09-20 10:00:00'
    UNION ALL SELECT 'ADJUSTMENT-2026-0927-0001', 'adjustment', NULL, 'Damaged HDMI cable found during count', 'admin', '2026-09-27 10:00:00'
    UNION ALL SELECT 'SIN-2026-0929', 'stock_in', 'Cebu Office Solutions', 'Same-day emergency replenishment', 'admin', '2026-09-29 08:30:00'
    UNION ALL SELECT 'SOUT-2026-0929', 'stock_out', 'Cebu Office Solutions', 'Same-day issue to operations', 'staff', '2026-09-29 13:30:00'
) x
JOIN users u ON u.username = x.username
LEFT JOIN suppliers s ON s.supplier_name = x.supplier_name
WHERE NOT EXISTS (SELECT 1 FROM stock_transactions t WHERE t.transaction_no = x.transaction_no);

INSERT INTO stock_transaction_items (transaction_id, product_id, quantity, cost_price, created_at, updated_at)
SELECT t.id, p.id, x.quantity, p.cost_price, x.created_at, x.created_at
FROM (
    SELECT 'SIN-2025-1101' transaction_no, 'GEN-001' product_code, 160 quantity, '2025-11-12 10:00:00' created_at
    UNION ALL SELECT 'SIN-2025-1101', 'GEN-002', 45, '2025-11-12 10:00:00'
    UNION ALL SELECT 'SOUT-2025-1204', 'GEN-001', 20, '2025-12-04 14:00:00'
    UNION ALL SELECT 'SOUT-2025-1204', 'GEN-002', 10, '2025-12-04 14:00:00'
    UNION ALL SELECT 'SIN-2026-0108', 'GEN-001', 30, '2026-01-08 09:30:00'
    UNION ALL SELECT 'SIN-2026-0108', 'GEN-002', 10, '2026-01-08 09:30:00'
    UNION ALL SELECT 'SOUT-2026-0219', 'GEN-001', 40, '2026-02-19 15:15:00'
    UNION ALL SELECT 'SOUT-2026-0219', 'GEN-002', 30, '2026-02-19 15:15:00'
    UNION ALL SELECT 'SIN-2026-0303', 'ELEC-001', 30, '2026-03-03 11:00:00'
    UNION ALL SELECT 'SIN-2026-0303', 'ELEC-002', 20, '2026-03-03 11:00:00'
    UNION ALL SELECT 'SIN-2026-0303', 'ELEC-003', 18, '2026-03-03 11:00:00'
    UNION ALL SELECT 'SOUT-2026-0417', 'ELEC-001', 17, '2026-04-17 13:45:00'
    UNION ALL SELECT 'SOUT-2026-0417', 'ELEC-002', 14, '2026-04-17 13:45:00'
    UNION ALL SELECT 'SIN-2026-0509', 'OFF-001', 60, '2026-05-09 08:45:00'
    UNION ALL SELECT 'SIN-2026-0509', 'OFF-002', 8, '2026-05-09 08:45:00'
    UNION ALL SELECT 'SOUT-2026-0618', 'OFF-001', 5, '2026-06-18 16:00:00'
    UNION ALL SELECT 'SOUT-2026-0618', 'OFF-002', 5, '2026-06-18 16:00:00'
    UNION ALL SELECT 'SIN-2026-0722', 'CLN-001', 25, '2026-07-22 10:15:00'
    UNION ALL SELECT 'SIN-2026-0722', 'CLN-002', 70, '2026-07-22 10:15:00'
    UNION ALL SELECT 'SOUT-2026-0806', 'CLN-001', 25, '2026-08-06 15:30:00'
    UNION ALL SELECT 'SOUT-2026-0806', 'CLN-002', 10, '2026-08-06 15:30:00'
    UNION ALL SELECT 'SIN-2026-0812', 'PAN-001', 120, '2026-08-12 09:00:00'
    UNION ALL SELECT 'SIN-2026-0812', 'PAN-002', 50, '2026-08-12 09:00:00'
    UNION ALL SELECT 'SOUT-2026-0828', 'PAN-001', 20, '2026-08-28 17:00:00'
    UNION ALL SELECT 'SOUT-2026-0828', 'PAN-002', 10, '2026-08-28 17:00:00'
    UNION ALL SELECT 'SIN-2026-0901', 'ELEC-004', 12, '2026-09-01 09:00:00'
    UNION ALL SELECT 'SIN-2026-0901', 'GEN-003', 25, '2026-09-01 09:00:00'
    UNION ALL SELECT 'SOUT-2026-0915', 'ELEC-004', 3, '2026-09-15 11:30:00'
    UNION ALL SELECT 'SOUT-2026-0915', 'GEN-003', 25, '2026-09-15 11:30:00'
    UNION ALL SELECT 'ADJUSTMENT-2026-0920-0001', 'OFF-002', 1, '2026-09-20 10:00:00'
    UNION ALL SELECT 'ADJUSTMENT-2026-0927-0001', 'ELEC-003', 1, '2026-09-27 10:00:00'
    UNION ALL SELECT 'SIN-2026-0929', 'GEN-001', 5, '2026-09-29 08:30:00'
    UNION ALL SELECT 'SOUT-2026-0929', 'GEN-001', 5, '2026-09-29 13:30:00'
) x
JOIN stock_transactions t ON t.transaction_no = x.transaction_no
JOIN products p ON p.product_code = x.product_code
WHERE NOT EXISTS (
    SELECT 1 FROM stock_transaction_items existing
    WHERE existing.transaction_id = t.id AND existing.product_id = p.id
);

-- -----------------------------------------------------------------------------
-- 5. Physical stock adjustments, including both directions
-- -----------------------------------------------------------------------------
UPDATE stock_adjustments a
JOIN products p ON p.id = a.product_id
JOIN (
    SELECT 'ADJUSTMENT-2026-0920-0001' transaction_no, 'OFF-002' product_code, 'Physical count found one extra stapler' reason, '2026-09-20 10:00:00' created_at
    UNION ALL SELECT 'ADJUSTMENT-2026-0927-0001', 'ELEC-003', 'Damaged HDMI cable found during count', '2026-09-27 10:00:00'
) x ON x.product_code = p.product_code AND x.reason = a.reason AND x.created_at = a.created_at
JOIN stock_transactions t ON t.transaction_no = x.transaction_no AND t.type = 'adjustment'
JOIN stock_transaction_items i ON i.transaction_id = t.id AND i.product_id = p.id
SET a.transaction_id = t.id
WHERE a.transaction_id IS NULL
  AND a.created_by = t.created_by
  AND t.remarks = a.reason;

INSERT INTO stock_adjustments
(transaction_id, product_id, system_stock, actual_stock, difference, reason, created_by, created_at, updated_at)
SELECT t.id, p.id, x.system_stock, x.actual_stock, x.difference, x.reason, u.id, x.created_at, x.created_at
FROM (
    SELECT 'ADJUSTMENT-2026-0920-0001' transaction_no, 'OFF-002' product_code, 3 system_stock, 4 actual_stock, 1 difference, 'Physical count found one extra stapler' reason, 'admin' username, '2026-09-20 10:00:00' created_at
    UNION ALL SELECT 'ADJUSTMENT-2026-0927-0001', 'ELEC-003', 18, 17, -1, 'Damaged HDMI cable found during count', 'admin', '2026-09-27 10:00:00'
) x
JOIN products p ON p.product_code = x.product_code
JOIN users u ON u.username = x.username
JOIN stock_transactions t ON t.transaction_no = x.transaction_no AND t.type = 'adjustment' AND t.created_by = u.id
JOIN stock_transaction_items i ON i.transaction_id = t.id AND i.product_id = p.id
WHERE NOT EXISTS (
    SELECT 1 FROM stock_adjustments a
    WHERE a.product_id = p.id AND a.reason = x.reason AND a.created_at = x.created_at
);

-- -----------------------------------------------------------------------------
-- 6. Audit trail attached to real users and real records
-- -----------------------------------------------------------------------------
INSERT INTO activity_logs (user_id, action, description, ip_address, created_at, updated_at)
SELECT u.id, x.action, x.description, x.ip_address, x.created_at, x.created_at
FROM (
    SELECT 'admin' username, 'LOGIN' action, 'Administrator signed in' description, '127.0.0.1' ip_address, '2026-09-29 08:00:00' created_at
    UNION ALL SELECT 'staff', 'LOGIN', 'Inventory staff signed in', '127.0.0.1', '2026-09-29 08:10:00'
    UNION ALL SELECT 'admin', 'PRODUCT_CREATED', 'Created product ELEC-004 USB-C Hub 6-in-1', '127.0.0.1', '2026-01-08 09:00:00'
    UNION ALL SELECT 'admin', 'STOCK_IN', 'Processed SIN-2026-0929', '127.0.0.1', '2026-09-29 08:30:00'
    UNION ALL SELECT 'staff', 'STOCK_OUT', 'Processed SOUT-2026-0929', '127.0.0.1', '2026-09-29 13:30:00'
    UNION ALL SELECT 'admin', 'stock_adjustment', 'Processed ADJUSTMENT-2026-0927-0001', '127.0.0.1', '2026-09-27 10:00:00'
    UNION ALL SELECT 'admin', 'REPORT_EXPORT', 'Exported movement report for Sep 1-29, 2026', '127.0.0.1', '2026-09-29 14:00:00'
) x
JOIN users u ON u.username = x.username
WHERE NOT EXISTS (
    SELECT 1 FROM activity_logs existing
    WHERE existing.user_id = u.id AND existing.action = x.action
      AND existing.description = x.description AND existing.created_at = x.created_at
);

COMMIT;

-- Quick post-import checks (safe to run manually):
-- SELECT COUNT(*) FROM roles;
-- SELECT COUNT(*) FROM modules;
-- SELECT COUNT(*) FROM permissions;
-- SELECT COUNT(*) FROM role_permissions;
-- SELECT COUNT(*) FROM users;
-- SELECT COUNT(*) FROM categories;
-- SELECT COUNT(*) FROM suppliers;
-- SELECT COUNT(*) FROM products;
-- SELECT COUNT(*) FROM stock_transactions;
-- SELECT COUNT(*) FROM stock_transaction_items;
-- SELECT COUNT(*) FROM stock_adjustments;
-- SELECT COUNT(*) FROM activity_logs;
