
<?php

$this->load->view('components/page_header', [
    'description' => 'Review inventory corrections and record physical stock adjustments.',
    'actions' => [
        [
            'label' => 'New Adjustment',
            'icon' => 'bi-sliders',
            'class' => 'btn-primary',
            'modal_url' => site_url('stock/adjustment'),
        ],
    ],
]);

$this->load->view('components/data_table', [
    'source' => site_url('stock/adjustments/datatable'),
    'table_id' => 'stock-adjustments-table',
    'search_placeholder' => 'Search product, reason, user, or date...',
    'filters' => [
        [
            'name' => 'difference',
            'label' => 'Adjustment',
            'icon' => 'bi-sliders',
            'options' => [
                '' => 'All adjustments',
                'increase' => 'Stock increased',
                'decrease' => 'Stock decreased',
            ],
        ],
        [
            'name' => 'period',
            'label' => 'Period',
            'icon' => 'bi-calendar3',
            'options' => [
                '' => 'All dates',
                'today' => 'Today',
                '7_days' => 'Last 7 days',
                '30_days' => 'Last 30 days',
            ],
        ],
    ],
    'columns' => [
        'Product',
        ['label' => 'System Stock', 'class' => 'text-end text-nowrap', 'render' => 'stock_value'],
        ['label' => 'Actual Stock', 'class' => 'text-end text-nowrap', 'render' => 'stock_value'],
        ['label' => 'Difference', 'class' => 'text-end text-nowrap', 'render' => 'difference'],
        'Reason',
        'Processed By',
        ['label' => 'Date', 'class' => 'text-nowrap'],
    ],
]);
