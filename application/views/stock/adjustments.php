<?php
$this->load->view('components/page_header', array(
    'title' => 'Stock Adjustments',
    'description' => 'Review inventory corrections and record physical stock adjustments.',
    'actions' => array(
        array(
            'label' => 'New Adjustment',
            'icon' => 'bi-sliders',
            'class' => 'btn-primary',
            'modal_url' => site_url('stock/adjustment')
        )
    )
));

$this->load->view('components/data_table', array(
    'source' => site_url('stock/adjustments/datatable'),
    'table_id' => 'stock-adjustments-table',
    'columns' => array(
        'Product',
        'System Stock',
        'Actual Stock',
        'Difference',
        'Reason',
        'Processed By',
        'Date'
    )
));

$this->load->view('modal/container');
?>