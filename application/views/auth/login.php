<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    
</head>
<body>
    <div class="login-box">
        <h2>Inventory Login</h2>

        <?php if (isset($error)) : ?>
            <p><?php echo html_escape($error); ?></p>
        <?php endif; ?>

        <?php echo form_open('login'); ?>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" autocomplete="off" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" autocomplete="off" required>
            </div>

            <button type="submit">Login</button>
        <?php echo form_close(); ?>

        <div class="demo">
            Demo account: admin / admin123
        </div>
    </div>
</body>
</html>
