
<!-- Content -->
<div class="p-2">

    <?php if (!empty($delete_error)): ?>

        <!-- Error -->
        <div class="alert alert-danger d-flex align-items-start mb-0" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-3"></i>

            <div>
                <div class="fw-semibold mb-1">
                    Unable to delete role
                </div>

                <div class="small">
                    <?php echo html_escape($delete_error); ?>
                </div>
            </div>
        </div>

    <?php else: ?>

        <!-- Warning -->
        <div class="text-center px-2">

            <div
                class="bg-danger-subtle text-danger rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                style="width: 64px; height: 64px;"
            >
                <i class="bi bi-trash3 fs-3"></i>
            </div>

            <h5 class="mb-2">
                Are you sure?
            </h5>

            <p class="text-muted mb-0">
                You are about to delete
                <strong class="text-dark">
                    <?php echo html_escape($role->role_name); ?>
                </strong>.
                This action cannot be undone.
            </p>

        </div>

        <!-- Form -->
        <?php echo form_open(current_url(), array('data-modal-form' => '1')); ?>

            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">

                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-modal-close
                >
                    <i class="bi bi-x-lg me-1"></i>
                    Cancel
                </button>

                <button
                    type="submit"
                    class="btn btn-danger"
                >
                    <i class="bi bi-trash3 me-1"></i>
                    Delete Role
                </button>

            </div>

        <?php echo form_close(); ?>

    <?php endif; ?>

</div>

<?php if (!empty($delete_error)): ?>
    <div class="border-top p-3 d-flex justify-content-end">
        <button
            type="button"
            class="btn btn-outline-secondary"
            data-modal-close
        >
            <i class="bi bi-x-lg me-1"></i>
            Close
        </button>
    </div>
<?php endif; ?>

</div>
