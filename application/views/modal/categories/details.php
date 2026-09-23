<dialog id="category-details-<?php echo (int) $category->id; ?>">
    <h3>Category Details</h3>
    <p>Name: <?php echo html_escape($category->category_name); ?></p>
    <p>Status: <?php echo $category->status ? 'Active' : 'Inactive'; ?></p>
    <p>Products: <?php echo (int) $this->Category_model->count_products($category->id); ?></p>
    <button type="button" onclick="this.closest('dialog').close();">Close</button>
</dialog>
