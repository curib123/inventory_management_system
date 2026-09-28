
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"  rel="stylesheet"  integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"  crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="https://cdn.datatables.net/v/bs5/dt-3.1.1/datatables.min.css">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/app.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/table.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/sidebar.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/modal.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/plain-mode.css'); ?>">


    <title><?php echo html_escape(isset($page_title) ? $page_title : 'Inventory Management System'); ?></title>
</head>

<body
    class="bg-body-tertiary"
    data-ui-page-style="styled"
    data-ui-modal-style="styled"
>

<?php if ($this->session->userdata('user_id')): ?>
<?php
$current_user_id = (int) $this->session->userdata('user_id');
$current_controller = strtolower((string) $this->router->fetch_class());
$current_method = strtolower((string) $this->router->fetch_method());

$sidebar_active = array(
    'dashboard' => $current_controller === 'dashboard',
    'products' => $current_controller === 'products',
    'suppliers' => $current_controller === 'suppliers',
    'stock' => $current_controller === 'stock' && $current_method !== 'low_stock',
    'low_stock' => $current_controller === 'stock' && $current_method === 'low_stock',
    'categories' => $current_controller === 'categories',
    'reports' => $current_controller === 'reports',
    'users' => $current_controller === 'users',
    'roles' => $current_controller === 'roles'
);

$sidebar_link_class = function ($key) use ($sidebar_active) {
    return 'nav-link' . (!empty($sidebar_active[$key]) ? ' active' : '');
};

$sidebar_aria_current = function ($key) use ($sidebar_active) {
    return !empty($sidebar_active[$key]) ? ' aria-current="page"' : '';
};
?>

