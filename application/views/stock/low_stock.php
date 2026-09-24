<?php
$this->load->view('components/datatable', array(
    'title' => 'Low Stock Monitoring',
    'subtitle' => 'Products that have reached or fallen below their reorder level.',
    'data_source' => site_url('stock/low-stock/datatable'),
    'columns' => array(
        'Code',
        'Product',
        'Current Stock',
        'Reorder Level',
        'Unit'
    )
));
?>
