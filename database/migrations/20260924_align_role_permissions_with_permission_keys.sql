-- Add Role module permissions required by the permission-key authorization design.
-- Safe to run on databases that already contain the new modules/permissions schema.

INSERT INTO modules
(module_name, module_key, description, status, sort_order)
VALUES
('Roles', 'roles', 'Role and permission management', 1, 80)
ON DUPLICATE KEY UPDATE
    module_name = VALUES(module_name),
    description = VALUES(description),
    status = VALUES(status),
    sort_order = VALUES(sort_order);

INSERT INTO permissions
(module_id, permission_name, permission_key, action, description, status)
SELECT id, 'View Roles', 'roles.view', 'view',
       'View roles and assigned permissions', 1
FROM modules WHERE module_key = 'roles'
ON DUPLICATE KEY UPDATE
    permission_name = VALUES(permission_name),
    description = VALUES(description),
    status = VALUES(status);

INSERT INTO permissions
(module_id, permission_name, permission_key, action, description, status)
SELECT id, 'Create Roles', 'roles.create', 'create',
       'Create system roles', 1
FROM modules WHERE module_key = 'roles'
ON DUPLICATE KEY UPDATE
    permission_name = VALUES(permission_name),
    description = VALUES(description),
    status = VALUES(status);

INSERT INTO permissions
(module_id, permission_name, permission_key, action, description, status)
SELECT id, 'Edit Roles', 'roles.edit', 'edit',
       'Edit role information', 1
FROM modules WHERE module_key = 'roles'
ON DUPLICATE KEY UPDATE
    permission_name = VALUES(permission_name),
    description = VALUES(description),
    status = VALUES(status);

INSERT INTO permissions
(module_id, permission_name, permission_key, action, description, status)
SELECT id, 'Delete Roles', 'roles.delete', 'delete',
       'Delete unused roles', 1
FROM modules WHERE module_key = 'roles'
ON DUPLICATE KEY UPDATE
    permission_name = VALUES(permission_name),
    description = VALUES(description),
    status = VALUES(status);

INSERT INTO permissions
(module_id, permission_name, permission_key, action, description, status)
SELECT id, 'Manage Role Permissions', 'roles.permissions', 'permissions',
       'Assign or remove permissions from roles', 1
FROM modules WHERE module_key = 'roles'
ON DUPLICATE KEY UPDATE
    permission_name = VALUES(permission_name),
    description = VALUES(description),
    status = VALUES(status);

-- Existing admin role receives all currently active permissions.
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
CROSS JOIN permissions p
WHERE r.role_name = 'admin'
  AND p.status = 1
ON DUPLICATE KEY UPDATE
    role_id = VALUES(role_id);
