# Issue 010 — Login has no brute-force rate limiting

**Severity:** High  
**Status:** Open  
**Area:** Authentication / security

## What is wrong

`Auth::login()` validates username/password and immediately calls `Auth_service::authenticate()` for every valid POST.

There is no visible mechanism for:

- per-IP throttling;
- per-username throttling;
- temporary lockout;
- exponential delay;
- attempt counters;
- cooldown windows.

Files involved:

- `application/controllers/Auth.php`
- `application/libraries/Auth_service.php`
- optionally a new persistence/cache layer for attempts

## Why this matters

An attacker can repeatedly submit password guesses without the application slowing or blocking attempts.

The generic “Invalid username or password” message is good for avoiding username enumeration, but it does not stop automated guessing.

## Fix guide

Implement server-side throttling before password verification.

Recommended minimum:

1. Track failed attempts by both normalized username and IP.
2. Use a short rolling window, for example 5 failed attempts within several minutes.
3. Apply a cooldown or exponential delay after the threshold.
4. Reset/reduce the counter after successful authentication.
5. Keep the client error generic.
6. Log excessive failed-login activity separately without logging submitted passwords.
7. Do not rely only on JavaScript; enforcement must be server-side.

Storage options:

- dedicated `login_attempts` table;
- Redis/cache if the deployment has it;
- another server-side rate-limit store.

If deployed behind a reverse proxy, verify trusted proxy handling before using forwarded IP headers.

## Tests to add

- repeated failures eventually trigger throttling;
- another username is independently tracked;
- successful login clears or reduces failure state;
- lockout expires;
- password values are never written to logs.

## Definition of done

- Automated unlimited login guessing is no longer possible.
- Throttling is enforced server-side.
- Valid users recover automatically after the cooldown.
- Error messages do not disclose whether a username exists.
