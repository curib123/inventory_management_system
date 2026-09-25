<?php
$this->load->view('components/modal/header', array(
    'modal_title' => 'User Account Created',
    'modal_subtitle' => 'The account is ready. Copy the temporary password now because it will not be shown again.',
    'modal_icon' => 'bi-person-check',
    'modal_variant' => 'success',
    'modal_eyebrow' => 'Account ready'
));
?>

<div class="modal-body">
    <div class="app-confirmation-review">
        <div class="app-confirmation-review-icon app-confirmation-review-icon-success">
            <i class="bi bi-check2-circle" aria-hidden="true"></i>
        </div>
        <div>
            <div class="app-confirmation-review-title">
                <?php echo html_escape($username); ?>
            </div>
            <p class="app-confirmation-review-message mb-0">
                A secure temporary password was generated automatically.
            </p>
        </div>
    </div>

    <div class="mt-3 p-3 border rounded-3 bg-body-tertiary">
        <div class="small text-body-secondary fw-semibold mb-2">Temporary password</div>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <code
                class="fs-5 px-3 py-2 bg-body border rounded user-select-all"
                data-temporary-password
            ><?php echo html_escape($temporary_password); ?></code>

            <button
                type="button"
                class="btn btn-outline-primary"
                data-copy-temporary-password
            >
                <i class="bi bi-copy me-1" aria-hidden="true"></i>Copy Password
            </button>
        </div>
    </div>

    <div class="app-assist-note app-assist-note-warning mt-3">
        <span class="app-assist-note-icon">
            <i class="bi bi-shield-lock" aria-hidden="true"></i>
        </span>
        <div>
            <div class="app-assist-note-title">Share it securely</div>
            <div class="app-assist-note-text">
                The system stores only the password hash. The user will be prompted after login to choose a new password, but they can select Ask later for that session.
            </div>
        </div>
    </div>
</div>

<div class="modal-footer app-modal-footer">
    <div class="app-modal-footer-note">
        <i class="bi bi-info-circle" aria-hidden="true"></i>
        <span>This temporary password is intentionally shown only once.</span>
    </div>
    <div class="app-modal-footer-actions">
        <a class="btn btn-primary" href="<?php echo site_url('users'); ?>">
            <i class="bi bi-check-lg me-1" aria-hidden="true"></i>Done
        </a>
    </div>
</div>
