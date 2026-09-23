<h2>Products</h2>
<a class="btn btn-success" href="<?php echo site_url('products/add'); ?>">Add Product</a>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Code</th>
            <th>Name</th>
            <th>Supplier</th>
            <th>Stock</th>
            <th>Price</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($products)): foreach ($products as $product): ?>
            <tr>
                <td><?php echo $product->id; ?></td>
                <td><?php echo $product->product_code; ?></td>
                <td><?php echo $product->product_name; ?></td>
                <td><?php echo isset($product->supplier_name) ? $product->supplier_name : 'N/A'; ?></td>
                <td><?php echo $product->stock; ?></td>
                <td><?php echo $product->selling_price; ?></td>
                <td>
                    <a class="btn" href="<?php echo site_url('products/edit/' . $product->id); ?>">Edit</a>
                    <a class="btn btn-danger" href="<?php echo site_url('products/delete/' . $product->id); ?>" onclick="return confirm('Delete this product?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; else: ?>
            <tr>
                <td colspan="7">No products found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php if (!empty($pagination)): ?>
    <?php echo $pagination; ?>
<?php endif; ?>
