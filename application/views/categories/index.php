<?php
$this->load->view('components/datatable', array(
    'title' => 'Categories',
    'subtitle' => 'Organize products into manageable inventory groups.',
    'data_source' => site_url('categories/datatable'),
    'actions' => array(
        array(
            'label' => 'Add Category',
            'url' => site_url('categories/add'),
            'icon' => 'bi-plus-lg',
            'variant' => 'primary',
            'mode' => 'modal'
        )
    ),
    'columns' => array(
        'ID',
        'Category',
        'Status',
        'Products',
        array('label' => 'Actions', 'orderable' => FALSE)
    )
));
?>

<?php $this->load->view('modal/container'); ?>
