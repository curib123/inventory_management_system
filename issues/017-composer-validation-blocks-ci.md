# Issue 017 — Composer metadata validation stops GitHub Actions

**Severity:** Medium  
**Status:** Open; proposed fix only  
**Reviewed commit:** `9c7229442fa48a8edd66898d970ace71e9bea17a`

## Evidence and impact

`.github/workflows/php.yml` runs `composer validate --strict`. The current `composer.json` has no `name`, `description`, or `license`. The merged avatar change's [workflow run](https://github.com/curib123/inventory_management_system/actions/runs/36868181527) fails at validation, before dependency installation.

The job reports that name and description are required for package publication and warns that a license is missing. This is a manifest problem, not an avatar rendering failure. None of issue guides 001–016 covers it.

## Fix with code

Add these properties at the top of `composer.json`, keeping the existing requirements and scripts:

```json
{
    "name": "curib123/inventory-management-system",
    "description": "CodeIgniter inventory management with stock transactions, reports, and role permissions.",
    "type": "project",
    "license": "Apache-2.0"
}
```

This is a properties example, not a replacement for the entire file. `Apache-2.0` matches the repository's existing `LICENSE`; do not change the project's actual licensing as part of this fix.

Refresh the lock metadata without requesting dependency version upgrades:

```bash
composer update --lock --no-install
composer validate --strict
composer install --prefer-dist --no-progress
```

Review the resulting `composer.lock` diff before committing it. Package versions should stay unchanged. Run the commands with the PHP version/extensions used by deployment.

## Verify

1. Strict validation exits with code 0, without missing-metadata warnings.
2. Dependency installation completes.
3. A new push or PR reaches the install step in GitHub Actions.

The existing workflow does not execute the test suite: a green Composer job alone is not evidence that tests pass. Run `composer test` and the JavaScript checks separately.

**Validation performed during analysis:** inspected the actual failed workflow and manifest. The metadata change and lock refresh have not been applied to the repository.
