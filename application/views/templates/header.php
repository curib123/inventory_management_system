
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

$current_username = trim((string) $this->session->userdata('username'));
$current_first_name = trim((string) $this->session->userdata('first_name'));
$current_last_name = trim((string) $this->session->userdata('last_name'));
$current_role_name = trim((string) $this->session->userdata('role_name'));
$current_display_name = trim($current_first_name . ' ' . $current_last_name);

if ($current_display_name === '') {
    $current_display_name = $current_username !== '' ? $current_username : 'User';
}

$current_name_parts = preg_split('/\s+/', $current_display_name);
$current_avatar_initials = '';

if (!empty($current_name_parts)) {
    $current_avatar_initials .= strtoupper(substr($current_name_parts[0], 0, 1));

    if (count($current_name_parts) > 1) {
        $current_avatar_initials .= strtoupper(substr($current_name_parts[count($current_name_parts) - 1], 0, 1));
    }
}

if ($current_avatar_initials === '') {
    $current_avatar_initials = 'U';
}

$current_role_label = $current_role_name !== ''
    ? ucwords(str_replace('_', ' ', $current_role_name))
    : 'User';

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
            <span>Inventory Management System</span>
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

            <div class="app-topbar-user-section">
                <button
                    type="button"
                    class="btn app-topbar-icon-button"
                    data-modal-url="<?php echo site_url('account/change-password'); ?>"
                    title="Change password"
                    aria-label="Change password"
                >
                    <i class="bi bi-key" aria-hidden="true"></i>
                </button>

                <div class="app-user-profile " title="Signed in as <?php echo html_escape($current_display_name); ?>">
                    <div class="app-user-avatar" aria-hidden="true">
                        <?php echo html_escape($current_avatar_initials); ?>
                    </div>

                    <div class="app-user-profile-copy d-none d-sm-flex">
                        <span class="app-user-profile-meta">
                            <?php echo html_escape($current_username); ?>
                          
                        </span>
                    </div>
                </div>
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
                        <div><?php echo html_escape($flash_error); ?></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Dismiss"></button>
                </div>
            <?php endif; ?>

            <?php if ($flash_warning): ?>
                <div class="alert alert-warning alert-dismissible fade show app-feedback-alert" role="alert">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                        <div><?php echo html_escape($flash_warning); ?></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Dismiss"></button>
                </div>
            <?php endif; ?>

            <?php if ($flash_success): ?>
                <template data-flash-success-template>
                    <div class="app-modal-header app-modal-header-success">
                        <div class="app-modal-heading">
                            <span class="app-modal-icon app-modal-icon-success">
                                <i class="bi bi-check2-circle" aria-hidden="true"></i>
                            </span>
                            <div class="app-modal-heading-copy">
                                <h2 class="app-modal-title" id="action-modal-title">Success</h2>
                            </div>
                        </div>
                        <button type="button" class="btn-close app-modal-close" data-modal-close aria-label="Close"></button>
                    </div>

                    <div class="app-modal-body">
                        <p class="mb-0"><?php echo html_escape($flash_success); ?></p>
                    </div>

                    <div class="app-modal-footer">
                        <div class="app-modal-footer-actions">
                            <button type="button" class="btn btn-success" data-modal-close>Done</button>
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
