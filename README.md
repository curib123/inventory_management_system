# inventory_management_system
Built an Inventory management System using Codelgniter 3 . The focus is not only CRUD but also business logic,stock transaction,inventory calculation,report,user permissions and transaction history.


Development Phases
 
 Phase 1 - Product & Category Management 

1.Products
2.Categories
3.Product code
4.unit
5.Cost Price
6.Selling Price
7.Currect Stock
8.Reorder Level 
9.Product status


Phase 2 - Supplier Management

1.Supplier information
2.Supplier contact details
3.Products associated with suppliers


Phase 3 - Stock in


1.Create Stock in Trasaction
2.Transaction number
3.When Select supplier when supplier selected it will fetch all product connected to that supplier selected
4.It will add multiple products in 1 transaction
5.Quantity received from each product selected and show old stock , and total new stock preview 
6.Increase product stock automatically
7.Record stock movement 

Phase 4. Stock out
1.Create Stock Out Transaction
2.Check available stock before processing 
3.Decrease product stock automatically
4.Prevent stock from becoming negative
5.Record stock movement

Phase 5 - Stock Movement History
1.Stock In History
2.Stock Out History
3.Transaction reference 
4.Product
5.Quantity
6.User who processed the transaction 
7.Date and Time

Phase 6 - Stock Adjustment

1.Compare system stock in physical stock
2.Record quantity difference
3.Record adjustment reason
4.Update inventory
5.Keep adjustment history


Phase 7 - Low Stock Monitoring 

1.Set reorder level
2.Indentify low stock products
3.Records adjustment reason 
4.Update inventory
5.keep adjusment history

Phase 8- Dashboard  - with chart.js visuals

1.Total Products
2.Total stock quantity
3.Low stocks products
4.Todays stock in
5.Todays stock out
6.Inventory value
7.Stock by Category
8.Monthly stock movement

Phase 9 - Reports & Export

1.Inventory Reports
2.Stock in Report
3.Stock Out Report
4.Stock Movement report
5.Low-Stock report
6.Inventory valuation
7.Export CSV/Excel - PHPSpreadsheet Library
8.Print Pdf report - Dompdf Library

Phase 10 -User Roles & Permissions
1. Admin
2. Staff
3. Controll Access to inventory functions
4. Track which user processed transactions


Suggestion Database Tables


1. users - id,username,password,role,status,created_at

2. categories - id,category_name,status

3. suppliers - id ,supplier_name,contact_person,phone,address

4. products - id ,category_id,supplier_id ,product_code ,product_name,unit,cost_price,selling_price,stock,reorder_level,status

5. stock_transaction - id,transaction_no,type,supplier_id,remarks,created_by,created_at

6. stock_transaction_items - id,transaction_id,product_id,quantity,cost_price

7. stock_adjustments -id , product_id,system_stock,actual_stock,difference,reason,created_by,created_at

8. activity_logs - id,user_id,action,description,ip_address,created_at


Very Important take this on my mind

1. Stock In  : New Stock = Old Stock + Quantity Received
2. Stock Out : New Stock = Old Stock - Quantity Received
3. Do not allow stock out when available stock is insufficient
4. Every Stock change should create a transaction / history record
5. Only authorized users should be allowed  to adjust or delete important inventory records
6. Use database transaction when updating stocks and transaction records together

Tech Stack to use

1. Codelgniter 3
2. PHP
3. MYsql
4. Bootstrap
5. Javascript 
6. Ajax 
7. DataTables
8. Chart.js


issuess:

styling of all pages and modal use bootstrap on it

fix issues in stock in must follow the rule this rule phase 3

1.Create Stock in Trasaction
2.Transaction number
3.When Select supplier when supplier selected it will fetch all product connected to that supplier selected
4.It will add multiple products in 1 transaction
5.Quantity received from each product selected and show old stock , and total new stock preview 
6.Increase product stock automatically
7.Record stock movement 

all pages have own branches to style make sure when merge to development no conflict

proper layout in excell format and more nice table design in pdf format

## Automated Unit Testing

This project uses PHPUnit for automated regression testing of important business rules.

Run the professional readable test report:

```bash
composer test
```

Or run PHPUnit directly:

```bash
vendor/bin/phpunit --testdox
```

On Windows PowerShell or Command Prompt:

```powershell
vendor\bin\phpunit --testdox
```

The TestDox report prints each business rule as a readable passed check, for example:

```text
Auth Service
 ✔ Authenticate returns session data for valid credentials
 ✔ Session data does not contain legacy password change flag
 ✔ Authenticate returns false for invalid credentials
 ✔ Session data contains role information

Stock Rules
 ✔ Stock in adds quantity to current stock
 ✔ Stock out subtracts quantity from current stock
 ✔ Stock out rejects insufficient inventory
 ✔ Fractional quantity is rejected instead of truncated
 ✔ Stock in rejects database integer overflow

OK (tests and assertions passed)
```

Current unit-test areas include authentication/session behavior, DataTables request/pagination rules, report definitions/export formats, and stock calculation/validation rules. PHPUnit is configured to display TestDox output by default and to fail the run on warnings or risky tests.







