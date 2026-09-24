<?php
$page_actions = array();

if ($this->User_model->has_permission($this->session->userdata('user_id'), 'products.create')) {
    $page_actions[] = array(
        'label' => 'Add Product',
        'icon' => 'bi-plus-lg',
        'class' => 'btn-primary',
        'modal_url' => site_url('products/add')
    );
}

$this->load->view('components/page_header', array(
    'title' => 'Products',
    'description' => 'Manage inventory products, pricing, suppliers, and stock settings.',
    'actions' => $page_actions
));

$this->load->view('components/data_table', array(
    'source' => site_url('products/datatable'),
    'table_id' => 'products-table',
    'columns' => array(
        'ID',
        'Code',
        'Name',
        'Category',
        'Supplier',
        'Stock',
        'Selling Price',
        'Status',
        array('label' => 'Actions', 'orderable' => false)
    )
));

$this->load->view('modal/container');
?>