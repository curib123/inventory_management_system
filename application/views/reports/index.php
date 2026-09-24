<?php
$report_actions = array(
    array('label' => 'Inventory', 'url' => site_url('reports/inventory'), 'icon' => 'bi-boxes', 'variant' => 'outline-secondary', 'mode' => 'link'),
    array('label' => 'Stock In', 'url' => site_url('reports/stock-in'), 'icon' => 'bi-box-arrow-in-down', 'variant' => 'outline-secondary', 'mode' => 'link'),
    array('label' => 'Stock Out', 'url' => site_url('reports/stock-out'), 'icon' => 'bi-box-arrow-up', 'variant' => 'outline-secondary', 'mode' => 'link'),
    array('label' => 'Movement', 'url' => site_url('reports/movement'), 'icon' => 'bi-arrow-left-right', 'variant' => 'outline-secondary', 'mode' => 'link'),
    array('label' => 'Low Stock', 'url' => site_url('reports/low-stock'), 'icon' => 'bi-exclamation-triangle', 'variant' => 'outline-secondary', 'mode' => 'link'),
    array('label' => 'Valuation', 'url' => site_url('reports/valuation'), 'icon' => 'bi-cash-stack', 'variant' => 'outline-secondary', 'mode' => 'link'),
    array('label' => 'CSV', 'url' => site_url('reports/export/' . $report_key . '/csv'), 'icon' => 'bi-filetype-csv', 'variant' => 'outline-success', 'mode' => 'link'),
    array('label' => 'Excel', 'url' => site_url('reports/export/' . $report_key . '/xlsx'), 'icon' => 'bi-file-earmark-excel', 'variant' => 'outline-success', 'mode' => 'link'),
    array('label' => 'PDF', 'url' => site_url('reports/export/' . $report_key . '/pdf'), 'icon' => 'bi-file-earmark-pdf', 'variant' => 'outline-danger', 'mode' => 'link')
);

$report_columns = array();
foreach ($columns as $label) {
    $report_columns[] = $label;
}

$this->load->view('components/datatable', array(
    'title' => $report_title,
    'subtitle' => 'View server-side report data and export the complete report.',
    'data_source' => site_url('reports/datatable/' . $report_key),
    'actions' => $report_actions,
    'columns' => $report_columns
));
?>
