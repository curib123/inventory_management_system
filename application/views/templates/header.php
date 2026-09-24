<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    >

    <link
        rel="stylesheet"
        href="https://cdn.datatables.net/v/bs5/dt-3.1.1/datatables.min.css"
    >

    <link rel="stylesheet" href="<?php echo base_url('assets/css/app.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/table.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/sidebar.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/modal.css'); ?>">

    <title><?php echo html_escape(isset($page_title) ? $page_title : 'Inventory Management System'); ?></title>
</head>

<body class="bg-body-tertiary">

<?php if ($this->session->userdata('user_id')): ?>

<div class="d-flex min-vh-100">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-box-seam"></i>
            <span>Inventory System</span>
        </div>

        <div class="sidebar-heading">Main Menu</div>

        <nav class="nav flex-column" aria-label="Main navigation">
            <?php if ($this->User_model->has_permission($this->session->userdata('user_id'), 'view_dashboard')): ?>
                <a class="nav-link" href="<?php echo site_url('dashboard'); ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            <?php endif; ?>

            <?php if ($this->User_model->has_permission($this->session->userdata('user_id'), 'manage_products')): ?>
                <a class="nav-link" href="<?php echo site_url('products'); ?>">
                    <i class="bi bi-box"></i>
                    <span>Products</span>
                </a>

                <a class="nav-link" href="<?php echo site_url('categories'); ?>">
                    <i class="bi bi-tags"></i>
                    <span>Categories</span>
                </a>
            <?php endif; ?>

            <?php if ($this->User_model->has_permission($this->session->userdata('user_id'), 'manage_suppliers')): ?>
                <a class="nav-link" href="<?php echo site_url('suppliers'); ?>">
                    <i class="bi bi-truck"></i>
                    <span>Suppliers</span>
                </a>
            <?php endif; ?>

            <?php if (
                $this->User_model->has_permission($this->session->userdata('user_id'), 'view_reports') ||
                $this->User_model->has_permission($this->session->userdata('user_id'), 'manage_stock_in') ||
                $this->User_model->has_permission($this->session->userdata('user_id'), 'manage_stock_out')
            ): ?>
                <a class="nav-link" href="<?php echo site_url('stock'); ?>">
                    <i class="bi bi-arrow-left-right"></i>
                    <span>Stock History</span>
                </a>
            <?php endif; ?>

            <?php if ($this->User_model->has_permission($this->session->userdata('user_id'), 'view_dashboard')): ?>
                <a class="nav-link" href="<?php echo site_url('stock/low-stock'); ?>">
                    <i class="bi bi-exclamation-triangle"></i>
                    <span>Low Stock</span>
                </a>
            <?php endif; ?>

            <?php if ($this->User_model->has_permission($this->session->userdata('user_id'), 'view_reports')): ?>
                <div class="sidebar-heading">Reports</div>

                <a class="nav-link" href="<?php echo site_url('reports'); ?>">
                    <i class="bi bi-bar-chart"></i>
                    <span>Reports</span>
                </a>
            <?php endif; ?>

            <?php if ($this->User_model->has_permission($this->session->userdata('user_id'), 'manage_users')): ?>
                <div class="sidebar-heading">Administration</div>

                <a class="nav-link" href="<?php echo site_url('users'); ?>">
                    <i class="bi bi-people"></i>
                    <span>Users</span>
                </a>

                <a class="nav-link" href="<?php echo site_url('roles'); ?>">
                    <i class="bi bi-shield-lock"></i>
                    <span>Roles</span>
                </a>
            <?php endif; ?>
        </nav>

        <div class="logout-section">
            <?php echo form_open('logout'); ?>
                <button type="submit" class="btn btn-outline-light w-100">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    Logout
                </button>
            <?php echo form_close(); ?>
        </div>
    </aside>

    <div class="sidebar-backdrop" id="sidebar-backdrop" aria-hidden="true"></div>

    <div class="main-content flex-grow-1">
        <header class="topbar d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2 min-w-0">
                <button
                    type="button"
                    class="btn btn-outline-secondary mobile-toggle"
                    id="sidebar-toggle"
                    aria-label="Open navigation"
                    aria-controls="sidebar"
                    aria-expanded="false"
                >
                    <i class="bi bi-list"></i>
                </button>

                <h1 class="h5 mb-0 fw-semibold text-truncate">
                    <?php echo html_escape(isset($page_title) ? $page_title : 'Inventory Management System'); ?>
                </h1>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="badge text-bg-primary">
                    <?php echo html_escape($this->session->userdata('role_name')); ?>
                </span>
                <span class="fw-semibold d-none d-sm-inline">
                    <i class="bi bi-person-circle me-1"></i>
                    <?php echo html_escape($this->session->userdata('username')); ?>
                </span>
            </div>
        </header>

        <main class="container-fluid py-4">
            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo html_escape($this->session->flashdata('error')); ?>
                </div>
            <?php endif; ?>

            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo html_escape($this->session->flashdata('success')); ?>
                </div>
            <?php endif; ?>

<?php else: ?>

<main class="container py-4">

<?php endif; ?>
