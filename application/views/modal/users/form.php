<?php
$user_is_edit = isset($user) && $user;
$user_confirmation = array(
    'title' => $user_is_edit ? 'Save user changes?' : 'Create this user account?',
    'message' => $user_is_edit
        ? 'Review the user profile, role, status, and any password reset before saving.'
        : 'Review the account details and assigned role before creating access.',
    'impact' => $user_is_edit
        ? 'Role, status, and password reset changes can immediately affect this account.'
        : 'A secure temporary password will be generated automatically and shown once after the account is created.',
    'assist' => $user_is_edit
        ? 'Confirm the person, username, assigned role, account status, and whether a password reset is intended.'
        : 'Copy the generated temporary password after creation and share it securely with the user.',
    'label' => $user_is_edit ? 'Save User Changes' : 'Create User',
    'variant' => 'primary',
    'icon' => 'bi-person-check'
);

echo form_open(current_url(), ui_modal_form_attributes($user_confirmation));
?>
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
            <input type="text" id="first_name" name="first_name" class="form-control" required maxlength="100" value="<?php echo html_escape(set_value('first_name', $user_is_edit ? $user->first_name : '')); ?>">
        </div>

        <div class="col-12 col-md-6">
            <label for="middle_name" class="form-label">Middle Name</label>
            <input type="text" id="middle_name" name="middle_name" class="form-control" maxlength="100" value="<?php echo html_escape(set_value('middle_name', $user_is_edit ? $user->middle_name : '')); ?>">
        </div>

        <div class="col-12 col-md-6">
            <label for="last_name" class="form-label">Last Name</label>
            <input type="text" id="last_name" name="last_name" class="form-control" required maxlength="100" value="<?php echo html_escape(set_value('last_name', $user_is_edit ? $user->last_name : '')); ?>">
        </div>

        <div class="col-12 col-md-6">
            <label for="username" class="form-label">Username</label>
            <input type="text" id="username" name="username" class="form-control" required minlength="3" maxlength="50" value="<?php echo html_escape(set_value('username', $user_is_edit ? $user->username : '')); ?>">
            <div class="form-text">Use a unique username the user can identify and remember.</div>
        </div>

        <?php if ($user_is_edit): ?>
            <div class="col-12">
                <label for="password" class="form-label">
                    Reset Password <span class="text-body-secondary">(Optional)</span>
                </label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    minlength="8"
                    maxlength="255"
                    autocomplete="new-password"
                >
                <div class="form-text">
                    Leave blank to keep the current password. If you reset it here, the user will be prompted to change it after the next login.
                </div>
            </div>
        <?php else: ?>
            <div class="col-12">
                <?php
                $this->load->view('components/form/assist_note', array(
                    'assist_title' => 'Temporary password is generated automatically',
                    'assist_text' => 'After this account is created, the system will show a secure temporary password once. The user can sign in with it and will be asked to choose a new password.',
                    'assist_variant' => 'info',
                    'assist_icon' => 'bi-key'
                ));
                ?>
            </div>
        <?php endif; ?>

        <div class="col-12 col-md-6">
            <label for="role_id" class="form-label">Role</label>
            <?php $selected_role = set_value('role_id', $user_is_edit ? $user->role_id : ''); ?>
            <select
                id="role_id"
                name="role_id"
                class="form-select"
                required
                data-searchable-select
                data-search-placeholder="Search role name or description..."
                data-search-url="<?php echo site_url('users/roles/search'); ?>"
            >
                <option value="">Search then select a role</option>
                <?php foreach ($roles as $role): ?>
                    <option value="<?php echo (int) $role->id; ?>" <?php echo ((string) $selected_role === (string) $role->id) ? 'selected' : ''; ?>>
                        <?php echo html_escape($role->role_name . ((int) $role->status === 1 ? '' : ' (Inactive)')); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="form-text">Search runs on the server for large role lists. The selected role determines the modules and actions this user can access.</div>
        </div>

        <div class="col-12 col-md-6">
            <label for="status" class="form-label">Status</label>
            <?php $selected_status = set_value('status', $user_is_edit ? $user->status : 1); ?>
            <select id="status" name="status" class="form-select">
                <option value="1" <?php echo ((string) $selected_status === '1') ? 'selected' : ''; ?>>Active</option>
                <option value="0" <?php echo ((string) $selected_status === '0') ? 'selected' : ''; ?>>Inactive</option>
            </select>
            <div class="form-text">Inactive users cannot use the account for normal system access.</div>
        </div>
    </div>

    <div class="mt-3">
        <?php
        $this->load->view('components/form/assist_note', array(
            'assist_title' => 'Access check',
            'assist_text' => 'Confirm the assigned role carefully. Role and status changes can immediately change what this account can access.',
            'assist_variant' => 'warning',
            'assist_icon' => 'bi-shield-exclamation'
        ));
        ?>
    </div>
</div>

<?php
$this->load->view('components/modal/footer', array(
    'close_label' => 'Cancel',
    'submit_label' => $user_is_edit ? 'Save User' : 'Create User',
    'submit_icon' => 'bi-check-lg'
));
?>
<?php echo form_close(); ?>
