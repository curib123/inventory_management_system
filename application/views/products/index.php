<?php
$page_actions = array();

if ($this->authorization_service->has_permission($this->session->userdata('user_id'), 'products.create')) {
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
    'search_placeholder' => 'Search product code, name, category, or supplier...',
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
        ),
        array(
            'name' => 'stock',
            'label' => 'Stock level',
            'icon' => 'bi-box-seam',
            'options' => array(
                '' => 'All stock levels',
                'healthy' => 'Healthy stock',
                'low' => 'Low stock',
                'out' => 'Out of stock'
            )
        )
    ),
    'columns' => array(
        array('label' => 'ID', 'visible' => false, 'orderable' => false),
        'Code',
        'Name',
        'Category',
        'Supplier',
        array('label' => 'Stock', 'class' => 'text-end text-nowrap'),
        array('label' => 'Selling Price', 'class' => 'text-end text-nowrap'),
        array('label' => 'Status', 'class' => 'text-center text-nowrap', 'render' => 'status'),
        array('label' => 'Actions', 'orderable' => false, 'class' => 'text-end text-nowrap')
    )
));

?>