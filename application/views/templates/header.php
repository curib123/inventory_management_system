<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Inventory'; ?></title>
</head>
<body>
    <?php
    $alert_message = $this->session->flashdata('error');
    $alert_title = 'Error';
    if (!$alert_message) {
        $alert_message = $this->session->flashdata('success');
        $alert_title = 'Success';
    }
    if ($alert_message):
        $this->load->view('modal/alerts/message', array(
            'alert_title' => $alert_title,
            'alert_message' => $alert_message
        ));
    endif;
    ?>
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
            <?php if ($this->session->userdata('user_id')): ?>
                <button type="button" onclick="document.getElementById('logout-confirmation').showModal();">Logout</button>
            <?php endif; ?>
        </div>
    </div>
    <div class="container">

    <?php if ($this->session->userdata('user_id')): ?>
        <?php $this->load->view('modal/auth/logout'); ?>
    <?php endif; ?>
