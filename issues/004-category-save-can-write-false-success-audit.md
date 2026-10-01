# Issue 004 — Failed category saves can still create a success audit record

**Severity:** High  
**Status:** Open  
**Area:** Categories / audit integrity

## What is wrong

In `Category_service::save()`, the model save result is stored in `$saved`, but the activity log is inserted before the service checks whether `$saved` is true.

File:

- `application/libraries/Category_service.php`

Current order:

1. call `Category_model->save()`;
2. create `category_created` / `category_updated` activity log;
3. return success or failure based on `$saved`.

## Impact

If the category write fails because of a database problem, constraint problem, connection issue, or other persistence failure, the audit table can still claim the category was created/updated.

That makes the audit trail unreliable.

## Root cause

Audit creation is unconditional instead of being tied to the successful persistence result.

## Fix guide

Minimum safe fix:

1. Call `Category_model->save()`.
2. If it returns false, immediately return the failure result.
3. Only then insert the success activity log.
4. Return success afterward.

Better fix for audit-critical behavior:

1. Start a DB transaction.
2. Save the category.
3. Insert the activity record.
4. If either fails, roll back.
5. Commit only if both succeed.

The transactional approach is preferred if Activity Logs are considered an audit requirement rather than optional telemetry.

## Tests to add

Mock or force `Category_model->save()` to return false and assert:

- service returns `success = FALSE`;
- `insert_activity_log()` is never called.

Also test a successful create and update and assert exactly one activity row is written.

## Definition of done

- No category success audit exists when the category write fails.
- Successful create/update still logs exactly once.
- Error response remains correct for the modal.
