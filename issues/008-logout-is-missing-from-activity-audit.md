# Issue 008 — Logout is not recorded in Activity Logs

**Severity:** Medium  
**Status:** Open  
**Area:** Authentication / audit completeness

## What is wrong

Successful login is logged by `Auth_service::authenticate()`, but `Auth::logout()` destroys the session immediately without recording a logout event.

Files:

- `application/controllers/Auth.php`
- `application/libraries/Auth_service.php` or a dedicated audit/auth service

## Why it matters

The Activity Logs feature is intended for audit purposes. Without logout records, an auditor can see when a session began but cannot distinguish:

- explicit user logout;
- session expiration;
- browser abandonment.

At minimum, explicit logout should be traceable.

## Fix guide

Before destroying the session:

1. capture the current `user_id`;
2. capture username if useful;
3. insert a `logout_created` or preferably a consistent action such as `user_logout`;
4. include IP address;
5. only then destroy the session.

Prefer putting the behavior in `Auth_service` rather than adding more business logic to the controller.

If the audit insert is considered mandatory, define what should happen if logging fails. Do not destroy the identity before capturing the user ID.

## Tests to add

- POST logout while logged in → one logout audit row, then session destroyed;
- GET logout → 405 and no logout row;
- anonymous POST logout → define and test desired behavior.

## Definition of done

- Explicit logout creates exactly one audit record with the correct user.
- Session is still destroyed and redirected to login.
- Login and logout action naming is consistent enough for Activity Log filtering.
