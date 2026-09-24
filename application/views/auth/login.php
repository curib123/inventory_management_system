<?php $ui_styling_enabled = $this->config->item('ui_styling_enabled') !== FALSE; ?>
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
    >\n    <?php endif; ?>

    <title>Login | Inventory Management System</title>
</head>

<body class="bg-body-tertiary">
    <main class="container min-vh-100 d-flex align-items-center justify-content-center py-4">
        <div class="card shadow-sm border-0 w-100"<?php echo $ui_styling_enabled ? ' style="max-width: 420px;"' : ''; ?>>
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="display-6 text-primary mb-2">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <h1 class="h4 mb-1">Inventory Management System</h1>
                    <p class="text-body-secondary mb-0">Sign in to continue</p>
                </div>

                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo html_escape($this->session->flashdata('success')); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php echo form_open('login'); ?>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-person"></i>
                            </span>
                            <input
                                type="text"
                                name="username"
                                id="username"
                                class="form-control"
                                required
                                minlength="3"
                                maxlength="50"
                                autocomplete="username"
                                value="<?php echo html_escape(set_value('username')); ?>"
                            >
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-control"
                                required
                                minlength="8"
                                maxlength="255"
                                autocomplete="current-password"
                            >
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right me-1"></i>
                            Sign In
                        </button>
                    </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </main>
</body>
</html>
