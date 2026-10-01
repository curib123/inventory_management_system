# Issue 005 — Auth_service dependency injection breaks successful authentication

**Severity:** High  
**Status:** Open  
**Area:** Authentication / automated tests

## What is wrong

`Auth_service::__construct()` supports injecting only `user_model` for unit testing and returns early.

That leaves `$this->CI` unset unless a CI dependency is also explicitly passed.

On valid authentication, `authenticate()` now always executes:

`$this->CI->Activity_log_model->insert_activity_log(...)`

So a unit test that injects a user model but not a CI instance can crash on successful credentials.

This exact pattern exists in:

- `tests/AuthServiceTest.php::testAuthenticateReturnsSessionDataForValidCredentials()`

Files involved:

- `application/libraries/Auth_service.php`
- `tests/AuthServiceTest.php`

## Why it matters

The service's dependency-injection contract and its tests are no longer compatible after login audit logging was added.

Invalid-login tests may still pass because they return before the log call, hiding the problem.

## Fix guide

Use explicit dependencies instead of reaching through CI from the unit-test path.

Recommended design:

1. Add an `activity_log_model` dependency/property to `Auth_service`.
2. In production construction, load `User_model` and `Activity_log_model` from CI.
3. In injected/test construction, accept both model dependencies.
4. For IP address, either:
   - inject a small callable/context dependency, or
   - safely use CI only when available.
5. Update `AuthServiceTest` with an activity-log mock and assert one login log on valid authentication.
6. Assert no login-success log on invalid credentials.

Avoid simply checking `if ($this->CI)` and silently skipping audit logging in tests; that leaves behavior untested.

## Tests to add/update

- valid credentials → returns session + one activity log;
- invalid password → false + zero success logs;
- inactive/missing user → false + zero success logs.

## Definition of done

- `AuthServiceTest::testAuthenticateReturnsSessionDataForValidCredentials` passes.
- Successful production login still creates one audit row.
- Invalid login does not create a successful-login row.
- Auth_service can be instantiated in unit tests without a full CodeIgniter runtime.
