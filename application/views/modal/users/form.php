<?php echo form_open(current_url(), array('data-modal-form' => '1')); ?>
<?php
$this->load->view('components/modal/header', array(
    'modal_title' => $page_title,
    'modal_subtitle' => 'Enter the user information and access settings.',
    'modal_icon' => 'bi-person'
));
?>

<div class="modal-body">
    <?php if (validation_errors()): ?>
        <div class="alert alert-danger"><?php echo validation_errors(); ?></div>
    <?php endif; ?>

    <?php if (!empty($form_error)): ?>
        <div class="alert alert-danger"><?php echo html_escape($form_error); ?></div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-12 col-md-6">
            <label for="first_name" class="form-label">First Name</label>
            <input type="text" id="first_name" name="first_name" class="form-control" required maxlength="100" value="<?php echo html_escape(set_value('first_name', isset($user) && $user ? $user->first_name : '')); ?>">
        </div>

        <div class="col-12 col-md-6">
            <label for="middle_name" class="form-label">Middle Name</label>
            <input type="text" id="middle_name" name="middle_name" class="form-control" maxlength="100" value="<?php echo html_escape(set_value('middle_name', isset($user) && $user ? $user->middle_name : '')); ?>">
        </div>

        <div class="col-12 col-md-6">
            <label for="last_name" class="form-label">Last Name</label>
            <input type="text" id="last_name" name="last_name" class="form-control" required maxlength="100" value="<?php echo html_escape(set_value('last_name', isset($user) && $user ? $user->last_name : '')); ?>">
        </div>

        <div class="col-12 col-md-6">
            <label for="username" class="form-label">Username</label>
            <input type="text" id="username" name="username" class="form-control" required minlength="3" maxlength="50" value="<?php echo html_escape(set_value('username', isset($user) && $user ? $user->username : '')); ?>">
        </div>

        <div class="col-12">
            <label for="password" class="form-label">
                Password<?php echo isset($user) && $user ? ' (leave blank to keep current password)' : ''; ?>
            </label>
            <input type="password" id="password" name="password" class="form-control" <?php echo isset($user) && $user ? '' : 'required'; ?> minlength="8" maxlength="255" autocomplete="new-password">
        </div>

        <div class="col-12 col-md-6">
            <label for="role_id" class="form-label">Role</label>
            <?php $selected_role = set_value('role_id', isset($user) && $user ? $user->role_id : ''); ?>
            <select id="role_id" name="role_id" class="form-select" required>
                <option value="">Select Role</option>
                <?php foreach ($roles as $role): ?>
                    <option value="<?php echo (int) $role->id; ?>" <?php echo ((string) $selected_role === (string) $role->id) ? 'selected' : ''; ?>><?php echo html_escape($role->role_name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-12 col-md-6">
            <label for="status" class="form-label">Status</label>
            <?php $selected_status = set_value('status', isset($user) && $user ? $user->status : 1); ?>
            <select id="status" name="status" class="form-select">
                <option value="1" <?php echo ((string) $selected_status === '1') ? 'selected' : ''; ?>>Active</option>
                <option value="0" <?php echo ((string) $selected_status === '0') ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>
    </div>
</div>

<?php
$this->load->view('components/modal/footer', array(
    'close_label' => 'Cancel',
    'submit_label' => 'Save User',
    'submit_icon' => 'bi-check-lg'
));
?>
<?php echo form_close(); ?>