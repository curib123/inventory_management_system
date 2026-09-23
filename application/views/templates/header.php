<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title><?php echo html_escape(isset($page_title) ? $page_title : 'Inventory Management System'); ?></title>
</head>
<body>
    <header>
        <h1><?php echo html_escape(isset($page_title) ? $page_title : 'Inventory Management System'); ?></h1>
        <?php if ($this->session->userdata('user_id')): ?>
            <p>
                Signed in as <?php echo html_escape($this->session->userdata('username')); ?>
                (<?php echo html_escape($this->session->userdata('role_name')); ?>)
            </p>
        <?php endif; ?>
    </header>

    <?php if ($this->session->userdata('user_id')): ?>
        <nav aria-label="Main navigation">
            <a href="<?php echo site_url('dashboard'); ?>">Dashboard</a> |
            <a href="<?php echo site_url('products'); ?>">Products</a> |
            <a href="<?php echo site_url('categories'); ?>">Categories</a> |
            <a href="<?php echo site_url('suppliers'); ?>">Suppliers</a> |
            <a href="<?php echo site_url('stock'); ?>">Stock History</a> |
            <a href="<?php echo site_url('stock/low-stock'); ?>">Low Stock</a> |
            <a href="<?php echo site_url('reports'); ?>">Reports</a>
            <?php if ($this->User_model->has_permission($this->session->userdata('user_id'), 'manage_users')): ?>
                | <a href="<?php echo site_url('users'); ?>">Users</a>
                | <a href="<?php echo site_url('roles'); ?>">Roles</a>
            <?php endif; ?>
        </nav>
        <?php echo form_open('logout'); ?>
            <button type="submit">Logout</button>
        <?php echo form_close(); ?>
        <hr>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <p><?php echo html_escape($this->session->flashdata('error')); ?></p>
    <?php endif; ?>
    <?php if ($this->session->flashdata('success')): ?>
        <p><?php echo html_escape($this->session->flashdata('success')); ?></p>
    <?php endif; ?>

    <main>
