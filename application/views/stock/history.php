<?php
$current_user_id = (int) $this->session->userdata('user_id');
$page_actions = array();

if ($this->User_model->has_permission($current_user_id, 'stock.stock_in')) {
    $page_actions[] = array(
        'label' => 'Stock In',
        'icon' => 'bi-box-arrow-in-down',
        'class' => 'btn-primary',
        'modal_url' => site_url('stock/in')
    );
}

if ($this->User_model->has_permission($current_user_id, 'stock.stock_out')) {
    $page_actions[] = array(
        'label' => 'Stock Out',
        'icon' => 'bi-box-arrow-up',
        'class' => 'btn-danger',
        'modal_url' => site_url('stock/out')
    );
}

if ($this->User_model->has_permission($current_user_id, 'stock.adjust')) {
    $page_actions[] = array(
        'label' => 'Adjustment',
        'icon' => 'bi-sliders',
        'class' => 'btn-warning',
        'modal_url' => site_url('stock/adjustment')
    );

    $page_actions[] = array(
        'label' => 'Adjustment History',
        'icon' => 'bi-clock-history',
        'class' => 'btn-outline-secondary',
        'url' => site_url('stock/adjustments')
    );
}

$this->load->view('components/page_header', array(
    'title' => 'Stock Movement History',
    'description' => 'Track stock-in, stock-out, and adjustment activity across the inventory.',
    'actions' => $page_actions
));

$this->load->view('components/data_table', array(
    'source' => site_url('stock/history/datatable'),
    'table_id' => 'stock-history-table',
    'columns' => array(
        array('label' => 'Transaction No.', 'class' => 'text-nowrap'),
        array('label' => 'Type', 'class' => 'text-nowrap'),
        'Supplier',
        'Processed By',
        array('label' => 'Date', 'class' => 'text-nowrap'),
        array('label' => 'Action', 'orderable' => false, 'class' => 'text-end text-nowrap')
    )
));

$this->load->view('modal/container');
?>