<dialog id="category-delete-<?php echo (int) $category->id; ?>">
    <h3>Delete Category</h3>
    <p>Are you sure you want to delete this  <?php echo html_escape($category->category_name); ?> category?</p>
    <a href="<?php echo site_url('categories/delete/' . $category->id); ?>">Delete</a>
    <button type="button" onclick="this.closest('dialog').close();">Cancel</button>
</dialog>
