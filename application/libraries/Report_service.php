<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Report_service {

    private $CI;

    // Setup ni sa Report_service; gi-load ni sa application/controllers/Reports.php para report semantics ug data orchestration naa ra diri.
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model('Report_model');
        $this->CI->load->library('Report_rules');
    }

    // Business rule ni para supported export format; application/controllers/Reports.php ang caller so format policy stays in report service layer.
    public function export_format_is_supported($format) {
        return $this->CI->report_rules->export_format_is_supported($format);
    }

    // Business definition ni para report; application/controllers/Reports.php ang caller, with supported-report validation delegated sa Report_rules.
    public function definition($report) {
        return $this->CI->report_rules->get($report);
    }

    // Business schema ni para report columns; application/controllers/Reports.php ang caller para UI ug exports pareho ug field definition.
    public function columns($report) {
        if ($report === 'inventory' || $report === 'valuation') {
            return array(
                'product_code' => 'Product Code',
                'product_name' => 'Product Name',
                'category_name' => 'Category',
                'supplier_name' => 'Supplier',
                'unit' => 'Unit',
                'stock' => 'Stock',
                'cost_price' => 'Cost Price',
                'inventory_value' => 'Inventory Value'
            );
        }

        if ($report === 'low-stock') {
            return array(
                'product_code' => 'Product Code',
                'product_name' => 'Product Name',
                'category_name' => 'Category',
                'unit' => 'Unit',
                'stock' => 'Stock',
                'reorder_level' => 'Reorder Level',
                'shortage' => 'Shortage'
            );
        }

        return array(
            'transaction_no' => 'Transaction No.',
            'type' => 'Type',
            'product_code' => 'Product Code',
            'product_name' => 'Product Name',
            'quantity' => 'Quantity',
            'cost_price' => 'Cost Price',
            'supplier_name' => 'Supplier',
            'username' => 'Processed By',
            'remarks' => 'Remarks',
            'created_at' => 'Date'
        );
    }

    // Business ordering map ni para report table; application/controllers/Reports.php ang caller, keeping browser column indexes mapped to safe DB columns.
    public function order_columns($report) {
        if ($report === 'inventory' || $report === 'valuation') {
            return array(
                'p.product_code',
                'p.product_name',
                'c.category_name',
                's.supplier_name',
                'p.unit',
                'p.stock',
                'p.cost_price',
                'inventory_value'
            );
        }

        if ($report === 'low-stock') {
            return array(
                'p.product_code',
                'p.product_name',
                'c.category_name',
                'p.unit',
                'p.stock',
                'p.reorder_level',
                'shortage'
            );
        }

        return array(
            't.transaction_no',
            't.type',
            'p.product_code',
            'p.product_name',
            'i.quantity',
            'i.cost_price',
            's.supplier_name',
            'u.username',
            't.remarks',
            't.created_at'
        );
    }

    // Business default order ni para report; application/controllers/Reports.php ang caller para consistent default sorting across table/export UX.
    public function default_order($report) {
        if ($report === 'inventory' || $report === 'valuation') {
            return array('column' => 'p.product_name', 'dir' => 'asc');
        }

        if ($report === 'low-stock') {
            return array('column' => 'p.stock', 'dir' => 'asc');
        }

        return array('column' => 't.created_at', 'dir' => 'desc');
    }

    // Business data flow ni para server-side report table; application/controllers/Reports.php ang caller, while Report_model query-only.
    public function datatable($report, $request) {
        $rows = $this->CI->Report_model->get_datatable(
            $report,
            $request['start'],
            $request['length'],
            $request['search'],
            $request['order_column'],
            $request['order_dir'],
            $request['filters']
        );

        return array(
            'rows' => $rows,
            'total' => $this->CI->Report_model->count_datatable_total($report),
            'filtered' => $this->CI->Report_model->count_datatable_filtered(
                $report,
                $request['search'],
                $request['filters']
            )
        );
    }

    // Business export payload ni para filtered report; application/controllers/Reports.php ang caller, then file rendering remains controller/output concern.
    public function export_payload($report, $search, $filters, $prepared_by) {
        $definition = $this->definition($report);
        $rows = $this->CI->Report_model->get_export_rows($report, $search, $filters);

        return array(
            'definition' => $definition,
            'columns' => $this->columns($report),
            'rows' => $rows,
            'meta' => array(
                'system_name' => 'Inventory Management System',
                'report_key' => $report,
                'report_title' => $definition['title'],
                'generated_at' => date('F j, Y g:i A'),
                'prepared_by' => (string) $prepared_by,
                'record_count' => count($rows),
                'summary' => $this->summary($report, $rows)
            )
        );
    }

    // Internal helper ni para report summary; tawagon ra sulod Report_service para totals ug movement values dili na i-compute sa controller.
    private function summary($report, $rows) {
        $summary = array('Records' => number_format(count($rows)));

        if ($report === 'inventory' || $report === 'valuation') {
            $total_stock = 0;
            $total_value = 0.0;

            foreach ($rows as $row) {
                $total_stock += isset($row['stock']) ? (int) $row['stock'] : 0;
                $total_value += isset($row['inventory_value']) ? (float) $row['inventory_value'] : 0;
            }

            $summary['Total Stock'] = number_format($total_stock);
            $summary['Inventory Value'] = '₱' . number_format($total_value, 2);
            return $summary;
        }

        if ($report === 'low-stock') {
            $shortage = 0;

            foreach ($rows as $row) {
                $shortage += isset($row['shortage']) ? (int) $row['shortage'] : 0;
            }

            $summary['Total Shortage'] = number_format($shortage);
            return $summary;
        }

        $total_quantity = 0;
        $movement_value = 0.0;

        foreach ($rows as $row) {
            $quantity = isset($row['quantity']) ? (int) $row['quantity'] : 0;
            $cost_price = isset($row['cost_price']) ? (float) $row['cost_price'] : 0;
            $total_quantity += $quantity;
            $movement_value += $quantity * $cost_price;
        }

        $summary['Total Quantity'] = number_format($total_quantity);
        $summary['Movement Value'] = '₱' . number_format($movement_value, 2);

        return $summary;
    }
}
