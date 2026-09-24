<?php if (validation_errors()): ?>
    <div class="alert alert-danger" role="alert"><?php echo validation_errors(); ?></div>
<?php endif; ?>

<?php if (!empty($form_error)): ?>
    <div class="alert alert-danger" role="alert"><?php echo html_escape($form_error); ?></div>
<?php endif; ?>
