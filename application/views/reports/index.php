<?php
$this->load->view('components/page_header', array(
    'title' => $report_title,
    'description' => 'Review inventory data and export a manager-ready business report.'
));
?>

<div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-sm <?php echo $report_key === 'inventory' ? 'btn-primary' : 'btn-outline-secondary'; ?>" href="<?php echo site_url('reports/inventory'); ?>">Inventory</a>
                <a class="btn btn-sm <?php echo $report_key === 'stock-in' ? 'btn-primary' : 'btn-outline-secondary'; ?>" href="<?php echo site_url('reports/stock-in'); ?>">Stock In</a>
                <a class="btn btn-sm <?php echo $report_key === 'stock-out' ? 'btn-primary' : 'btn-outline-secondary'; ?>" href="<?php echo site_url('reports/stock-out'); ?>">Stock Out</a>
                <a class="btn btn-sm <?php echo $report_key === 'movement' ? 'btn-primary' : 'btn-outline-secondary'; ?>" href="<?php echo site_url('reports/movement'); ?>">Movement</a>
                <a class="btn btn-sm <?php echo $report_key === 'low-stock' ? 'btn-primary' : 'btn-outline-secondary'; ?>" href="<?php echo site_url('reports/low-stock'); ?>">Low Stock</a>
                <a class="btn btn-sm <?php echo $report_key === 'valuation' ? 'btn-primary' : 'btn-outline-secondary'; ?>" href="<?php echo site_url('reports/valuation'); ?>">Valuation</a>
            </div>

            <?php if ($this->User_model->has_permission($this->session->userdata('user_id'), 'reports.export')): ?>
                <div class="d-flex flex-wrap gap-2" aria-label="Export report">
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary"
                        data-report-export
                        data-export-url="<?php echo site_url('reports/export/' . $report_key . '/csv'); ?>"
                    >
                        <i class="bi bi-filetype-csv me-1"></i>CSV
                    </button>

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-success"
                        data-report-export
                        data-export-url="<?php echo site_url('reports/export/' . $report_key . '/xlsx'); ?>"
                    >
                        <i class="bi bi-file-earmark-excel me-1"></i>Excel
                    </button>

                    <a
                        class="btn btn-sm btn-outline-danger"
                        href="<?php echo site_url('reports/export/' . $report_key . '/pdf'); ?>"
                        target="_blank"
                        rel="noopener"
                    >
                        <i class="bi bi-printer me-1"></i>Print PDF
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($this->User_model->has_permission($this->session->userdata('user_id'), 'reports.export')): ?>
            <div class="app-report-export-note mt-3">
                <i class="bi bi-printer me-1"></i>
                CSV and Excel download as report files. Print PDF opens a print-ready A4 landscape report in a new tab.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$table_columns = array();
$table_filters = array();
$numeric_fields = array('stock', 'quantity', 'reorder_level', 'shortage', 'cost_price', 'inventory_value');

if ($report_key === 'inventory' || $report_key === 'valuation') {
    $table_filters[] = array(
        'name' => 'stock',
        'label' => 'Stock level',
        'icon' => 'bi-box-seam',
        'options' => array(
            '' => 'All stock levels',
            'healthy' => 'Healthy stock',
            'low' => 'Low stock',
            'out' => 'Out of stock'
        )
    );
} elseif ($report_key === 'low-stock') {
    $table_filters[] = array(
        'name' => 'severity',
        'label' => 'Stock alert',
        'icon' => 'bi-exclamation-triangle',
        'options' => array(
            '' => 'All low stock',
            'out' => 'Out of stock',
            'low' => 'Low but available'
        )
    );
} elseif (in_array($report_key, array('stock-in', 'stock-out', 'movement'), TRUE)) {
    if ($report_key === 'movement') {
        $table_filters[] = array(
            'name' => 'type',
            'label' => 'Movement type',
            'icon' => 'bi-arrow-left-right',
            'options' => array(
                '' => 'All movements',
                'stock_in' => 'Stock In',
                'stock_out' => 'Stock Out',
                'adjustment' => 'Adjustment'
            )
        );
    }

    $table_filters[] = array(
        'name' => 'period',
        'label' => 'Period',
        'icon' => 'bi-calendar3',
        'options' => array(
            '' => 'All dates',
            'today' => 'Today',
            '7_days' => 'Last 7 days',
            '30_days' => 'Last 30 days'
        )
    );
}

foreach ($columns as $field => $label) {
    $table_columns[] = in_array($field, $numeric_fields, TRUE)
        ? array(
            'label' => $label,
            'class' => 'text-end text-nowrap',
            'render' => $field === 'stock' && $report_key === 'low-stock' ? 'stock_alert' : ''
        )
        : array(
            'label' => $label,
            'class' => in_array($field, array('transaction_no', 'type', 'unit', 'created_at'), TRUE)
                ? 'text-nowrap'
                : '',
            'render' => $field === 'type' ? 'movement' : ''
        );
}

$this->load->view('components/data_table', array(
    'source' => site_url('reports/datatable/' . $report_key),
    'table_id' => 'report-table',
    'search_placeholder' => 'Search this report...',
    'filters' => $table_filters,
    'columns' => $table_columns
));
?>
