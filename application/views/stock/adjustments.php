<?php
$this->load->view('components/page_header', array(
    'title' => 'Stock Adjustments',
    'description' => 'Review inventory corrections and record physical stock adjustments.',
    'actions' => array(
        array(
            'label' => 'New Adjustment',
            'icon' => 'bi-sliders',
            'class' => 'btn-primary',
            'modal_url' => site_url('stock/adjustment')
        )
    )
));

$this->load->view('components/data_table', array(
    'source' => site_url('stock/adjustments/datatable'),
    'table_id' => 'stock-adjustments-table',
    'search_placeholder' => 'Search product, reason, user, or date...',
    'filters' => array(
        array(
            'name' => 'difference',
            'label' => 'Adjustment',
            'icon' => 'bi-sliders',
            'options' => array(
                '' => 'All adjustments',
                'increase' => 'Stock increased',
                'decrease' => 'Stock decreased'
            )
        ),
        array(
            'name' => 'period',
            'label' => 'Period',
            'icon' => 'bi-calendar3',
            'options' => array(
                '' => 'All dates',
                'today' => 'Today',
                '7_days' => 'Last 7 days',
                '30_days' => 'Last 30 days'
            )
        )
    ),
    'columns' => array(
        'Product',
        array('label' => 'System Stock', 'class' => 'text-end text-nowrap'),
        array('label' => 'Actual Stock', 'class' => 'text-end text-nowrap'),
        array('label' => 'Difference', 'class' => 'text-end text-nowrap', 'render' => 'difference'),
        'Reason',
        'Processed By',
        array('label' => 'Date', 'class' => 'text-nowrap')
    )
));

?>