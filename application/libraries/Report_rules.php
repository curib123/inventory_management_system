<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Report_rules {

    private $definitions = array(
        'inventory' => array('title' => 'Inventory Report', 'type' => NULL),
        'stock-in' => array('title' => 'Stock-In Report', 'type' => 'stock_in'),
        'stock-out' => array('title' => 'Stock-Out Report', 'type' => 'stock_out'),
        'movement' => array('title' => 'Stock Movement Report', 'type' => NULL),
        'low-stock' => array('title' => 'Low-Stock Report', 'type' => NULL),
        'valuation' => array('title' => 'Inventory Valuation', 'type' => NULL)
    );

    public function get($report) {
        if (!isset($this->definitions[$report])) {
            throw new InvalidArgumentException('Unknown report.');
        }

        return $this->definitions[$report];
    }

    public function export_format_is_supported($format) {
        return in_array(strtolower($format), array('csv', 'xlsx', 'pdf'), TRUE);
    }
}
