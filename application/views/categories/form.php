<h2><?php echo isset($category) ? 'Edit Category' : 'Add Category'; ?></h2>

<?php echo validation_errors(); ?>

<?php echo form_open(isset($form_action) ? $form_action : current_url()); ?>
    <div class="form-group">
        <label for="category_name">Category Name</label>
        <input type="text" id="category_name" name="category_name"  value="<?php echo html_escape(isset($category) ? $category->category_name : ''); ?>" autocomplete="off" required maxlength="20">
    </div>

    <div class="form-group">
        <label for="category_status">Status</label>
        <select id="category_status" name="status">
            <option value="1" <?php echo (isset($category) && $category->status == 1) ? 'selected' : ''; ?>>Active</option>
            <option value="0" <?php echo (isset($category) && $category->status == 0) ? 'selected' : ''; ?>>Inactive</option>
        </select>
    </div>

    <div class="form-actions">
        <button class="btn btn-success" type="submit">Save</button>
        <a class="btn" href="<?php echo site_url('categories'); ?>">Cancel</a>
    </div>
<?php echo form_close(); ?>
