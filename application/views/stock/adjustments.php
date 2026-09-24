<?php
$this->load->view('components/datatable', array(
    'title' => 'Stock Adjustments',
    'subtitle' => 'Review physical-stock corrections and their recorded reasons.',
    'data_source' => site_url('stock/adjustments/datatable'),
    'actions' => array(
        array(
            'label' => 'New Adjustment',
            'url' => site_url('stock/adjustment'),
            'icon' => 'bi-plus-lg',
            'variant' => 'primary',
            'mode' => 'modal'
        )
    ),
    'columns' => array(
        'Product',
        'System Stock',
        'Actual Stock',
        'Difference',
        'Reason',
        'Processed By',
        'Date'
    )
));
?>

<?php $this->load->view('modal/container'); ?>
