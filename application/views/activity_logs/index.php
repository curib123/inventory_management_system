<?php
$this->load->view('components/page_header', array(
    'description' => 'Review security and inventory activity for audit and accountability.'
));

$user_options = array('' => 'All users');

foreach ((array) $audit_users as $user) {
    $display_name = trim((string) $user->first_name . ' ' . (string) $user->last_name);
    $label = $display_name !== ''
        ? $display_name . ' (' . $user->username . ')'
        : $user->username;

    $user_options[(string) $user->id] = $label;
}

$action_options = array('' => 'All actions');

foreach ((array) $audit_actions as $action) {
    $raw_action = trim((string) $action->action);

    if ($raw_action === '') {
        continue;
    }

    $action_options[$raw_action] = ucwords(str_replace('_', ' ', strtolower($raw_action)));
}

$this->load->view('components/data_table', array(
    'source' => site_url('activity-logs/datatable'),
    'table_id' => 'activity-logs-table',
    'search_placeholder' => 'Search user, action, description, IP address, or date...',
    'filters' => array(
        array(
            'name' => 'user',
            'label' => 'User',
            'icon' => 'bi-person',
            'options' => $user_options
        ),
        array(
            'name' => 'action',
            'label' => 'Action',
            'icon' => 'bi-activity',
            'options' => $action_options
        ),
        array(
            'name' => 'period',
            'label' => 'Date range',
            'icon' => 'bi-calendar3',
            'custom_range' => TRUE,
            'options' => array(
                '' => 'All dates',
                'today' => 'Today',
                '7_days' => 'Last 7 days',
                '30_days' => 'Last 30 days',
                'custom' => 'Custom range'
            )
        )
    ),
    'columns' => array(
        array('label' => 'Date & Time', 'class' => 'text-nowrap'),
        'User',
        array('label' => 'Action', 'class' => 'text-nowrap', 'render' => 'activity_action'),
        'Description',
        array('label' => 'IP Address', 'class' => 'text-nowrap')
    )
));
?>
