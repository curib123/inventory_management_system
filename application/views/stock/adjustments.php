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
        array('label' => 'System Stock', 'class' => 'text-end text-nowrap'),
        array('label' => 'Actual Stock', 'class' => 'text-end text-nowrap'),
        array('label' => 'Difference', 'class' => 'text-end text-nowrap'),
        'Reason',
        'Processed By',
        array('label' => 'Date', 'class' => 'text-nowrap')
    )
));

$this->load->view('modal/container');
?>