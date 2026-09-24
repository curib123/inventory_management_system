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

    <title>Initial Setup | Inventory Management System</title>
</head>
<body class="bg-body-tertiary">
    <main class="container min-vh-100 d-flex align-items-center justify-content-center py-4">
        <div class="card shadow-sm border-0 w-100" style="max-width: 640px;">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="display-6 text-primary mb-2">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <h1 class="h4 mb-1">Create the First Administrator</h1>
                    <p class="text-body-secondary mb-0">
                        This setup is available only while no user account exists.
                    </p>
                </div>

                <?php if (validation_errors() || !empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo validation_errors('', ' '); ?>
                        <?php echo !empty($error) ? html_escape($error) : ''; ?>
                    </div>
                <?php endif; ?>

                <?php echo form_open('setup'); ?>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="first_name" class="form-label">First Name</label>
                            <input
                                type="text"
                                id="first_name"
                                name="first_name"
                                class="form-control"
                                maxlength="100"
                                required
                                value="<?php echo html_escape(set_value('first_name')); ?>"
                            >
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="middle_name" class="form-label">Middle Name</label>
                            <input
                                type="text"
                                id="middle_name"
                                name="middle_name"
                                class="form-control"
                                maxlength="100"
                                value="<?php echo html_escape(set_value('middle_name')); ?>"
                            >
                        </div>

                        <div class="col-12">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input
                                type="text"
                                id="last_name"
                                name="last_name"
                                class="form-control"
                                maxlength="100"
                                required
                                value="<?php echo html_escape(set_value('last_name')); ?>"
                            >
                        </div>

                        <div class="col-12">
                            <label for="username" class="form-label">Administrator Username</label>
                            <input
                                type="text"
                                id="username"
                                name="username"
                                class="form-control"
                                minlength="3"
                                maxlength="50"
                                pattern="[A-Za-z0-9_-]+"
                                autocomplete="username"
                                required
                                value="<?php echo html_escape(set_value('username')); ?>"
                            >
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="password" class="form-label">Password</label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control"
                                minlength="12"
                                maxlength="255"
                                autocomplete="new-password"
                                required
                            >
                            <div class="form-text">Use at least 12 characters for the initial administrator.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="password_confirm" class="form-label">Confirm Password</label>
                            <input
                                type="password"
                                id="password_confirm"
                                name="password_confirm"
                                class="form-control"
                                minlength="12"
                                maxlength="255"
                                autocomplete="new-password"
                                required
                            >
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-person-check me-1"></i>
                            Create Administrator
                        </button>
                    </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </main>
</body>
</html>
