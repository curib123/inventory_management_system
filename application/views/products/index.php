<h2>Products</h2>
<p><a href="<?php echo site_url('products/add'); ?>">Add Product</a></p>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Code</th>
            <th>Name</th>
            <th>Category</th>
            <th>Supplier</th>
            <th>Stock</th>
            <th>Selling Price</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($products)): foreach ($products as $product): ?>
            <tr>
                <td><?php echo (int) $product->id; ?></td>
                <td><?php echo html_escape($product->product_code); ?></td>
                <td><?php echo html_escape($product->product_name); ?></td>
                <td><?php echo html_escape($product->category_name ?: 'N/A'); ?></td>
                <td><?php echo html_escape($product->supplier_name ?: 'N/A'); ?></td>
                <td><?php echo (int) $product->stock; ?></td>
                <td><?php echo number_format((float) $product->selling_price, 2); ?></td>
                <td><?php echo $product->status ? 'Active' : 'Inactive'; ?></td>
                <td>
                    <a href="<?php echo site_url('products/edit/' . (int) $product->id); ?>">Edit</a>
                    <?php echo form_open('products/delete/' . (int) $product->id); ?>
                        <button type="submit">Delete</button>
                    <?php echo form_close(); ?>
                </td>
            </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="9">No products found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

