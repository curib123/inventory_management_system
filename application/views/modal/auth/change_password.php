<?php
$password_change_required = !empty($password_change_required);

echo form_open(
    site_url('account/change-password'),
    array(
        'data-modal-form' => '1',
        'autocomplete' => 'off'
    )
);
?>

<?php
$this->load->view('components/modal/header', array(
    'modal_title' => 'Change Your Password',
    'modal_subtitle' => $password_change_required
        ? 'You are currently using a temporary or administrator-reset password. Choose a password only you know.'
        : 'Update your account password securely.',
    'modal_icon' => 'bi-key',
    'modal_variant' => 'warning',
    'modal_eyebrow' => $password_change_required ? 'First login security' : 'Account security'
));
?>

<div class="modal-body">
    <?php if (validation_errors()): ?>
        <div class="alert alert-danger"><?php echo validation_errors(); ?></div>
    <?php endif; ?>

    <?php if (!empty($password_error)): ?>
        <div class="alert alert-danger"><?php echo html_escape($password_error); ?></div>
    <?php endif; ?>

    <?php if ($password_change_required): ?>
        <div class="app-assist-note app-assist-note-warning mb-3">
            <span class="app-assist-note-icon">
                <i class="bi bi-shield-lock" aria-hidden="true"></i>
            </span>
            <div>
                <div class="app-assist-note-title">Temporary password detected</div>
                <div class="app-assist-note-text">
                    Change it now for better account security, or choose Ask later to continue using the system for this session.
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="vstack gap-3">
        <div>
            <label for="current_password" class="form-label">Current Password</label>
            <input
                type="password"
                id="current_password"
                name="current_password"
                class="form-control"
                required
                minlength="8"
                maxlength="255"
                autocomplete="current-password"
            >
            <div class="form-text">Enter the temporary password or your current account password.</div>
        </div>

        <div>
            <label for="new_password" class="form-label">New Password</label>
            <input
                type="password"
                id="new_password"
                name="new_password"
                class="form-control"
                required
                minlength="8"
                maxlength="255"
                autocomplete="new-password"
            >
            <div class="form-text">Use at least 8 characters and avoid reusing the temporary password.</div>
        </div>

        <div>
            <label for="confirm_password" class="form-label">Confirm New Password</label>
            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                class="form-control"
                required
                minlength="8"
                maxlength="255"
                autocomplete="new-password"
            >
        </div>
    </div>
</div>

<div class="modal-footer app-modal-footer">
    <div class="app-modal-footer-note">
        <i class="bi bi-info-circle" aria-hidden="true"></i>
        <span>
            <?php echo $password_change_required
                ? 'Ask later postpones this reminder until your next login.'
                : 'Your password changes immediately after it is saved.'; ?>
        </span>
    </div>

    <div class="app-modal-footer-actions">
        <?php if ($password_change_required): ?>
            <button
                type="submit"
                class="btn btn-outline-secondary"
                name="password_action"
                value="later"
                formnovalidate
            >
                <i class="bi bi-clock me-1" aria-hidden="true"></i>Ask later
            </button>
        <?php else: ?>
            <button type="button" class="btn btn-outline-secondary" data-modal-close>
                Cancel
            </button>
        <?php endif; ?>

        <button
            type="submit"
            class="btn btn-primary"
            name="password_action"
            value="change"
        >
            <i class="bi bi-check-lg me-1" aria-hidden="true"></i>Change Password
        </button>
    </div>
</div>

<?php echo form_close(); ?>
