<h2>Dashboard</h2>

<div class="stats">
    <div class="card">
        <h3>Total Products</h3>
        <div class="value"><?php echo $total_products; ?></div>
    </div>

    <div class="card">
        <h3>Total Stock</h3>
        <div class="value"><?php echo $total_stock; ?></div>
    </div>

    <div class="card">
        <h3>Low Stock Items</h3>
        <div class="value"><?php echo $low_stock_items; ?></div>
    </div>

    <div class="card">
        <h3>Today's Stock In</h3>
        <div class="value"><?php echo $today_stock_in; ?></div>
    </div>

    <div class="card">
        <h3>Today's Stock Out</h3>
        <div class="value"><?php echo $today_stock_out; ?></div>
    </div>

    <div class="card">
        <h3>Inventory Value</h3>
        <div class="value"><?php echo number_format($inventory_value, 2); ?></div>
    </div>
</div>

<h3>Stock by Category</h3>
<table>
    <thead>
        <tr>
            <th>Category</th>
            <th>Products</th>
            <th>Total Stock</th>
        </tr>
    </thead>
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
    <thead>
        <tr>
            <th>Month</th>
            <th>Stock In</th>
            <th>Stock Out</th>
        </tr>
    </thead>
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
