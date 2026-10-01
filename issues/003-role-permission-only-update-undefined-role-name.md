# Issue 003 — Permission-only role updates use an undefined role name

**Severity:** High  
**Status:** Open  
**Area:** Roles / audit logging

## What is wrong

`Role_service::save()` only defines `$role_name` when `$can_edit_role` is true.

Later, the permission-only branch does this:

`$description = 'Updated permissions for role: ' . $role_name;`

That branch specifically runs when:

- `$can_edit_role === FALSE`
- `$can_manage_permissions === TRUE`

So `$role_name` is not initialized in that path.

Files involved:

- `application/libraries/Role_service.php`
- `application/controllers/Roles.php`

## Why it matters

A user who has `roles.permissions` but not `roles.edit` can save permission changes. The save may succeed, but PHP can emit an undefined-variable warning and the audit description may contain a blank role name.

In stricter environments/error handlers this can also disrupt the request.

## How to reproduce

1. Create a role for an operator.
2. Give the operator `roles.view` + `roles.permissions`, but not `roles.edit`.
3. Log in as that operator.
4. Open a role and change permissions.
5. Save.
6. Check PHP logs and `activity_logs.description`.

## Root cause

The service derives the role name only from editable POST data. Permission-only edits intentionally do not post editable role fields, so the service must read the existing role instead.

## Fix guide

At the beginning of `Role_service::save()`:

1. If `$id` is not null, load the current role with `Role_model->get_by_id($id)`.
2. Return a not-found result if it does not exist.
3. Initialize `$role_name` from the existing role.
4. If `$can_edit_role` is true, replace it with the validated posted role name.
5. Use the guaranteed `$role_name` when building audit descriptions.

Do not solve this by suppressing notices or using an empty fallback string; the audit record should identify the actual role.

## Tests to add

Add a Role_service test where:

- role editing is disabled;
- permission management is enabled;
- existing role name is `warehouse_staff`;
- permission replacement succeeds.

Assert that the activity description contains `warehouse_staff` and no undefined variable is produced.

## Definition of done

- Permission-only changes save normally.
- Audit log always contains the correct role name.
- No undefined-variable warning occurs.
- Full role edit/create flows still use the submitted validated role name.
