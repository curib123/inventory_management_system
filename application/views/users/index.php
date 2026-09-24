<?php
$this->load->view('components/datatable', array(
    'title' => 'User Management',
    'subtitle' => 'Create, view, update, and manage system user accounts.',
    'data_source' => site_url('users/datatable'),
    'actions' => array(
        array(
            'label' => 'Add User',
            'url' => site_url('users/add'),
            'icon' => 'bi-person-plus',
            'variant' => 'primary',
            'mode' => 'modal'
        )
    ),
    'columns' => array(
        'First Name',
        'Middle Name',
        'Last Name',
        'Username',
        'Role',
        'Status',
        'Created',
        'Updated',
        array('label' => 'Actions', 'orderable' => FALSE)
    )
));
?>

<?php $this->load->view('modal/container'); ?>
