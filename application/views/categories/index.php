<h2>Categories</h2>
<p><a href="<?php echo site_url('categories/add'); ?>">Add Category</a></p>

<table>
    <thead>
        <tr><th>ID</th><th>Category</th><th>Status</th><th>Products</th><th>Actions</th></tr>
    </thead>
    <tbody>
        <?php if (!empty($categories)): foreach ($categories as $category): ?>
            <tr>
                <td><?php echo (int) $category->id; ?></td>
                <td><?php echo html_escape($category->category_name); ?></td>
                <td><?php echo $category->status ? 'Active' : 'Inactive'; ?></td>
                <td><?php echo (int) $this->Category_model->count_products($category->id); ?></td>
                <td>
                    <a href="<?php echo site_url('categories/edit/' . (int) $category->id); ?>">Edit</a>
                    <?php echo form_open('categories/delete/' . (int) $category->id); ?>
                        <button type="submit">Delete</button>
                    <?php echo form_close(); ?>
                </td>
            </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="5">No categories found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

