<h2>Dashboard</h2>

<ul>
    <li>Total Products: <?php echo (int) $total_products; ?></li>
    <li>Total Stock: <?php echo (int) $total_stock; ?></li>
    <li>Low Stock Items: <?php echo (int) $low_stock_items; ?></li>
    <li>Today's Stock In: <?php echo (int) $today_stock_in; ?></li>
    <li>Today's Stock Out: <?php echo (int) $today_stock_out; ?></li>
    <li>Inventory Value: <?php echo number_format((float) $inventory_value, 2); ?></li>
</ul>

<h3>Stock by Category</h3>
<table>
    <thead><tr><th>Category</th><th>Products</th><th>Total Stock</th></tr></thead>
    <tbody>
        <?php if (!empty($stock_by_category)): foreach ($stock_by_category as $category): ?>
            <tr>
                <td><?php echo html_escape($category->category_name); ?></td>
                <td><?php echo (int) $category->product_count; ?></td>
                <td><?php echo (int) $category->total_stock; ?></td>
            </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="3">No category stock data found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<h3>Monthly Stock Movement</h3>
<table>
    <thead><tr><th>Month</th><th>Stock In</th><th>Stock Out</th></tr></thead>
    <tbody>
        <?php if (!empty($monthly_movement)): foreach ($monthly_movement as $movement): ?>
            <tr>
                <td><?php echo html_escape($movement->month); ?></td>
                <td><?php echo (int) $movement->stock_in; ?></td>
                <td><?php echo (int) $movement->stock_out; ?></td>
            </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="3">No stock movement found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
