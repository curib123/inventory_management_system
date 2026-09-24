<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap 5 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous"
    >

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <link rel="stylesheet"
          href="<?php echo base_url('assets/css/sidebar.css'); ?>">

    <title>
        <?php echo html_escape(
            isset($page_title) ? $page_title : 'Inventory Management System'
        ); ?>
    </title>
</head>

<body>

<?php if ($this->session->userdata('user_id')): ?>

    <!-- ================= SIDEBAR ================= -->
    <aside class="sidebar" id="sidebar">

        <!-- Brand -->
        <div class="sidebar-brand">
            <i class="bi bi-box-seam"></i>
            <span>Inventory System</span>
        </div>

     
        <!-- Navigation -->
        <div class="sidebar-heading">
            Main Menu
        </div>

        <nav class="nav flex-column" aria-label="Main navigation">

            <?php if (
                $this->User_model->has_permission(
                    $this->session->userdata('user_id'),
                    'view_dashboard'
                )
            ): ?>

                <a class="nav-link active"
                   href="<?php echo site_url('dashboard'); ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>

            <?php endif; ?>


            <?php if (
                $this->User_model->has_permission(
                    $this->session->userdata('user_id'),
                    'manage_products'
                )
            ): ?>

                <a class="nav-link"
                   href="<?php echo site_url('products'); ?>">
                    <i class="bi bi-box"></i>
                    <span>Products</span>
                </a>

            <?php endif; ?>


            <?php if (
                $this->User_model->has_permission(
                    $this->session->userdata('user_id'),
                    'manage_categories'
                )
            ): ?>

                <a class="nav-link"
                   href="<?php echo site_url('categories'); ?>">
                    <i class="bi bi-tags"></i>
                    <span>Categories</span>
                </a>

            <?php endif; ?>


            <?php if (
                $this->User_model->has_permission(
                    $this->session->userdata('user_id'),
                    'view_dashboard'
                )
            ): ?>

                <a class="nav-link"
                   href="<?php echo site_url('suppliers'); ?>">
                    <i class="bi bi-truck"></i>
                    <span>Suppliers</span>
                </a>

            <?php endif; ?>


            <?php if (
                $this->User_model->has_permission(
                    $this->session->userdata('user_id'),
                    'view_dashboard'
                )
            ): ?>

                <a class="nav-link"
                   href="<?php echo site_url('stock'); ?>">
                    <i class="bi bi-clock-history"></i>
                    <span>Stock History</span>
                </a>

            <?php endif; ?>


            <?php if (
                $this->User_model->has_permission(
                    $this->session->userdata('user_id'),
                    'view_dashboard'
                )
            ): ?>

                <a class="nav-link"
                   href="<?php echo site_url('stock/low-stock'); ?>">
                    <i class="bi bi-exclamation-triangle"></i>
                    <span>Low Stock</span>
                </a>

            <?php endif; ?>


            <?php if (
                $this->User_model->has_permission(
                    $this->session->userdata('user_id'),
                    'view_reports'
                )
            ): ?>

                <div class="sidebar-heading">
                    Reports
                </div>

                <a class="nav-link"
                   href="<?php echo site_url('reports'); ?>">
                    <i class="bi bi-bar-chart"></i>
                    <span>Reports</span>
                </a>

            <?php endif; ?>


            <?php if (
                $this->User_model->has_permission(
                    $this->session->userdata('user_id'),
                    'manage_users'
                )
            ): ?>

                <div class="sidebar-heading">
                    Administration
                </div>

                <a class="nav-link"
                   href="<?php echo site_url('users'); ?>">
                    <i class="bi bi-people"></i>
                    <span>Users</span>
                </a>

                <a class="nav-link"
                   href="<?php echo site_url('roles'); ?>">
                    <i class="bi bi-shield-lock"></i>
                    <span>Roles</span>
                </a>

            <?php endif; ?>

        </nav>

        <!-- Logout -->
        <div class="logout-section">

            <?php echo form_open('logout'); ?>

                <button type="submit" class="logout-btn">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    Logout
                </button>

            <?php echo form_close(); ?>

        </div>

    </aside>


    <!-- ================= MAIN CONTENT ================= -->
    <div class="main-content">

        <!-- Topbar -->
        <header class="topbar align-items-center justify-content-between">

            <div class = "d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold">
                    <?php echo html_escape(
                        isset($page_title)
                            ? $page_title
                            : 'Inventory Management System'
                    ); ?>
                </h5>  
            </div>

        <!-- User -->
        <div class="topnav-user">
            <div class="block " >

                <div class="user-avatar me-3">
                    <i class="bi bi-person"></i>
                </div>
                   <div class="d-flex">
                     <div class="username">
                        <?php echo html_escape(
                            $this->session->userdata('username')
                        ); ?>
                    </div>

                    <div class="role">
                        <?php echo html_escape(
                            $this->session->userdata('role_name')
                        ); ?>
                    </div>  
                   </div>
            </div>
        </div>

        </header>

        <div class="content-wrapper">

<?php else: ?>

    <!-- Guest page -->
    <main class="container py-4">

<?php endif; ?>

<!-- Bootstrap JS -->
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>



</body>
</html>

