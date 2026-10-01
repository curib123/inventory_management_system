# Issue 011 — Editing your own role leaves stale session data and can redirect to a forbidden page

**Severity:** Medium-High  
**Status:** Open  
**Area:** User management / session consistency

## What is wrong

An administrator with `users.edit` can edit their own account and assign themselves a different role.

`User_service::save()` updates the database role, but the active session still contains the old:

- `role_id`
- `role_name`

The controller only has special handling for `self_deactivated`. After a normal self role change it always redirects to `users`.

Files involved:

- `application/libraries/User_service.php`
- `application/controllers/Users.php`
- `application/views/templates/header.php`

The header renders the role label from session data, so it can show the old role even though permission checks read the new database role.

## Failure scenario

1. Admin edits their own user.
2. Changes role from `admin` to a role without `users.view`.
3. Save succeeds.
4. Controller redirects to `/users`.
5. The next request returns 403 because the new role no longer has access.
6. Header/session identity may still show the old role name on any reachable page.

## Root cause

The database identity changes, but session identity is not refreshed and redirect selection assumes the old permissions.

## Fix guide

After a successful update where `$id === current_session_user_id`:

1. Reload the updated user + role from the database.
2. Refresh session identity fields:
   - role_id
   - role_name
   - first_name/last_name/username if editable values changed
3. Re-evaluate the first authorized route using `Authorization_service::first_authorized_route()`.
4. Redirect there instead of always redirecting to `users`.
5. If the new role is inactive or has no page permission, destroy the session or send the user to a safe no-access/logout flow.

Do not trust the old session role after self-edit.

## Tests to add

- self role change to lower privilege refreshes session role;
- redirect goes to a page the new role can access;
- self role change to no-access role produces a controlled outcome;
- editing another user does not change the current admin session.

## Definition of done

- Session role matches the database immediately after self-edit.
- No successful self-role change redirects directly into a 403 page.
- Header role label is current.
