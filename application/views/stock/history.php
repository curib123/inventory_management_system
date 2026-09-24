<?php
$this->load->view('components/page_header', array(
    'title' => 'Stock Movement History',
    'description' => 'Track stock-in, stock-out, and adjustment activity across the inventory.',
    'actions' => array(
        array(
            'label' => 'Stock In',
            'icon' => 'bi-box-arrow-in-down',
            'class' => 'btn-success',
            'modal_url' => site_url('stock/in')
        ),
        array(
            'label' => 'Stock Out',
            'icon' => 'bi-box-arrow-up',
            'class' => 'btn-warning',
            'modal_url' => site_url('stock/out')
        ),
        array(
            'label' => 'Adjustment',
            'icon' => 'bi-sliders',
            'class' => 'btn-primary',
            'modal_url' => site_url('stock/adjustment')
        ),
        array(
            'label' => 'Adjustment History',
            'icon' => 'bi-clock-history',
            'class' => 'btn-outline-secondary',
            'url' => site_url('stock/adjustments')
        )
    )
));

$this->load->view('components/data_table', array(
    'source' => site_url('stock/history/datatable'),
    'table_id' => 'stock-history-table',
    'columns' => array(
        'Transaction No.',
        'Type',
        'Supplier',
        'Processed By',
        'Date',
        array('label' => 'Action', 'orderable' => false)
    )
));

$this->load->view('modal/container');
?>