<?php
$this->load->view('components/page_header', array(
    'title' => 'Suppliers',
    'description' => 'Manage supplier information and product sources.',
    'actions' => array(
        array(
            'label' => 'Add Supplier',
            'icon' => 'bi-plus-lg',
            'class' => 'btn-primary',
            'modal_url' => site_url('suppliers/add')
        )
    )
));

$this->load->view('components/data_table', array(
    'source' => site_url('suppliers/datatable'),
    'table_id' => 'suppliers-table',
    'columns' => array(
        'Name',
        'Contact Person',
        'Phone',
        'Address',
        'Status',
        array('label' => 'Actions', 'orderable' => false)
    )
));

$this->load->view('modal/container');
?>