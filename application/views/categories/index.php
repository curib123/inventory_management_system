<?php
$page_actions = array();

if ($this->User_model->has_permission($this->session->userdata('user_id'), 'categories.create')) {
    $page_actions[] = array(
        'label' => 'Add Category',
        'icon' => 'bi-plus-lg',
        'class' => 'btn-primary',
        'modal_url' => site_url('categories/add')
    );
}

$this->load->view('components/page_header', array(
    'title' => 'Categories',
    'description' => 'Organize products into manageable inventory groups.',
    'actions' => $page_actions
));

$this->load->view('components/data_table', array(
    'source' => site_url('categories/datatable'),
    'table_id' => 'categories-table',
    'search_placeholder' => 'Search category name...',
    'filters' => array(
        array(
            'name' => 'status',
            'label' => 'Status',
            'icon' => 'bi-toggle-on',
            'options' => array(
                '' => 'All statuses',
                'active' => 'Active',
                'inactive' => 'Inactive'
            )
        )
    ),
    'columns' => array(
        array('label' => 'ID', 'visible' => false, 'orderable' => false),
        'Category',
        array('label' => 'Status', 'class' => 'text-center text-nowrap', 'render' => 'status'),
        array('label' => 'Products', 'class' => 'text-end text-nowrap'),
        array('label' => 'Actions', 'orderable' => false, 'class' => 'text-end text-nowrap')
    )
));

$this->load->view('modal/container');
?>