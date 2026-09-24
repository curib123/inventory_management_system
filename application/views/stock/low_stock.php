<?php
$this->load->view('components/page_header', array(
    'title' => 'Low Stock Monitoring',
    'description' => 'Monitor products that have reached or fallen below their reorder level.'
));

$this->load->view('components/data_table', array(
    'source' => site_url('stock/low-stock/datatable'),
    'table_id' => 'low-stock-table',
    'columns' => array(
        'Code',
        'Product',
        'Current Stock',
        'Reorder Level',
        'Unit'
    )
));
?>