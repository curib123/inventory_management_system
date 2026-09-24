<?php
$this->load->view('components/datatable', array(
    'title' => 'Stock Movement History',
    'subtitle' => 'Review stock transactions and create new inventory movements.',
    'data_source' => site_url('stock/history/datatable'),
    'actions' => array(
        array('label' => 'Stock In', 'url' => site_url('stock/in'), 'icon' => 'bi-box-arrow-in-down', 'variant' => 'success', 'mode' => 'modal'),
        array('label' => 'Stock Out', 'url' => site_url('stock/out'), 'icon' => 'bi-box-arrow-up', 'variant' => 'warning', 'mode' => 'modal'),
        array('label' => 'Adjustment', 'url' => site_url('stock/adjustment'), 'icon' => 'bi-sliders', 'variant' => 'primary', 'mode' => 'modal'),
        array('label' => 'Adjustment History', 'url' => site_url('stock/adjustments'), 'icon' => 'bi-clock-history', 'variant' => 'outline-secondary', 'mode' => 'link')
    ),
    'columns' => array(
        'Transaction No.',
        'Type',
        'Supplier',
        'Processed By',
        'Date',
        array('label' => 'Action', 'orderable' => FALSE)
    )
));
?>

<?php $this->load->view('modal/container'); ?>
