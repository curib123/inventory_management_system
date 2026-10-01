# Issue 015 — Role editor can lock out the last administrator

**Severity:** High  
**Status:** Open  
**Area:** Roles / availability / authorization

## What is wrong

The role editor allows authorized users to change role status and permissions, including the role they currently use.

There is no visible invariant preventing the system from ending up with zero usable administrators who can manage roles/permissions.

Examples:

- deactivate the only admin role;
- remove `roles.view` / `roles.permissions` from the only administrative role;
- move the only administrative user to a non-admin role.

Files involved:

- `application/libraries/Role_service.php`
- `application/controllers/Roles.php`
- `application/libraries/User_service.php`

## Why this is serious

After the change, all remaining users can lose the ability to restore permissions through the UI.

The initial setup route is not a recovery mechanism once users already exist, so recovery may require direct database editing.

## Fix guide

Define a “minimum administrative capability” invariant.

A practical rule:

At least one active user must remain assigned to an active role that has both:

- `roles.view`
- `roles.permissions`

Before committing any role/user change that could remove that last capability:

1. calculate the post-change state;
2. reject the save if it would leave zero qualifying users;
3. return a clear message explaining why.

Apply the check to both:

- role permission/status changes;
- user role/status changes.

Do this inside the service/transaction layer, not only in the UI.

## Tests to add

- removing admin permission when a second admin remains → allowed;
- removing it from the last capable admin → rejected;
- deactivating the last admin user → rejected;
- deactivating an admin role with another independent admin role → allowed.

## Definition of done

- Normal RBAC editing still works.
- The application cannot remove its final UI-capable administrator through normal forms.
- Recovery does not require manual SQL for a preventable admin edit.