# Resolved QA Issues

- [x] Stock Out now uses the same multi-product quantity-list workflow as Stock In, but in reverse. It shows current stock, live new-stock preview, enforces available-stock limits in the UI, and the server still blocks negative stock.
- [x] Centralized DataTable layout now places a full-width search control on the left and the page-length selector on the right, with responsive stacking on smaller screens.
- [x] DataTable columns are centrally aligned through column metadata. Internal Product and Category IDs remain available to the server/actions but are hidden from users.
- [x] Added a centralized searchable-select enhancement for large relationship lists. It is currently used for Stock In suppliers, Product category/supplier selection, and Stock Adjustment supplier/product selection.
- [x] Stock Adjustment now uses a required supplier-first reconciliation flow. Selecting a supplier limits Product search to that supplier's active products; an explicit Unassigned Products scope handles products without a supplier. The modal shows system stock, physical count, live variance, requires a reason, blocks no-op adjustments, and records permanent history.
- [x] CSV and Excel remain downloadable report files. Excel output is hardened against buffered-output corruption and uses the same professional blue-header, bordered, zebra-row table language as PDF.
- [x] PDF is now a print-focused export: it opens inline in a new tab as an A4 landscape report instead of forcing a file download.
- [x] Stock In continues to follow Phase 3: supplier selection fetches only that supplier's active products, supports multiple products in one transaction, shows old/new stock, updates inventory atomically, creates a transaction number, and records movement history.
- [x] Bootstrap/custom styling remains centralized through shared page, table, modal, form, stock, sidebar, and report components to reduce page-specific merge conflicts.

### UI styling branches

The following UI branches are prepared from the current `development` baseline:

- `ui/shared` — shared components/CSS/JS only
- `ui/dashboard`
- `ui/products`
- `ui/categories`
- `ui/suppliers`
- `ui/stock`
- `ui/reports`
- `ui/users`
- `ui/roles`

To reduce merge conflicts, page branches should modify only that page's view-specific files. Changes to shared components such as `components/data_table.php`, `app.css`, `table.css`, `modal.css`, or `app.js` should go through `ui/shared` first, then be merged into `development` before page branches are refreshed.

## QA note

The current `development` branch has been rechecked from a fresh clone with:
- JavaScript syntax parsing for `assets/js/app.js`.
- PHP syntax linting across every PHP file under `application/`.
- PHPUnit TestDox regression testing on PHP 8.2.
- Code-path review of Stock In, Stock Out, Stock Adjustment, DataTables, permissions, searchable relationship selectors, and report exports.

Fresh-install smoke testing also confirmed that the schema and seed SQL import successfully into MariaDB and that the real login page renders through PHP 8.2 with the configured database. The seed SQL is executable directly and the documented admin/staff development password hashes were corrected.

A final browser transaction smoke test is still recommended on the deployment machine before production release because it verifies environment-specific sessions, Stock In/Out/Adjustment submissions, generated files, and browser interactions.



## User account temporary-password onboarding

User Management now uses a secure first-login password flow:

- New user accounts no longer require an administrator to type an initial password.
- A cryptographically random temporary password is generated automatically.
- Only the password hash is stored in the database.
- The temporary password is shown once after account creation so the administrator can copy and share it securely.
- Newly created users are marked with `must_change_password = 1`.
- After login, the Change Password modal opens automatically and asks for Current Password, New Password, and Confirm New Password.
- Users can choose **Ask later**. This postpones the reminder only for the current login session; the database requirement remains active and returns on the next login.
- A successful password change clears `must_change_password`.
- The top navigation includes a key button so users can open Change Password manually at any time.
- If an administrator manually resets a password while editing a user, the account is marked to request another password change after the next login.

For an existing database created before this feature, run:

```sql
SOURCE database/migrations/20260926_add_must_change_password.sql;
```

Fresh installations already include the column in `database/inventory_management_db.sql`.

## Latest QA checklist

- [x] Add relevant filtration to each DataTable. Filters remain contextual rather than adding unnecessary controls to every page.
- [x] Stock Adjustment uses the recommended supplier-first reconciliation flow: Supplier -> Product -> System Stock -> Actual Physical Stock -> Variance -> Reason -> Review -> Confirm.
- [x] CSV export uses a structured business-report layout and is available from the Reports UI.
- [x] Excel export uses a professional manager-ready workbook layout with metadata, KPI summary, filters, frozen headers, print setup, number formats, and semantic movement styling.
- [x] PDF export uses a professional A4 landscape report layout with metadata, summary cards, styled table, movement badges, and page numbering.
- [x] Large relationship dropdowns are searchable. Stock supplier/product, Product category/supplier, and User role lookups use server-side search for scalability; small fixed table filters remain normal dropdowns because search would add unnecessary interaction.
- [x] Report CSV, Excel, and Print PDF exports inherit the current report search and active table filters, so downloaded/printed data matches the user's filtered report scope.
- [x] Stock In, Stock Out, and Movement reports include a server-side Period filter for Today, Last 7 days, Last 30 days, or all dates.
- [x] New user accounts receive a secure generated temporary password and are prompted to change it after login, with a session-only Ask later option.
