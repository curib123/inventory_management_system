<?php
$page_actions = array();

if ($this->User_model->has_permission($this->session->userdata('user_id'), 'users.create')) {
    $page_actions[] = array(
        'label' => 'Add User',
        'icon' => 'bi-person-plus',
        'class' => 'btn-primary',
        'modal_url' => site_url('users/add')
    );
}

$this->load->view('components/page_header', array(
    'title' => 'User Management',
    'description' => 'Manage user accounts, names, roles, access status, and account activity.',
    'actions' => $page_actions
));

$this->load->view('components/data_table', array(
    'source' => site_url('users/datatable'),
    'table_id' => 'users-table',
    'search_placeholder' => 'Search name, username, or role...',
    'filters' => array(
        array(
            'name' => 'status',
            'label' => 'Account status',
            'icon' => 'bi-person-check',
            'options' => array(
                '' => 'All accounts',
                'active' => 'Active',
                'inactive' => 'Inactive'
            )
        )
    ),
    'columns' => array(
        'First Name',
        'Middle Name',
        'Last Name',
        'Username',
        'Role',
        array('label' => 'Status', 'class' => 'text-center text-nowrap', 'render' => 'status'),
        array('label' => 'Created', 'class' => 'text-nowrap'),
        array('label' => 'Updated', 'class' => 'text-nowrap'),
        array('label' => 'Actions', 'orderable' => false, 'class' => 'text-end text-nowrap')
    )
));

?>