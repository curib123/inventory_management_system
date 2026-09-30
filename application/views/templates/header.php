
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

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">


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
    'activity_logs' => $current_controller === 'activity_logs',
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
            <span class="sidebar-brand-mark" aria-hidden="true">IMS</span>
            <span class="sidebar-brand-copy">
                <strong>Inventory</strong>
                <small>Operations workspace</small>
            </span>
        </div>

        <nav class="nav flex-column" aria-label="Main navigation">
            <?php if ($this->authorization_service->has_permission($current_user_id, 'dashboard.view')): ?>
                <div class="sidebar-section">
                    <div class="sidebar-heading">Workspace</div>

                    <a class="<?php echo $sidebar_link_class('dashboard'); ?>" href="<?php echo site_url('dashboard'); ?>"<?php echo $sidebar_aria_current('dashboard'); ?>>
                        <i class="bi bi-grid-1x2-fill" aria-hidden="true"></i>
                        <span class="nav-link-label">Dashboard</span>
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($this->authorization_service->has_any_permission($current_user_id, array('products.view', 'suppliers.view', 'stock.history', 'stock.view', 'categories.view'))): ?>
                <div class="sidebar-section">
                    <div class="sidebar-heading">Inventory</div>

                    <?php if ($this->authorization_service->has_permission($current_user_id, 'products.view')): ?>
                        <a class="<?php echo $sidebar_link_class('products'); ?>" href="<?php echo site_url('products'); ?>"<?php echo $sidebar_aria_current('products'); ?>>
                            <i class="bi bi-box-seam" aria-hidden="true"></i>
                            <span class="nav-link-label">Products</span>
                        </a>
                    <?php endif; ?>

                    <?php if ($this->authorization_service->has_permission($current_user_id, 'suppliers.view')): ?>
                        <a class="<?php echo $sidebar_link_class('suppliers'); ?>" href="<?php echo site_url('suppliers'); ?>"<?php echo $sidebar_aria_current('suppliers'); ?>>
                            <i class="bi bi-truck" aria-hidden="true"></i>
                            <span class="nav-link-label">Suppliers</span>
                        </a>
                    <?php endif; ?>

                    <?php if ($this->authorization_service->has_permission($current_user_id, 'stock.history')): ?>
                        <a class="<?php echo $sidebar_link_class('stock'); ?>" href="<?php echo site_url('stock'); ?>"<?php echo $sidebar_aria_current('stock'); ?>>
                            <i class="bi bi-arrow-left-right" aria-hidden="true"></i>
                            <span class="nav-link-label">Stock movements</span>
                        </a>
                    <?php endif; ?>

                    <?php if ($this->authorization_service->has_permission($current_user_id, 'stock.view')): ?>
                        <a class="<?php echo $sidebar_link_class('low_stock'); ?>" href="<?php echo site_url('stock/low-stock'); ?>"<?php echo $sidebar_aria_current('low_stock'); ?>>
                            <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                            <span class="nav-link-label">Low stock</span>
                        </a>
                    <?php endif; ?>

                    <?php if ($this->authorization_service->has_permission($current_user_id, 'categories.view')): ?>
                        <a class="<?php echo $sidebar_link_class('categories'); ?>" href="<?php echo site_url('categories'); ?>"<?php echo $sidebar_aria_current('categories'); ?>>
                            <i class="bi bi-tags" aria-hidden="true"></i>
                            <span class="nav-link-label">Categories</span>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($this->authorization_service->has_permission($current_user_id, 'reports.view')): ?>
                <div class="sidebar-section">
                    <div class="sidebar-heading">Insights</div>

                    <a class="<?php echo $sidebar_link_class('reports'); ?>" href="<?php echo site_url('reports'); ?>"<?php echo $sidebar_aria_current('reports'); ?>>
                        <i class="bi bi-bar-chart-fill" aria-hidden="true"></i>
                        <span class="nav-link-label">Reports &amp; exports</span>
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($this->authorization_service->has_any_permission($current_user_id, array('users.view', 'activity_logs.view', 'roles.view'))): ?>
                <div class="sidebar-section">
                    <div class="sidebar-heading">Administration</div>

                    <?php if ($this->authorization_service->has_permission($current_user_id, 'users.view')): ?>
                        <a class="<?php echo $sidebar_link_class('users'); ?>" href="<?php echo site_url('users'); ?>"<?php echo $sidebar_aria_current('users'); ?>>
                            <i class="bi bi-people" aria-hidden="true"></i>
                            <span class="nav-link-label">Users</span>
                        </a>
                    <?php endif; ?>

                    <?php if ($this->authorization_service->has_permission($current_user_id, 'activity_logs.view')): ?>
                        <a class="<?php echo $sidebar_link_class('activity_logs'); ?>" href="<?php echo site_url('activity-logs'); ?>"<?php echo $sidebar_aria_current('activity_logs'); ?>>
                            <i class="bi bi-journal-text" aria-hidden="true"></i>
                            <span class="nav-link-label">Activity logs</span>
                        </a>
                    <?php endif; ?>

                    <?php if ($this->authorization_service->has_permission($current_user_id, 'roles.view')): ?>
                        <a class="<?php echo $sidebar_link_class('roles'); ?>" href="<?php echo site_url('roles'); ?>"<?php echo $sidebar_aria_current('roles'); ?>>
                            <i class="bi bi-shield-lock" aria-hidden="true"></i>
                            <span class="nav-link-label">Roles &amp; permissions</span>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-account">
                <div class="sidebar-avatar" aria-hidden="true">
                    <?php echo html_escape($current_avatar_initials); ?>
                </div>

                <div class="sidebar-account-copy">
                    <strong><?php echo html_escape($current_display_name); ?></strong>
                    <span><?php echo html_escape($current_role_label); ?></span>
                </div>

                <span class="sidebar-account-status" role="img" title="Active account" aria-label="Active account"></span>
            </div>

            <button
                type="button"
                class="btn btn-danger w-100"
                data-modal-url="<?php echo site_url('logout/confirm'); ?>"
            >
                <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                <span>Sign out</span>
            </button>
        </div>
    </aside>

    <div class="sidebar-backdrop" id="sidebar-backdrop" aria-hidden="true"></div>

    <div class="main-content flex-grow-1">
        <header class="topbar d-flex align-items-center justify-content-between">
            <div class="topbar-context d-flex align-items-center min-w-0">
                <button
                    type="button"
                    class="btn mobile-toggle"
                    id="sidebar-toggle"
                    aria-label="Open navigation"
                    aria-controls="sidebar"
                    aria-expanded="false"
                >
                    <i class="bi bi-list" aria-hidden="true"></i>
                </button>

                <div class="topbar-heading min-w-0">
                    <span class="topbar-eyebrow">Inventory workspace</span>
                    <h1 class="topbar-title mb-0 text-truncate">
                        <?php echo html_escape(isset($page_title) ? $page_title : 'Inventory Management System'); ?>
                    </h1>
                </div>
            </div>

            <div class="topbar-actions app-topbar-user-section">
                <button
                    type="button"
                    class="btn app-topbar-icon-button topbar-security-button"
                    data-modal-url="<?php echo site_url('account/change-password'); ?>"
                    title="Change password"
                    aria-label="Change password"
                >
                    <i class="bi bi-key" aria-hidden="true"></i>
                    <span class="topbar-security-label">Security</span>
                </button>

                <div class="app-user-profile" title="Signed in as <?php echo html_escape($current_display_name); ?>">
                    <div class="app-user-avatar" aria-hidden="true">
                        <?php echo html_escape($current_avatar_initials); ?>
                    </div>

                    <div class="app-user-profile-copy d-none d-sm-flex">
                        <strong class="app-user-profile-name">
                            <?php echo html_escape($current_display_name); ?>
                        </strong>
                        <span class="app-user-profile-meta">
                            <span class="app-user-profile-role">
                                <?php echo html_escape($current_role_label); ?>
                            </span>
                            <span class="topbar-user-separator" aria-hidden="true">&bull;</span>
                            <?php echo html_escape($current_username); ?>
                        </span>
                    </div>

                    <span class="topbar-user-status" role="img" title="Active account" aria-label="Active account"></span>
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
