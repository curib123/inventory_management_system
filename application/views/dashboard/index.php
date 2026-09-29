<?php
$health_total =
    (int) $stock_health['healthy'] +
    (int) $stock_health['low_stock'] +
    (int) $stock_health['out_of_stock'];

$chart_data = array(
    'stockHealth' => array(
        'healthy' => (int) $stock_health['healthy'],
        'lowStock' => (int) $stock_health['low_stock'],
        'outOfStock' => (int) $stock_health['out_of_stock'],
        'total' => $health_total
    ),
    'stockByCategory' => array_map(function ($category) {
        return array(
            'category' => (string) $category->category_name,
            'products' => (int) $category->product_count,
            'stock' => (int) $category->total_stock
        );
    }, (array) $stock_by_category),
    'monthlyMovement' => array_values((array) $monthly_movement)
);

$dashboard_user_name = trim((string) $this->session->userdata('username'));

if ($dashboard_user_name === '') {
    $dashboard_user_name = trim(
        (string) $this->session->userdata('first_name') . ' ' .
        (string) $this->session->userdata('last_name')
    );
}

if ($dashboard_user_name === '') {
    $dashboard_user_name = 'User';
}

$dashboard_hour = (int) date('G');
$dashboard_greeting = $dashboard_hour < 12
    ? 'Good morning'
    : ($dashboard_hour < 18 ? 'Good afternoon' : 'Good evening');
?>

