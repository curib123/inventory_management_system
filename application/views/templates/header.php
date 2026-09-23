<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Inventory'; ?></title>
    <link rel="stylesheet" href="<?php echo base_url('assets/css/style.css'); ?>">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            color: #1f2937;
        }

        .topbar {
            background: #111827;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav a {
            color: white;
            margin-left: 15px;
            text-decoration: none;
        }

        .container {
            max-width: 1100px;
            margin: 30px auto;
            background: white;
            border-radius: 10px;
            padding: 24px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #e5e7eb;
            padding: 12px;
            text-align: left;
        }

        th {
            background: #f3f4f6;
        }

        .btn {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 6px;
            text-decoration: none;
            color: white;
            background: #2563eb;
            margin-right: 8px;
        }

        .btn-danger {
            background: #dc2626;
        }

        .btn-success {
            background: #16a34a;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 22px;
        }

        .card h3 {
            margin: 0 0 10px;
            color: #374151;
        }

        .card .value {
            font-size: 28px;
            font-weight: bold;
            color: #111827;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 10px 12px;
            box-sizing: border-box;
            border: 1px solid #d1d5db;
            border-radius: 6px;
        }

        .form-actions {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="topbar">
        <div><strong>Inventory Management System</strong></div>
        <div class="nav">
            <a href="<?php echo site_url('dashboard'); ?>">Dashboard</a>
            <a href="<?php echo site_url('products'); ?>">Products</a>
            <a href="<?php echo site_url('categories'); ?>">Categories</a>
            <a href="<?php echo site_url('suppliers'); ?>">Suppliers</a>
            <?php if ($this->session->userdata('user_id')): ?>
                <a href="<?php echo site_url('stock'); ?>">Stock History</a>
                <a href="<?php echo site_url('stock/low-stock'); ?>">Low Stock</a>
                <a href="<?php echo site_url('reports'); ?>">Reports</a>
            <?php endif; ?>
            <?php if ($this->session->userdata('user_id') && $this->User_model->has_permission($this->session->userdata('user_id'), 'manage_users')): ?>
                <a href="<?php echo site_url('roles'); ?>">Roles</a>
            <?php endif; ?>
            <a href="<?php echo site_url('logout'); ?>">Logout</a>
        </div>
    </div>
    <div class="container">
