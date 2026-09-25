<?php
$this->load->view('components/page_header', array(
    'title' => 'Low Stock Monitoring',
    'description' => 'Monitor products that have reached or fallen below their reorder level.'
));

$this->load->view('components/data_table', array(
    'source' => site_url('stock/low-stock/datatable'),
    'table_id' => 'low-stock-table',
    'search_placeholder' => 'Search product code, name, or unit...',
    'filters' => array(
        array(
            'name' => 'severity',
            'label' => 'Stock alert',
            'icon' => 'bi-exclamation-triangle',
            'options' => array(
                '' => 'All low stock',
                'out' => 'Out of stock',
                'low' => 'Low but available'
            )
        )
    ),
    'columns' => array(
        'Code',
        'Product',
        array('label' => 'Current Stock', 'class' => 'text-end text-nowrap', 'render' => 'stock_alert'),
        array('label' => 'Reorder Level', 'class' => 'text-end text-nowrap'),
        array('label' => 'Unit', 'class' => 'text-nowrap')
    )
));
?>