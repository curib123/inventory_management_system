# Inventory Management System

A web-based inventory management system built with CodeIgniter 3, PHP, MySQL, Bootstrap, JavaScript, AJAX, DataTables, and Chart.js. It is designed to manage stock transactions, inventory records, supplier details, low-stock alerts, reports, and role-based access control.

## Overview

This project is focused on real inventory operations, not just basic CRUD functionality. It includes product management, stock-in and stock-out processing, stock movement history, reporting, and authorization logic.

## Features

### Product and Category Management
- Manage products and categories
- Product code support
- Unit tracking
- Cost and selling price
- Current stock value
- Reorder level
- Product status

### Supplier Management
- Store supplier information
- Track contact details
- Associate products with suppliers

### Stock In
- Create stock-in transactions
- Generate transaction numbers
- Select suppliers
- Add multiple products 
- Record quantities received
- Increase product stock automatically
- Record the stock movement

### Stock Out
- Create stock-out transactions
- Validate available stock before processing
- Reduce stock automatically
- Prevent negative inventory
- Log stock movement

### Stock Movement History
- Track stock-in and stock-out activities
- Store transaction references
- View product and quantity details
- Track who processed the transaction
- Record date and time

### Stock Adjustment
- Compare system stock with physical stock
- Record quantity differences
- Save adjustment reason
- Update inventory balances
- Keep history of adjustments

### Low Stock Monitoring
- Configure reorder levels
- Identify low-stock products
- Raise inventory warnings
- Track corrective changes

### Dashboard
- Total products
- Total stock quantity
- Low stock items
- Today’s stock in
- Today’s stock out
- Inventory value
- Stock by category
- Monthly stock movement summary

### Reports and Export
- Inventory reports
- Stock-in report
- Stock-out report
- Stock movement reports
- Low-stock report
- Inventory valuation
- CSV/Excel export using PHPSpreadsheet
- PDF export using Dompdf

### User Roles and Permissions
- Admin
- Staff
- Role-based access control
- Transaction tracking by user

## Business Rules

1. Stock In: New Stock = Old Stock + Quantity Received
2. Stock Out: New Stock = Old Stock - Quantity Received
3. Prevent stock out when inventory is insufficient
4. Every stock change must create a transaction/history record
5. Only authorized users can adjust or delete critical inventory records
6. Use database transactions when updating stock and transaction records together

## Tech Stack

- CodeIgniter 3
- PHP
- MySQL
- Bootstrap
- JavaScript
- AJAX
- DataTables
- Chart.js

## Suggested Database Tables

```sql
users (
    id,
    username,
    password,
    role,
    status,
    created_at
)

categories (
    id,
    category_name,
    status
)

suppliers (
    id,
    supplier_name,
    contact_person,
    phone,
    address
)

products (
    id,
    category_id,
    supplier_id,
    product_code,
    product_name,
    unit,
    cost_price,
    selling_price,
    stock,
    reorder_level,
    status
)

stock_transactions (
    id,
    transaction_no,
    type,
    supplier_id,
    remarks,
    created_by,
    created_at
)

stock_transaction_items (
    id,
    transaction_id,
    product_id,
    quantity,
    cost_price
)

stock_adjustments (
    id,
    product_id,
    system_stock,
    actual_stock,
    difference,
    reason,
    created_by,
    created_at
)

activity_logs (
    id,
    user_id,
    action,
    description,
    ip_address,
    created_at
)
```

## Recommended Workflow

Product Setup -> Supplier Setup -> Stock In -> Inventory Update -> Stock Out -> Inventory Update -> Stock Movement History -> Stock Adjustment -> Dashboard -> Reports

## Project Structure

```text
inventory_management_system/
├── application/
├── system/
├── assets/
├── uploads/
├── index.php
├── README.md
├── LICENSE
└── .htaccess
```

## Installation

### Prerequisites
- PHP 7.4 or newer
- MySQL or MariaDB
- Apache or Nginx
- Composer

### Setup Steps
1. Clone the repository.
2. Import the database schema into MySQL.
3. Run `composer install` to install PHPSpreadsheet and Dompdf.
4. Configure the database connection in the CodeIgniter settings.
5. Set the application base URL.
6. Ensure writable folders have the proper permissions.
7. Run the project in a browser.

### Run Tests

```bash
vendor/bin/phpunit
```

The unit tests cover authentication, stock-in/out rules, insufficient inventory, report definitions, and export formats. Database-backed model and controller integration tests should run against a dedicated test database.

## Security Best Practices

- Hash all user passwords securely
- Restrict access based on user role
- Validate all input data
- Use prepared statements for database queries
- Record important administrative actions
- Use database transactions for stock and transaction updates

## License

This project is licensed under the MIT License. See the LICENSE file for details.

## Notes

This repository serves as a project blueprint and planning document for the Inventory Management System. It can be extended into a complete CodeIgniter application as development continues.
