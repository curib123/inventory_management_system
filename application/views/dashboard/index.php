<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
        <h2 class="h4 mb-1">Dashboard</h2>
        <p class="text-body-secondary mb-0">Inventory overview and current stock activity.</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="text-body-secondary small">Total Products</div>
                <div class="fs-3 fw-semibold"><?php echo (int) $total_products; ?></div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="text-body-secondary small">Total Stock Quantity</div>
                <div class="fs-3 fw-semibold"><?php echo (int) $total_stock; ?></div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="text-body-secondary small">Low Stock Items</div>
                <div class="fs-3 fw-semibold"><?php echo (int) $low_stock_items; ?></div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="text-body-secondary small">Today's Stock In</div>
                <div class="fs-3 fw-semibold"><?php echo (int) $today_stock_in; ?></div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="text-body-secondary small">Today's Stock Out</div>
                <div class="fs-3 fw-semibold"><?php echo (int) $today_stock_out; ?></div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="text-body-secondary small">Inventory Value</div>
                <div class="fs-3 fw-semibold">₱<?php echo number_format((float) $inventory_value, 2); ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-xl-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-body">
                <h3 class="h6 mb-0">Stock by Category</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table app-data-table table-hover align-middle mb-0">
                        <thead class="table-light">
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
                                <tr>
                                    <td colspan="3" class="text-center text-body-secondary py-4">No category stock data found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-body">
                <h3 class="h6 mb-0">Monthly Stock Movement</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table app-data-table table-hover align-middle mb-0">
                        <thead class="table-light">
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
                                <tr>
                                    <td colspan="3" class="text-center text-body-secondary py-4">No stock movement found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