<div class="d-flex min-vh-100">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-box-seam"></i>
            <span>Inventory System</span>
        </div>

        <div class="sidebar-heading">Main Menu</div>

        <nav class="nav flex-column" aria-label="Main navigation">
            <?php if ($this->authorization_service->has_permission($current_user_id, 'dashboard.view')): ?>
                <a class="<?php echo $sidebar_link_class('dashboard'); ?>" href="<?php echo site_url('dashboard'); ?>"<?php echo $sidebar_aria_current('dashboard'); ?>>
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            <?php endif; ?>

            <?php if ($this->authorization_service->has_permission($current_user_id, 'products.view')): ?>
                <a class="<?php echo $sidebar_link_class('products'); ?>" href="<?php echo site_url('products'); ?>"<?php echo $sidebar_aria_current('products'); ?>>
                    <i class="bi bi-box"></i>
                    <span>Products Management</span>
                </a>
            <?php endif; ?>


            <?php if ($this->authorization_service->has_permission($current_user_id, 'suppliers.view')): ?>
                <a class="<?php echo $sidebar_link_class('suppliers'); ?>" href="<?php echo site_url('suppliers'); ?>"<?php echo $sidebar_aria_current('suppliers'); ?>>
                    <i class="bi bi-truck"></i>
                    <span>Suppliers Management</span>
                </a>
            <?php endif; ?>

            <?php if ($this->authorization_service->has_permission($current_user_id, 'stock.history')): ?>
                <a class="<?php echo $sidebar_link_class('stock'); ?>" href="<?php echo site_url('stock'); ?>"<?php echo $sidebar_aria_current('stock'); ?>>
                    <i class="bi bi-arrow-left-right"></i>
                    <span>Stock Management</span>
                </a>
            <?php endif; ?>

            <?php if ($this->authorization_service->has_permission($current_user_id, 'stock.view')): ?>
                <a class="<?php echo $sidebar_link_class('low_stock'); ?>" href="<?php echo site_url('stock/low-stock'); ?>"<?php echo $sidebar_aria_current('low_stock'); ?>>
                    <i class="bi bi-exclamation-triangle"></i>
                    <span>Low Stock Monitoring</span>
                </a>
            <?php endif; ?>

            <?php if ($this->authorization_service->has_permission($current_user_id, 'categories.view')): ?>
                <a class="<?php echo $sidebar_link_class('categories'); ?>" href="<?php echo site_url('categories'); ?>"<?php echo $sidebar_aria_current('categories'); ?>>
                    <i class="bi bi-tags"></i>
                    <span>Categories Setup</span>
                </a>
            <?php endif; ?>

            <?php if ($this->authorization_service->has_permission($current_user_id, 'reports.view')): ?>
                <div class="sidebar-heading">Reports</div>

                <a class="<?php echo $sidebar_link_class('reports'); ?>" href="<?php echo site_url('reports'); ?>"<?php echo $sidebar_aria_current('reports'); ?>>
                    <i class="bi bi-bar-chart"></i>
                    <span>Reports And Exports</span>
                </a>
            <?php endif; ?>

            <?php if ($this->authorization_service->has_any_permission($current_user_id, array('users.view', 'roles.view'))): ?>
                <div class="sidebar-heading">Administration </div>

                <?php if ($this->authorization_service->has_permission($current_user_id, 'users.view')): ?>
                    <a class="<?php echo $sidebar_link_class('users'); ?>" href="<?php echo site_url('users'); ?>"<?php echo $sidebar_aria_current('users'); ?>>
                        <i class="bi bi-people"></i>
                        <span>Users Management</span>
                    </a>
                <?php endif; ?>

                <?php if ($this->authorization_service->has_permission($current_user_id, 'roles.view')): ?>
                    <a class="<?php echo $sidebar_link_class('roles'); ?>" href="<?php echo site_url('roles'); ?>"<?php echo $sidebar_aria_current('roles'); ?>>
                        <i class="bi bi-shield-lock"></i>
                        <span>Roles Management</span>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>

        <div class="logout-section">
            <button
                type="button"
                class="btn app-logout-button w-100"
                data-modal-url="<?php echo site_url('logout/confirm'); ?>"
            >
                <i class="bi bi-box-arrow-right me-2"></i>
                Logout
            </button>
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
                <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary"
                    data-modal-url="<?php echo site_url('account/change-password'); ?>"
                    title="Change password"
                    aria-label="Change password"
                >
                    <i class="bi bi-key" aria-hidden="true"></i>
                </button>

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
            $flash_temporary_password = $this->session->flashdata('temporary_password');
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
                <template data-flash-success-template>
                    <div class="app-modal-header">
                        <div class="app-modal-heading">
                            <span class="app-modal-icon app-modal-icon-success">
                                <i class="bi bi-check2-circle" aria-hidden="true"></i>
                            </span>
                            <div class="app-modal-heading-copy">
                                <div class="app-modal-eyebrow">Success</div>
                                <h2 class="app-modal-title" id="action-modal-title">Completed successfully</h2>
                                <p class="app-modal-subtitle">The requested change was saved successfully.</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close app-modal-close" data-modal-close aria-label="Close"></button>
                    </div>

                    <div class="app-modal-body">
                        <div class="app-confirmation-review">
                            <div class="app-confirmation-review-icon app-confirmation-review-icon-success">
                                <i class="bi bi-check-lg" aria-hidden="true"></i>
                            </div>
                            <div>
                                <div class="app-confirmation-review-title">Success</div>
                                <p class="app-confirmation-review-message mb-0">
                                    <?php echo html_escape($flash_success); ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="app-modal-footer">
                        <div class="app-modal-footer-note">
                            <i class="bi bi-check-circle" aria-hidden="true"></i>
                            <span>Your latest changes are now reflected in the system.</span>
                        </div>
                        <div class="app-modal-footer-actions">
                            <button type="button" class="btn btn-success" data-modal-close>
                                <i class="bi bi-check-lg me-1" aria-hidden="true"></i>Done
                            </button>
                        </div>
                    </div>
                </template>
            <?php endif; ?>

            <?php if ($flash_temporary_password): ?>
                <div class="alert alert-warning app-feedback-alert" role="status">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-key-fill mt-1"></i>
                            <div>
                                <div class="fw-semibold">Temporary password — shown once</div>
                                <div class="small mb-2">
                                    Copy this password now and share it securely with the new user.
                                </div>
                                <code
                                    class="d-inline-block px-3 py-2 rounded bg-body border fs-6 user-select-all"
                                    data-temporary-password
                                ><?php echo html_escape($flash_temporary_password); ?></code>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-dark"
                            data-copy-temporary-password
                        >
                            <i class="bi bi-copy me-1" aria-hidden="true"></i>Copy Password
                        </button>
                    </div>
                </div>
            <?php endif; ?>

<?php else: ?>

<main class="container py-4">

<?php endif; ?>
