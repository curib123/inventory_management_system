<?php
$this->load->view('components/datatable', array(
    'title' => 'Products',
    'subtitle' => 'Manage inventory products, pricing, suppliers, and stock settings.',
    'data_source' => site_url('products/datatable'),
    'actions' => array(
        array(
            'label' => 'Add Product',
            'url' => site_url('products/add'),
            'icon' => 'bi-plus-lg',
            'variant' => 'primary',
            'mode' => 'modal'
        )
    ),
    'columns' => array(
        'ID',
        'Code',
        'Name',
        'Category',
        'Supplier',
        'Stock',
        'Selling Price',
        'Status',
        'Created',
        'Updated',
        array('label' => 'Actions', 'orderable' => FALSE)
    )
));
?>

<?php $this->load->view('modal/container'); ?>
