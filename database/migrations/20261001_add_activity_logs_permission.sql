-- Dedicated Activity Logs permission.
-- Safe for existing databases because module/permission inserts are idempotent.
-- Existing admin roles receive the permission automatically; other roles must be assigned explicitly.

INSERT INTO modules
(module_name, module_key, description, status, sort_order)
VALUES
('Activity Logs', 'activity_logs', 'Audit and activity history', 1, 75)
ON DUPLICATE KEY UPDATE
    module_name = VALUES(module_name),
    description = VALUES(description),
    status = VALUES(status),
    sort_order = VALUES(sort_order);

INSERT INTO permissions
(module_id, permission_name, permission_key, action, description, status)
SELECT id, 'View Activity Logs', 'activity_logs.view', 'view',
       'View system activity and audit logs', 1
FROM modules
WHERE module_key = 'activity_logs'
ON DUPLICATE KEY UPDATE
    module_id = VALUES(module_id),
    permission_name = VALUES(permission_name),
    action = VALUES(action),
    description = VALUES(description),
    status = VALUES(status);

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.permission_key = 'activity_logs.view'
WHERE r.role_name = 'admin'
ON DUPLICATE KEY UPDATE
    role_id = VALUES(role_id);
