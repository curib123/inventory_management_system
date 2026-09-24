<?php
$this->load->view('components/page_header', array(
    'title' => 'User Management',
    'description' => 'Manage user accounts, names, roles, access status, and account activity.',
    'actions' => array(
        array(
            'label' => 'Add User',
            'icon' => 'bi-person-plus',
            'class' => 'btn-primary',
            'modal_url' => site_url('users/add')
        )
    )
));

$this->load->view('components/data_table', array(
    'source' => site_url('users/datatable'),
    'table_id' => 'users-table',
    'columns' => array(
        'First Name',
        'Middle Name',
        'Last Name',
        'Username',
        'Role',
        'Status',
        'Created',
        'Updated',
        array('label' => 'Actions', 'orderable' => false)
    )
));

$this->load->view('modal/container');
?>