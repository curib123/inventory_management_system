# Issue 006 — Report export logs success before the file is actually generated

**Severity:** Medium-High  
**Status:** Open  
**Area:** Reports / audit accuracy

## What is wrong

`Reports::export()` writes an `export_created` or `print_created` activity record immediately after preparing the data payload, before calling:

- `export_csv()`
- `export_xlsx()`
- `export_pdf()`

File:

- `application/controllers/Reports.php`

Those export methods can still fail afterward, for example because PhpSpreadsheet/Dompdf is unavailable or output generation throws.

## User/audit impact

Activity Logs can say an export or PDF print was created even though the user received an export error and no valid file.

## Root cause

The audit event represents completion but is emitted at the start of output generation.

## Fix guide

Refactor export completion logging:

1. Move audit creation into a helper such as `log_successful_export()`.
2. Call it only after the export format has successfully produced the output.
3. For XLSX/PDF, log after the library has generated the file bytes successfully.
4. For CSV, because the method currently writes directly to `php://output` and exits, restructure it so generation can return success before the final response is sent.
5. Optionally add a separate `export_failed` audit action in the catch block with report key/format, but do not label failures as created.

A cleaner architecture is to have exporters generate a string/temp file/result first, then the controller logs success and sends it.

## Tests to add

Simulate exporter failure and assert:

- failure handler runs;
- no `export_created` / `print_created` success log is inserted.

Simulate a successful export and assert exactly one success log.

## Definition of done

- Failed exports never appear as successful in Activity Logs.
- Successful CSV/XLSX/PDF exports still log once.
- The browser download behavior remains unchanged.
