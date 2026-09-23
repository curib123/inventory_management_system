<h2>Delete User</h2>
<p>User: <?php echo html_escape($user->first_name . ' ' . $user->last_name); ?> (<?php echo html_escape($user->username); ?>)</p>
<?php if (!empty($delete_error)): ?>
    <p><?php echo html_escape($delete_error); ?></p>
    <button type="button" data-modal-close>Close</button>
<?php else: ?>
    <p>Are you sure you want to delete this user?</p>
    <?php echo form_open(current_url(), array('data-modal-form' => '1')); ?>
        <button type="submit">Delete User</button>
        <button type="button" data-modal-close>Cancel</button>
    <?php echo form_close(); ?>
<?php endif; ?>
