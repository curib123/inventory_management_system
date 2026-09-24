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

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-danger"
                        data-report-export
                        data-export-url="<?php echo site_url('reports/export/' . $report_key . '/pdf'); ?>"
                    >
                        <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($this->User_model->has_permission($this->session->userdata('user_id'), 'reports.export')): ?>
            <div class="app-report-export-note mt-3">
                <i class="bi bi-printer me-1"></i>
                Excel and PDF are formatted for professional review and printing. CSV includes report metadata and clean labeled columns for portability.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$table_columns = array();

foreach ($columns as $label) {
    $table_columns[] = $label;
}

$this->load->view('components/data_table', array(
    'source' => site_url('reports/datatable/' . $report_key),
    'table_id' => 'report-table',
    'columns' => $table_columns
));
?>
