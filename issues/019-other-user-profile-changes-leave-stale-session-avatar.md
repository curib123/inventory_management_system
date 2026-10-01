# Issue 019 — Another administrator's profile edit leaves the user's avatar stale

**Severity:** Medium  
**Status:** Open; proposed fix only  
**Reviewed commit:** `9c7229442fa48a8edd66898d970ace71e9bea17a`

## Evidence and reproduction

1. Sign in as user A in one browser and an administrator B in another.
2. B edits A's first/last name or uploads a replacement profile image.
3. A reloads a permitted page without signing out.

A's navigation retains the old session name and image filename. `User_service::save()` deletes the previous image after replacing it. The new missing-file fallback correctly shows initials, but it cannot discover the replacement image from stale session data.

Relevant code:

- `application/controllers/Users.php` refreshes identity only when the edited user is the current session user.
- `application/models/User_model.php::save()` increments `auth_version` only for a password update.
- `application/hooks/Session_version_guard.php::enforce()` checks the version, but does not refresh identity.
- The shared header reads identity from session data.

This differs from issue 011: that guide concerns **self-edit** and redirect selection, already handled in the controller. This case concerns a different user's existing session.

## Fix with code

Replace only `Session_version_guard::enforce()` with the method below. Keep `versions_match()` unchanged. Reuse `User_service::session_identity()`, which already checks active user/role status and returns current identity fields.

```php
public function enforce() {
    $CI =& get_instance();

    if (!$CI->session->userdata('logged_in')) {
        return;
    }

    $user_id = (int) $CI->session->userdata('user_id');
    $session_version = $CI->session->userdata('auth_version');
    $identity = FALSE;

    if ($user_id > 0) {
        try {
            $CI->load->library('User_service');
            $identity = $CI->user_service->session_identity($user_id);
        } catch (Throwable $exception) {
            log_message('error', 'Unable to validate the authenticated session identity.');
        }
    }

    $current_version = is_array($identity)
        ? $identity['auth_version']
        : NULL;

    if (!self::versions_match($session_version, $current_version)) {
        $CI->session->sess_destroy();
        redirect('login');
        return;
    }

    $CI->session->set_userdata($identity);
}
```

Compare versions **before** updating session data. Updating the version first would defeat password-reset revocation. Do not overwrite `password_change_deferred`; the returned identity intentionally does not contain that field. Do not change permissions based on client-provided values.

## Verify

```bash
php -l application/hooks/Session_version_guard.php
vendor/bin/phpunit --filter 'SessionVersionGuardTest|UserSessionIdentityTest|UserAuthVersionTest'
```

Repeat the two-browser scenario: A's next request should show B's updated name and replacement photo; B's own session identity should remain unchanged. Also confirm an admin password reset still logs A out, inactive users/roles are rejected, and anonymous requests do not perform an identity lookup.

**Validation performed during analysis:** traced the real save/controller/hook paths and exercised the existing hook with stale session data and the proposed hook with a fixture identity. No live database or real user account was changed.
