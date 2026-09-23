<h2><?php echo isset($category) ? 'Edit Category' : 'Add Category'; ?></h2>

<?php echo form_open(current_url()); ?>
    <div class="form-group">
        <label>Category Name</label>
        <input type="text" name="category_name" value="<?php echo isset($category) ? $category->category_name : ''; ?>" required>
    </div>

    <div class="form-group">
        <label>Status</label>
        <select name="status">
            <option value="1" <?php echo (isset($category) && $category->status == 1) ? 'selected' : ''; ?>>Active</option>
            <option value="0" <?php echo (isset($category) && $category->status == 0) ? 'selected' : ''; ?>>Inactive</option>
        </select>
    </div>

    <div class="form-actions">
        <button class="btn btn-success" type="submit">Save</button>
        <a class="btn" href="<?php echo site_url('categories'); ?>">Cancel</a>
    </div>
<?php echo form_close(); ?>
