<?php echo form_open(current_url(), array('data-modal-form' => '1')); ?>
    <div class="modal-header">
        <h2 class="modal-title fs-5"><?php echo html_escape($page_title); ?></h2>
        <button type="button" class="btn-close" data-modal-close aria-label="Close"></button>
    </div>

    <div class="modal-body">
        <?php if (validation_errors()): ?>
            <div class="alert alert-danger"><?php echo validation_errors(); ?></div>
        <?php endif; ?>

        <?php if (!empty($form_error)): ?>
            <div class="alert alert-danger"><?php echo html_escape($form_error); ?></div>
        <?php endif; ?>

        <div class="mb-3">
            <label for="category_name" class="form-label">Category Name</label>
            <input type="text" id="category_name" name="category_name" class="form-control" required maxlength="100" value="<?php echo html_escape(set_value('category_name', isset($category) && $category ? $category->category_name : '')); ?>">
        </div>

        <div>
            <label for="status" class="form-label">Status</label>
            <?php $selected_status = set_value('status', isset($category) && $category ? $category->status : 1); ?>
            <select id="status" name="status" class="form-select">
                <option value="1" <?php echo ((string) $selected_status === '1') ? 'selected' : ''; ?>>Active</option>
                <option value="0" <?php echo ((string) $selected_status === '0') ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
        <button type="submit" class="btn btn-primary">Save Category</button>
    </div>
<?php echo form_close(); ?>
