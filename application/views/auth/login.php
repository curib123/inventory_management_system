<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    >

    <link rel="stylesheet"
          href="<?php echo base_url('assets/css/login.css'); ?>">
    <title>Login</title>
</head>


<body class="auth-page min-vh-100 d-flex align-items-center justify-content-center">
        <div class="card login-card shadow">
            <div class="card-body p-3 p-sm-3">

                <div class="text-center mb-3">
                    <div class="login-logo bg-primary text-white rounded-3 d-inline-flex align-items-center justify-content-center mb-2">
                        <i class="bi bi-box-seam fs-3"></i>
                    </div>

                    <h1 class="h5 fw-bold mb-1">Inventory Management System</h1>
                    <p class="small text-secondary mb-0">Sign in to manage your inventory</p>
                </div>

                <div id="loginAlert" class="alert d-none align-items-center py-2 px-3 mb-3 small" role="alert">
                    <i id="alertIcon" class="bi me-2"></i>
                    <span id="alertMessage"></span>
                </div>

                <?php echo form_open('login'); ?>

                    <input
                        type="hidden"
                        id="csrfToken"
                        name="<?php echo $this->security->get_csrf_token_name(); ?>"
                        value="<?php echo $this->security->get_csrf_hash(); ?>"
                    >

                    <div class="mb-2">
                        <label for="username" class="form-label small fw-semibold mb-1">Username</label>

                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-person text-secondary"></i>
                            </span>

                            <input
                                type="text"
                                name="username"
                                id="username"
                                class="form-control"
                                placeholder="Username"
                                autocomplete="off"
                                required
                                minlength="2"
                                maxlength="50"
                                value="<?php echo html_escape(set_value('username')); ?>"
                            >
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label small fw-semibold mb-1">Password</label>

                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-lock text-secondary"></i>
                            </span>

                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-control"
                                placeholder="Password"
                                autocomplete="off"
                                required
                                minlength="6"
                                maxlength="255"
                            >

                           
                        </div>
                    </div>

                    <div class="d-grid">
                        <button
                            type="submit"
                            id="loginButton"
                            class="btn btn-primary fw-semibold"
                        >
                            <i id="loginIcon" class="bi bi-box-arrow-in-right me-1"></i>
                            <span id="loginText">Sign In</span>
                        </button>
                    </div>

                <?php echo form_close(); ?>
            </div>

            <div class="card-footer bg-white border-0 text-center py-2">
                <small class="text-secondary">
                    <i class="bi bi-shield-check me-1"></i>
                    Inventory Management System
                </small>
            </div>
        </div>
    
<script src="<?php echo base_url('assets/js/auth/login.js'); ?>"></script>

</body>
</html>