<div class="app-dashboard">
    <section class="app-dashboard-hero mb-4">
        <div class="app-dashboard-hero-copy">
            <div class="app-dashboard-eyebrow">Inventory command center</div>
            <h1 class="app-dashboard-title">
                <?php echo html_escape($dashboard_greeting); ?>,
                <?php echo html_escape($dashboard_user_name); ?>
            </h1>
            <p class="app-dashboard-subtitle mb-0">
                Know what needs attention across stock health, inventory value, and movement patterns.
            </p>
        </div>

        <div class="app-dashboard-hero-actions">
            <div class="app-dashboard-hero-meta">
                <i class="bi bi-calendar3" aria-hidden="true"></i>
                <span>Today</span>
            </div>

            <?php if ((int) $low_stock_items > 0): ?>
                <a href="<?php echo site_url('stock/low-stock'); ?>" class="app-dashboard-hero-attention">
                    <span class="app-dashboard-attention-icon">
                        <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                    </span>
                    <span class="app-dashboard-hero-attention-copy">
                        <strong>
                            <?php echo number_format((int) $low_stock_items); ?>
                            item<?php echo (int) $low_stock_items === 1 ? '' : 's'; ?> need attention
                        </strong>
                        <small>Review low stock</small>
                    </span>
                    <i class="bi bi-arrow-up-right ms-auto" aria-hidden="true"></i>
                </a>
            <?php else: ?>
                <div class="app-dashboard-hero-healthy">
                    <i class="bi bi-check2-circle" aria-hidden="true"></i>
                    <span>All stock levels look healthy</span>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <div class="app-dashboard-stat-grid mb-4">
            <div class="card app-dashboard-stat h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="app-dashboard-stat-top">
                        <span class="app-dashboard-stat-icon app-dashboard-stat-icon-primary">
                            <i class="bi bi-box-seam"></i>
                        </span>
                        <span class="app-dashboard-stat-label">Products</span>
                    </div>
                    <div class="app-dashboard-stat-value"><?php echo number_format((int) $total_products); ?></div>
                    <div class="app-dashboard-stat-help">All inventory product records</div>
                </div>
            </div>

        <div class="card app-dashboard-stat h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="app-dashboard-stat-top">
                        <span class="app-dashboard-stat-icon app-dashboard-stat-icon-info">
                            <i class="bi bi-boxes"></i>
                        </span>
                        <span class="app-dashboard-stat-label">Total Stock</span>
                    </div>
                    <div class="app-dashboard-stat-value"><?php echo number_format((int) $total_stock); ?></div>
                    <div class="app-dashboard-stat-help">Combined units on hand</div>
                </div>
            </div>

        <div class="card app-dashboard-stat app-dashboard-stat-feature h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="app-dashboard-stat-top">
                        <span class="app-dashboard-stat-icon app-dashboard-stat-icon-success">
                            <i class="bi bi-cash-stack"></i>
                        </span>
                        <span class="app-dashboard-stat-label">Inventory Value</span>
                    </div>
                    <div class="app-dashboard-stat-value app-dashboard-stat-value-money">
                        ₱<?php echo number_format((float) $inventory_value, 2); ?>
                    </div>
                    <div class="app-dashboard-stat-help">Stock quantity × cost price</div>
                </div>
            </div>

        <div class="card app-dashboard-stat h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="app-dashboard-stat-top">
                        <span class="app-dashboard-stat-icon app-dashboard-stat-icon-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                        </span>
                        <span class="app-dashboard-stat-label">Needs Attention</span>
                    </div>
                    <div class="app-dashboard-stat-value"><?php echo number_format((int) $low_stock_items); ?></div>
                    <div class="app-dashboard-stat-help">At or below reorder level</div>
                </div>
            </div>

        <div class="card app-dashboard-stat h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="app-dashboard-stat-top">
                        <span class="app-dashboard-stat-icon app-dashboard-stat-icon-success">
                            <i class="bi bi-box-arrow-in-down"></i>
                        </span>
                        <span class="app-dashboard-stat-label">Stock In Today</span>
                    </div>
                    <div class="app-dashboard-stat-value"><?php echo number_format((int) $today_stock_in); ?></div>
                    <div class="app-dashboard-stat-help">Units received today</div>
                </div>
            </div>

        <div class="card app-dashboard-stat h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="app-dashboard-stat-top">
                        <span class="app-dashboard-stat-icon app-dashboard-stat-icon-danger">
                            <i class="bi bi-box-arrow-up"></i>
                        </span>
                        <span class="app-dashboard-stat-label">Stock Out Today</span>
                    </div>
                    <div class="app-dashboard-stat-value"><?php echo number_format((int) $today_stock_out); ?></div>
                    <div class="app-dashboard-stat-help">Units released today</div>
                </div>
            </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-4">
            <section class="card app-dashboard-chart-card app-dashboard-chart-card-health h-100 shadow-sm border-0">
                <div class="card-header bg-body border-0">
                    <div class="app-dashboard-chart-heading">
                        <div>
                            <h3 class="h6 mb-1">Stock Health</h3>
                            <p class="text-body-secondary small mb-0">
                                Active products grouped by current stock condition.
                            </p>
                        </div>
                        <span class="badge rounded-pill text-bg-light border">
                            <?php echo number_format($health_total); ?> active
                        </span>
                    </div>
                </div>

                <div class="card-body pt-2">
                    <div class="app-chart app-chart-doughnut" data-chart-shell>
                        <canvas
                            id="stock-health-chart"
                            role="img"
                            aria-label="Doughnut chart showing healthy, low stock, and out of stock products">
                        </canvas>
                        <div class="app-chart-empty d-none" data-chart-empty>
                            <i class="bi bi-pie-chart"></i>
                            <div class="fw-semibold">No active product data yet</div>
                            <div class="small">Stock health will appear after products are added.</div>
                        </div>
                    </div>

                    <div class="app-dashboard-health-legend mt-3">
                        <div>
                            <span class="app-dashboard-health-dot app-dashboard-health-dot-success"></span>
                            <span>Healthy</span>
                            <strong><?php echo number_format((int) $stock_health['healthy']); ?></strong>
                        </div>
                        <div>
                            <span class="app-dashboard-health-dot app-dashboard-health-dot-warning"></span>
                            <span>Low Stock</span>
                            <strong><?php echo number_format((int) $stock_health['low_stock']); ?></strong>
                        </div>
                        <div>
                            <span class="app-dashboard-health-dot app-dashboard-health-dot-danger"></span>
                            <span>Out of Stock</span>
                            <strong><?php echo number_format((int) $stock_health['out_of_stock']); ?></strong>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="col-12 col-xl-8">
            <section class="card app-dashboard-chart-card app-dashboard-chart-card-category h-100 shadow-sm border-0">
                <div class="card-header bg-body border-0">
                    <div class="app-dashboard-chart-heading">
                        <div>
                            <h3 class="h6 mb-1">Stock by Category</h3>
                            <p class="text-body-secondary small mb-0">
                                Compare how much physical stock is currently held in each active category.
                            </p>
                        </div>
                        <span class="badge rounded-pill text-bg-light border">
                            Quantity
                        </span>
                    </div>
                </div>

                <div class="card-body pt-2">
                    <div class="app-chart app-chart-category" data-chart-shell>
                        <canvas
                            id="stock-category-chart"
                            role="img"
                            aria-label="Horizontal bar chart showing stock quantity by category">
                        </canvas>
                        <div class="app-chart-empty d-none" data-chart-empty>
                            <i class="bi bi-bar-chart"></i>
                            <div class="fw-semibold">No category stock data yet</div>
                            <div class="small">Category comparison will appear when active products have stock.</div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <section class="card app-dashboard-chart-card app-dashboard-chart-card-movement shadow-sm border-0">
        <div class="card-header bg-body border-0">
            <div class="app-dashboard-chart-heading">
                <div>
                    <h3 class="h6 mb-1">12-Month Stock Movement</h3>
                    <p class="text-body-secondary small mb-0">
                        Monthly Stock In and Stock Out quantities. Zero-activity months remain visible for an accurate trend.
                    </p>
                </div>
                <span class="badge rounded-pill text-bg-light border">
                    Last 12 months
                </span>
            </div>
        </div>

        <div class="card-body pt-2">
            <div class="app-chart app-chart-movement" data-chart-shell>
                <canvas
                    id="stock-movement-chart"
                    role="img"
                    aria-label="Line chart comparing monthly stock in and stock out quantities over the last 12 months">
                </canvas>
                <div class="app-chart-empty d-none" data-chart-empty>
                    <i class="bi bi-graph-up"></i>
                    <div class="fw-semibold">No stock movement recorded</div>
                    <div class="small">Movement trends will appear after Stock In or Stock Out transactions are recorded.</div>
                </div>
            </div>
        </div>
    </section>
</div>

<script
    id="dashboard-chart-data"
    type="application/json"
><?php echo json_encode(
    $chart_data,
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
); ?></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"></script>
<script src="<?php echo base_url('assets/js/dashboard.js'); ?>"></script>
