<?php
$this->load->view('components/datatable', array(
    'title' => 'Suppliers',
    'subtitle' => 'Manage supplier information and product sources.',
    'data_source' => site_url('suppliers/datatable'),
    'actions' => array(
        array(
            'label' => 'Add Supplier',
            'url' => site_url('suppliers/add'),
            'icon' => 'bi-plus-lg',
            'variant' => 'primary',
            'mode' => 'modal'
        )
    ),
    'columns' => array(
        'Name',
        'Contact Person',
        'Phone',
        'Address',
        'Status',
        'Created',
        'Updated',
        array('label' => 'Actions', 'orderable' => FALSE)
    )
));
?>

<?php $this->load->view('modal/container'); ?>
