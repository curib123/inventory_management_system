<?php
$page_actions = array();

if ($this->authorization_service->has_permission($this->session->userdata('user_id'), 'suppliers.create')) {
    $page_actions[] = array(
        'label' => 'Add Supplier',
        'icon' => 'bi-plus-lg',
        'class' => 'btn-primary',
        'modal_url' => site_url('suppliers/add')
    );
}

$this->load->view('components/page_header', array(
    'title' => 'Suppliers',
    'description' => 'Manage supplier information and product sources.',
    'actions' => $page_actions
));

$this->load->view('components/data_table', array(
    'source' => site_url('suppliers/datatable'),
    'table_id' => 'suppliers-table',
    'search_placeholder' => 'Search supplier, contact person, phone, or address...',
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
        'Name',
        'Contact Person',
        array('label' => 'Phone', 'class' => 'text-nowrap'),
        'Address',
        array('label' => 'Status', 'class' => 'text-center text-nowrap', 'render' => 'status'),
        array('label' => 'Actions', 'orderable' => false, 'class' => 'text-end text-nowrap')
    )
));

?>