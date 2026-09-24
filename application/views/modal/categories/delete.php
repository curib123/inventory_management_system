<div class="container rounded bg-light p-4 d-flex justify-content-center align-items-center text-center">
    <div class="container-sm">

        <?php if (!empty($delete_error)): ?>

            <p class="text-danger mb-3">
                <?php echo html_escape($delete_error); ?>
            </p>

            <button type="button" class="btn btn-secondary" data-modal-close>
                Close
            </button>

        <?php else: ?>

            <div class="bg-danger-subtle text-danger rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                 style="width: 64px; height: 64px;">
                <i class="bi bi-trash3 fs-3"></i>
            </div>

            <h4 class="text-muted mb-2">Delete Category</h4>

            <p class="text-danger mb-4">
                Are you sure you want to delete this
                <?php echo html_escape($category->category_name); ?>
                category?
            </p>

            <div class="d-flex justify-content-end align-items-center gap-2">
                <?php echo form_open(current_url(), array('data-modal-form' => '1')); ?>

                    <button class="btn btn-danger" type="submit">
                        Delete Now
                    </button>

                    <button class="btn btn-secondary" type="button" data-modal-close>
                        Cancel
                    </button>

                <?php echo form_close(); ?>
            </div>

        <?php endif; ?>

    </div>
</div>
