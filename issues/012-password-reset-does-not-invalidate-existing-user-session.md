# Issue 012 — Admin password reset does not invalidate an already active user session

**Severity:** High  
**Status:** Open  
**Area:** User management / authentication security

## What is wrong

When an administrator supplies a replacement password in `User_service::save()`, the database password hash is changed and `must_change_password` is set to 1.

But existing sessions for that user are not revoked.

The user's already-open session also retains its old session value for `must_change_password`, so the account can continue operating without seeing the forced-change state until a future login.

Files involved:

- `application/libraries/User_service.php`
- `application/controllers/Users.php`
- session/authentication design

## Security impact

An administrator may reset a password because an account is suspected to be compromised. A stolen or already-authenticated session can continue to work after the password reset.

Changing credentials should have a clear session-revocation policy.

## Fix guide

Use a server-verifiable session version.

One practical design:

1. Add an `auth_version` or `session_version` integer to `users`.
2. Store that version in the session at login.
3. Increment it on:
   - admin password reset;
   - user password change if you want all other sessions revoked;
   - security-sensitive account recovery.
4. On authenticated requests, compare session version with the current DB value.
5. If it differs, destroy the session and require login again.

Alternative: maintain a sessions table keyed by user and delete all active user sessions on reset.

Also make sure the newly reset user sees `must_change_password = 1` on their next valid login.

## Tests to add

- login user → admin resets password → old session is rejected;
- old password no longer authenticates;
- new temporary/reset password authenticates;
- new login receives `must_change_password = true`.

## Definition of done

- Password reset has a defined and enforced session revocation behavior.
- Existing compromised sessions cannot continue indefinitely after reset.
