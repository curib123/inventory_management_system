<h2>Low Stock Monitoring</h2>

<table>
    <thead>
        <tr>
            <th>Code</th>
            <th>Product</th>
            <th>Current Stock</th>
            <th>Reorder Level</th>
            <th>Unit</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($products)): foreach ($products as $product): ?>
            <tr>
                <td><?php echo html_escape($product->product_code); ?></td>
                <td><?php echo html_escape($product->product_name); ?></td>
                <td><?php echo (int) $product->stock; ?></td>
                <td><?php echo (int) $product->reorder_level; ?></td>
                <td><?php echo html_escape($product->unit); ?></td>
            </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="5">No low-stock products found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
