<?php
$page_actions = array();

if ($this->authorization_service->has_permission($this->session->userdata('user_id'), 'roles.create')) {
    $page_actions[] = array(
        'label' => 'Add Role',
        'icon' => 'bi-shield-plus',
        'class' => 'btn-primary',
        'modal_url' => site_url('roles/add')
    );
}

$this->load->view('components/page_header', array(
    'title' => 'Roles and Permissions',
    'description' => 'Create roles and control which parts of the inventory system each role can access.',
    'actions' => $page_actions
));

$this->load->view('components/data_table', array(
    'source' => site_url('roles/datatable'),
    'table_id' => 'roles-table',
    'search_placeholder' => 'Search role or description...',
    'filters' => array(
        array(
            'name' => 'status',
            'label' => 'Status',
            'icon' => 'bi-shield-check',
            'options' => array(
                '' => 'All statuses',
                'active' => 'Active',
                'inactive' => 'Inactive'
            )
        )
    ),
    'columns' => array(
        'Role',
        'Description',
        array('label' => 'Status', 'class' => 'text-center text-nowrap', 'render' => 'status'),
        array('label' => 'Users', 'class' => 'text-end text-nowrap'),
        array('label' => 'Permissions', 'class' => 'text-end text-nowrap'),
        array('label' => 'Actions', 'orderable' => false, 'class' => 'text-end text-nowrap')
    )
));

?>