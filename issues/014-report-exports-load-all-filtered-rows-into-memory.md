# Issue 014 — Report exports load every filtered row into PHP memory

**Severity:** Medium-High  
**Status:** Open  
**Area:** Reports / performance / scalability

## What is wrong

`Report_model::get_export_rows()` runs the complete filtered query with no limit and returns `result_array()`.

`Report_service::export_payload()` then keeps the complete row array in memory and passes it to CSV/XLSX/PDF rendering.

Files involved:

- `application/models/Report_model.php`
- `application/libraries/Report_service.php`
- `application/controllers/Reports.php`

This is different from the DataTable path, which correctly paginates.

## Impact

As transaction history grows, exporting a broad date range can:

- hit PHP memory_limit;
- time out;
- make the process unresponsive;
- make XLSX/PDF generation substantially worse because those libraries also build in-memory structures.

One large export request can consume disproportionate server resources.

## Fix guide

Use format-appropriate export strategies.

### CSV

Prefer streaming/chunking:

1. query rows in stable chunks using primary-key/keyset pagination;
2. write each chunk directly to `php://output`;
3. do not build one giant PHP array.

### XLSX

Use a writer configured for lower-memory operation or a temporary-file/chunk workflow. Set a documented maximum export size if necessary.

### PDF

PDF is not suitable for arbitrarily large datasets. Apply a safe row cap and tell the user to use CSV/XLSX for full datasets.

General:

- calculate summary values incrementally;
- add maximum export limits;
- log/report a clear error instead of exhausting memory.

## Tests to add

Generate a large test dataset and verify:

- CSV memory does not grow linearly with total row count;
- PDF rejects/limits oversized exports cleanly;
- filters remain identical to the on-screen report.

## Definition of done

- Large exports have bounded memory behavior.
- Oversized PDF/XLSX requests fail gracefully or use a documented limit.
- Normal small exports remain unchanged.
