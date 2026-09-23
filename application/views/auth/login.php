<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Login</title>
</head>
<body>
    <h1>Inventory Management System</h1>
    <h2>Login</h2>

    <?php if (isset($error) && $error): ?>
        <div><?php echo $error; ?></div>
    <?php endif; ?>

    <?php echo form_open('login'); ?>
        <p>
            <label for="username">Username</label><br>
            <input type="text" name="username" id="username" autocomplete="username" required minlength="3" maxlength="50" value="<?php echo html_escape(set_value('username')); ?>">
        </p>
        <p>
            <label for="password">Password</label><br>
            <input type="password" name="password" id="password" autocomplete="current-password" required minlength="6" maxlength="255">
        </p>
        <button type="submit">Login</button>
    <?php echo form_close(); ?>
</body>
</html>
