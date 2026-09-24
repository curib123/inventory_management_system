<?php
$this->load->view('components/page_header', array(
    'title' => $report_title,
    'description' => 'Review inventory data and export the current report when needed.'
));
?>

<div class="card shadow-sm border-0 mb-3">
    <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-sm <?php echo $report_key === 'inventory' ? 'btn-primary' : 'btn-outline-secondary'; ?>" href="<?php echo site_url('reports/inventory'); ?>">Inventory</a>
            <a class="btn btn-sm <?php echo $report_key === 'stock-in' ? 'btn-primary' : 'btn-outline-secondary'; ?>" href="<?php echo site_url('reports/stock-in'); ?>">Stock In</a>
            <a class="btn btn-sm <?php echo $report_key === 'stock-out' ? 'btn-primary' : 'btn-outline-secondary'; ?>" href="<?php echo site_url('reports/stock-out'); ?>">Stock Out</a>
            <a class="btn btn-sm <?php echo $report_key === 'movement' ? 'btn-primary' : 'btn-outline-secondary'; ?>" href="<?php echo site_url('reports/movement'); ?>">Movement</a>
            <a class="btn btn-sm <?php echo $report_key === 'low-stock' ? 'btn-primary' : 'btn-outline-secondary'; ?>" href="<?php echo site_url('reports/low-stock'); ?>">Low Stock</a>
            <a class="btn btn-sm <?php echo $report_key === 'valuation' ? 'btn-primary' : 'btn-outline-secondary'; ?>" href="<?php echo site_url('reports/valuation'); ?>">Valuation</a>
        </div>

        <?php if ($this->User_model->has_permission($this->session->userdata('user_id'), 'reports.export')): ?>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-sm btn-outline-secondary" href="<?php echo site_url('reports/export/' . $report_key . '/csv'); ?>"><i class="bi bi-filetype-csv me-1"></i>CSV</a>
                <a class="btn btn-sm btn-outline-secondary" href="<?php echo site_url('reports/export/' . $report_key . '/xlsx'); ?>"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
                <a class="btn btn-sm btn-outline-secondary" href="<?php echo site_url('reports/export/' . $report_key . '/pdf'); ?>"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
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