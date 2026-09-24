<?php
$ui_styling_enabled = $this->config->item('ui_styling_enabled') !== FALSE;
$ui_controller = strtolower((string) $this->router->fetch_class());
$ui_method = strtolower((string) $this->router->fetch_method());
$ui_page_styled = $ui_styling_enabled && ui_style_enabled_for(
    (array) $this->config->item('ui_page_styles'),
    $ui_controller,
    $ui_method,
    TRUE
);
$ui_modal_styled = $ui_styling_enabled && ui_style_enabled_for(
    (array) $this->config->item('ui_modal_styles'),
    $ui_controller,
    $ui_method,
    TRUE
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php if ($ui_styling_enabled): ?>
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
    <?php if (!$ui_page_styled || !$ui_modal_styled): ?>
    <link rel="stylesheet" href="<?php echo base_url('assets/css/plain-mode.css'); ?>">
    <?php endif; ?>
    <?php endif; ?>

    <title><?php echo html_escape(isset($page_title) ? $page_title : 'Inventory Management System'); ?></title>
</head>

<body
    class="<?php echo $ui_page_styled ? 'bg-body-tertiary' : 'app-page-plain'; ?><?php echo $ui_modal_styled ? '' : ' app-modal-plain'; ?>"
    data-ui-page-style="<?php echo $ui_page_styled ? 'styled' : 'plain'; ?>"
    data-ui-modal-style="<?php echo $ui_modal_styled ? 'styled' : 'plain'; ?>"
>

<?php if ($this->session->userdata('user_id')): ?>

<div class="d-flex min-vh-100">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-box-seam"></i>
            <span>Inventory System</span>
        </div>

        <div class="sidebar-heading">Main Menu</div>

        <nav class="nav flex-column" aria-label="Main navigation">
            <?php $current_user_id = (int) $this->session->userdata('user_id'); ?>

            <?php if ($this->User_model->has_permission($current_user_id, 'dashboard.view')): ?>
                <a class="nav-link" href="<?php echo site_url('dashboard'); ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            <?php endif; ?>

            <?php if ($this->User_model->has_permission($current_user_id, 'products.view')): ?>
                <a class="nav-link" href="<?php echo site_url('products'); ?>">
                    <i class="bi bi-box"></i>
                    <span>Products</span>
                </a>
            <?php endif; ?>

            <?php if ($this->User_model->has_permission($current_user_id, 'categories.view')): ?>
                <a class="nav-link" href="<?php echo site_url('categories'); ?>">
                    <i class="bi bi-tags"></i>
                    <span>Categories</span>
                </a>
            <?php endif; ?>

            <?php if ($this->User_model->has_permission($current_user_id, 'suppliers.view')): ?>
                <a class="nav-link" href="<?php echo site_url('suppliers'); ?>">
                    <i class="bi bi-truck"></i>
                    <span>Suppliers</span>
                </a>
            <?php endif; ?>

            <?php if ($this->User_model->has_permission($current_user_id, 'stock.history')): ?>
                <a class="nav-link" href="<?php echo site_url('stock'); ?>">
                    <i class="bi bi-arrow-left-right"></i>
                    <span>Stock History</span>
                </a>
            <?php endif; ?>

            <?php if ($this->User_model->has_permission($current_user_id, 'stock.view')): ?>
                <a class="nav-link" href="<?php echo site_url('stock/low-stock'); ?>">
                    <i class="bi bi-exclamation-triangle"></i>
                    <span>Low Stock</span>
                </a>
            <?php endif; ?>

            <?php if ($this->User_model->has_permission($current_user_id, 'reports.view')): ?>
                <div class="sidebar-heading">Reports</div>

                <a class="nav-link" href="<?php echo site_url('reports'); ?>">
                    <i class="bi bi-bar-chart"></i>
                    <span>Reports</span>
                </a>
            <?php endif; ?>

            <?php if ($this->User_model->has_any_permission($current_user_id, array('users.view', 'roles.view'))): ?>
                <div class="sidebar-heading">Administration</div>

                <?php if ($this->User_model->has_permission($current_user_id, 'users.view')): ?>
                    <a class="nav-link" href="<?php echo site_url('users'); ?>">
                        <i class="bi bi-people"></i>
                        <span>Users</span>
                    </a>
                <?php endif; ?>

                <?php if ($this->User_model->has_permission($current_user_id, 'roles.view')): ?>
                    <a class="nav-link" href="<?php echo site_url('roles'); ?>">
                        <i class="bi bi-shield-lock"></i>
                        <span>Roles</span>
                    </a>
                <?php endif; ?>
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
            <?php
            $flash_error = $this->session->flashdata('error');
            $flash_warning = $this->session->flashdata('warning');
            $flash_success = $this->session->flashdata('success');
            ?>

            <?php if ($flash_error): ?>
                <div class="alert alert-danger alert-dismissible fade show app-feedback-alert" role="alert">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-exclamation-circle-fill mt-1"></i>
                        <div>
                            <div class="fw-semibold">Action could not be completed</div>
                            <div><?php echo html_escape($flash_error); ?></div>
                            <div class="small mt-1 opacity-75">Review the message, refresh the data if needed, and try again.</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Dismiss"></button>
                </div>
            <?php endif; ?>

            <?php if ($flash_warning): ?>
                <div class="alert alert-warning alert-dismissible fade show app-feedback-alert" role="alert">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                        <div>
                            <div class="fw-semibold">Please review</div>
                            <div><?php echo html_escape($flash_warning); ?></div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Dismiss"></button>
                </div>
            <?php endif; ?>

            <?php if ($flash_success): ?>
                <div class="alert alert-success alert-dismissible fade show app-feedback-alert" role="status">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-check-circle-fill mt-1"></i>
                        <div>
                            <div class="fw-semibold">Completed successfully</div>
                            <div><?php echo html_escape($flash_success); ?></div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Dismiss"></button>
                </div>
            <?php endif; ?>

<?php else: ?>

<main class="container py-4">

<?php endif; ?>
