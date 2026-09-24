<?php
$this->load->view('components/page_header', array(
    'title' => 'Categories',
    'description' => 'Organize products into manageable inventory groups.',
    'actions' => array(
        array(
            'label' => 'Add Category',
            'icon' => 'bi-plus-lg',
            'class' => 'btn-primary',
            'modal_url' => site_url('categories/add')
        )
    )
));

$this->load->view('components/data_table', array(
    'source' => site_url('categories/datatable'),
    'table_id' => 'categories-table',
    'columns' => array(
        'ID',
        'Category',
        'Status',
        'Products',
        array('label' => 'Actions', 'orderable' => false)
    )
));

$this->load->view('modal/container');
?>