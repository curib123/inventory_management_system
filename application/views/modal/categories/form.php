<h2><?php echo html_escape($page_title); ?></h2>
<?php echo validation_errors(); ?>
<?php if (!empty($form_error)): ?><p><?php echo html_escape($form_error); ?></p><?php endif; ?>

<?php echo form_open(current_url(), array('data-modal-form' => '1')); ?>
    <p>
        <label for="category_name">Category Name</label><br>
        <input type="text" id="category_name" name="category_name" required maxlength="100" value="<?php echo html_escape(set_value('category_name', isset($category) && $category ? $category->category_name : '')); ?>">
    </p>
    <p>
        <label for="status">Status</label><br>
        <?php $selected_status = set_value('status', isset($category) && $category ? $category->status : 1); ?>
        <select id="status" name="status">
            <option value="1" <?php echo ((string) $selected_status === '1') ? 'selected' : ''; ?>>Active</option>
            <option value="0" <?php echo ((string) $selected_status === '0') ? 'selected' : ''; ?>>Inactive</option>
        </select>
    </p>
    <button type="submit">Save Category</button>
    <button type="button" data-modal-close>Cancel</button>
<?php echo form_close(); ?>
