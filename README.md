# Project Objective

Build an Inventory Management System using CodeIgniter 3. The Focus is not only CRUD,but also business logic,stock transactions,inventory calculations,reports,user permissions aand transaction history.

# Completed Phases

# Phase 1 - Product & Category Management
- Products
- Categories
- Product code 
- Unit
- Cost Price
- Selling Price 
- Current  Stock
- ReOrder Level
- Product Status


# Phase 2 - Supplier Management

- Supplier information 
- Supplier contact details
- Products associated with suppliers


# Phase 3 - Stock in

- create stock in Transaction
- transaction number 
- select supplier
- add multiple products
- quantity recieved
- increase  product stock automatically
- record stock movement

# Phase 4 - Stock out

- create stock out transaction
- check available stock before processing
- Decrease product stock automatically
- prevent stock from becoming negative
- record stock movement

# Phase 5 - Stock Movement History 

- Stock IN History
- stock  out history
- transaction reference
- product
- quantity
- user who processed the transaction
- date and time

# Phase 6 - Stock  Adjustment 

- Compare system stock with physical stock
- recorrd quantity
- record adjustment reason
- update inventory
- keep adjustment history

# Phase 7 - Low  Stock Monitoring 
- Set reordr level
- identify low stock products
- dashboard low-stock count
- low-stock report

# Phase 8 - Dashboard 
- total products
- total stock quantity
- low stock product
- todays stock in
- todays stock out
- inventory value
- stock  by category
- monthly stock movement

# Phase 9 - Reports & Export

- Inventory report
- Stock In Report
- Stock Out Report
- Stock Movement report
- Low stock report
- inventory valuation
- export csv/excel
- print/pdf reports

# Phase 10 - User roles & Permissions

- Admin
- Staff
- Control access to inventory functions
- track which user processed transaction



# Implemented Rules

- Stock in : New Stock = Old Stock + Quantity Received
- Stock out : New Stock = Old Stock - Quantity Received
- Do not allow stock out when  available stock is inssufficient
- Every stock change should create a transaction / history record.
- Stock adjustment must record a reason
- Only authorized  users should be allowed to adjust or delete impoertant inventory records
- Use database transactions when updating stock and transaction record together 


# Tech Stack Use

- CodeIgniter 3
- PHP
- MySQL/MariaDB
- Bootstrap
- Javascript 
- AJAX
- DataTables
- Chart.js


# Use Final workflow 

- Product Setup - Supplier Setup - Stock in - Inventory Update - Stock out - Inventory Update - Stock movement  history - stock adjustment -dashboard - reports


# issues to fix 

- in product table highlights the low stock stock to warning
- make sure sidebar links have active focus design