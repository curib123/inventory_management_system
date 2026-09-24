<?php
$this->load->view('components/datatable', array(
    'title' => 'Roles and Permissions',
    'subtitle' => 'Control system access through reusable roles and permissions.',
    'data_source' => site_url('roles/datatable'),
    'actions' => array(
        array(
            'label' => 'Add Role',
            'url' => site_url('roles/add'),
            'icon' => 'bi-shield-plus',
            'variant' => 'primary',
            'mode' => 'modal'
        )
    ),
    'columns' => array(
        'Role',
        'Description',
        'Status',
        'Users',
        array('label' => 'Actions', 'orderable' => FALSE)
    )
));
?>

<?php $this->load->view('modal/container'); ?>
