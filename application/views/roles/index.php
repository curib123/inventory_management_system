<?php
$this->load->view('components/page_header', array(
    'title' => 'Roles and Permissions',
    'description' => 'Create roles and control which parts of the inventory system each role can access.',
    'actions' => array(
        array(
            'label' => 'Add Role',
            'icon' => 'bi-shield-plus',
            'class' => 'btn-primary',
            'modal_url' => site_url('roles/add')
        )
    )
));

$this->load->view('components/data_table', array(
    'source' => site_url('roles/datatable'),
    'table_id' => 'roles-table',
    'columns' => array(
        'Role',
        'Description',
        'Status',
        'Users',
        array('label' => 'Actions', 'orderable' => false)
    )
));

$this->load->view('modal/container');
?>