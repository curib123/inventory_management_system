<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
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
                <input type="text" name="username" id="username" autocomplete="username" required minlength="3" maxlength="50">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" autocomplete="current-password" required minlength="6" maxlength="255">
            </div>

            <button type="submit">Login</button>
        <?php echo form_close(); ?>

        <div class="demo">
            Demo account: admin / admin123
        </div>
    </div>
</body>
</html>
