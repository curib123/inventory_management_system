<h2>Categories</h2>
<a class="btn btn-success" href="<?php echo site_url('categories/add'); ?>">Add Category</a>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Category Name</th>
            <th>Status</th>
            <th>Products</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($categories)): foreach ($categories as $category): ?>
            <tr>
                <td><?php echo $category->id; ?></td>
                <td><?php echo $category->category_name; ?></td>
                <td><?php echo ($category->status == 1) ? 'Active' : 'Inactive'; ?></td>
                <td><?php echo $this->Category_model->count_products($category->id); ?></td>
                <td>
                    <a class="btn" href="<?php echo site_url('categories/edit/' . $category->id); ?>">Edit</a>
                    <a class="btn btn-danger" href="<?php echo site_url('categories/delete/' . $category->id); ?>" onclick="return confirm('Delete this category?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; else: ?>
            <tr>
                <td colspan="5">No categories found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php if (!empty($pagination)): ?>
    <?php echo $pagination; ?>
<?php endif; ?>
