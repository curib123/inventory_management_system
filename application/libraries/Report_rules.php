<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Report_rules {

    private $definitions = array(
        'inventory' => array('title' => 'Inventory Report', 'type' => NULL, 'method' => 'get_inventory_report'),
        'stock-in' => array('title' => 'Stock-In Report', 'type' => 'stock_in', 'method' => 'get_stock_movement_report'),
        'stock-out' => array('title' => 'Stock-Out Report', 'type' => 'stock_out', 'method' => 'get_stock_movement_report'),
        'movement' => array('title' => 'Stock Movement Report', 'type' => NULL, 'method' => 'get_stock_movement_report'),
        'low-stock' => array('title' => 'Low-Stock Report', 'type' => NULL, 'method' => 'get_low_stock_report'),
        'valuation' => array('title' => 'Inventory Valuation', 'type' => NULL, 'method' => 'get_inventory_report')
    );

    // Shared service ni para get; main caller/integration pangitaa sa application/controllers/Reports.php, so didto tan-awa if mangita ka asa ni gigamit.
    public function get($report) {
        if (!isset($this->definitions[$report])) {
            throw new InvalidArgumentException('Unknown report.');
        }

        return $this->definitions[$report];
    }

    // Shared service ni para export format is supported; main caller/integration pangitaa sa application/controllers/Reports.php, so didto tan-awa if mangita ka asa ni gigamit.
    public function export_format_is_supported($format) {
        return in_array(strtolower($format), array('csv', 'xlsx', 'pdf'), TRUE);
    }
}
