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

        $columns = array(
            'transaction_no' => 'Transaction No.',
            'type' => 'Type',
            'product_code' => 'Product Code',
            'product_name' => 'Product Name',
            'category_name' => 'Category',
            'unit' => 'Unit',
            'quantity' => $report === 'movement' ? 'Net Quantity' : 'Quantity',
            'cost_price' => 'Cost Price',
            'supplier_name' => 'Supplier',
            'username' => 'Processed By',
            'remarks' => 'Remarks',
            'created_at' => 'Date'
        );

        if ($report === 'movement') {
            $columns['system_stock'] = 'System Stock';
            $columns['actual_stock'] = 'Actual Stock';
            $columns['difference'] = 'Difference';
        }

        return $columns;
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

        $columns = array(
            't.transaction_no',
            't.type',
            'i.product_code_snapshot',
            'i.product_name_snapshot',
            'i.category_name_snapshot',
            'i.unit_snapshot',
            'i.quantity',
            'i.cost_price',
            's.supplier_name',
            'u.username',
            't.remarks',
            't.created_at'
        );

        if ($report === 'movement') {
            $columns[6] = 'quantity';
            $columns[] = 'a.system_stock';
            $columns[] = 'a.actual_stock';
            $columns[] = 'a.difference';
        }

        return $columns;
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
                'date_range' => $this->date_range_label($filters),
                'record_count' => count($rows),
                'summary' => $this->summary($report, $rows)
            )
        );
    }

    public function export_metadata($report, $search, $filters, $prepared_by, $chunk_size = 500) {
        $definition = $this->definition($report);
        $row_count = $this->CI->Report_model->count_export_rows($report, $search, $filters);
        $summary = array('Records' => number_format($row_count));

        if ($report === 'inventory' || $report === 'valuation') {
            $summary['Total Stock'] = 0;
            $summary['Inventory Value'] = 0.0;
        } elseif ($report === 'low-stock') {
            $summary['Total Shortage'] = 0;
        } else {
            $summary['Total Quantity'] = 0;
            $summary['Movement Value'] = 0.0;
        }

        $cursor = NULL;

        do {
            $rows = $this->export_rows_chunk($report, $search, $filters, $cursor, $chunk_size);

            foreach ($rows as $row) {
                if ($report === 'inventory' || $report === 'valuation') {
                    $summary['Total Stock'] += isset($row['stock']) ? (int) $row['stock'] : 0;
                    $summary['Inventory Value'] += isset($row['inventory_value']) ? (float) $row['inventory_value'] : 0.0;
                } elseif ($report === 'low-stock') {
                    $summary['Total Shortage'] += isset($row['shortage']) ? (int) $row['shortage'] : 0;
                } else {
                    $quantity = isset($row['quantity']) ? (int) $row['quantity'] : 0;
                    $cost_price = isset($row['cost_price']) ? (float) $row['cost_price'] : 0.0;
                    $summary['Total Quantity'] += $quantity;
                    $summary['Movement Value'] += $quantity * $cost_price;
                }

                $cursor = $this->next_export_cursor($report, $row);
            }
        } while (count($rows) === max(1, min(1000, (int) $chunk_size)));

        if ($report === 'inventory' || $report === 'valuation') {
            $summary['Total Stock'] = number_format($summary['Total Stock']);
            $summary['Inventory Value'] = '₱' . number_format($summary['Inventory Value'], 2);
        } elseif ($report === 'low-stock') {
            $summary['Total Shortage'] = number_format($summary['Total Shortage']);
        } else {
            $summary['Total Quantity'] = number_format($summary['Total Quantity']);
            $summary['Movement Value'] = '₱' . number_format($summary['Movement Value'], 2);
        }

        return array(
            'definition' => $definition,
            'columns' => $this->columns($report),
            'meta' => array(
                'system_name' => 'Inventory Management System',
                'report_key' => $report,
                'report_title' => $definition['title'],
                'generated_at' => date('F j, Y g:i A'),
                'prepared_by' => (string) $prepared_by,
                'date_range' => $this->date_range_label($filters),
                'record_count' => $row_count,
                'summary' => $summary
            )
        );
    }

    public function export_rows_chunk($report, $search, $filters, $cursor, $limit = 500) {
        return $this->CI->Report_model->get_export_rows_chunk(
            $report,
            $search,
            $filters,
            $cursor,
            max(1, min(1000, (int) $limit))
        );
    }

    public function export_row_count($report, $search, $filters) {
        return $this->CI->Report_model->count_export_rows($report, $search, $filters);
    }

    public function next_export_cursor($report, $row) {
        if ($report === 'inventory' || $report === 'valuation') {
            return array('name' => (string) $row['__export_name'], 'id' => (int) $row['__export_cursor_id']);
        }

        if ($report === 'low-stock') {
            return array(
                'stock' => (int) $row['__export_stock'],
                'name' => (string) $row['__export_name'],
                'id' => (int) $row['__export_cursor_id']
            );
        }

        return array(
            'created_at' => (string) $row['__export_created_at'],
            'transaction_id' => (int) $row['__export_transaction_id'],
            'item_id' => (int) $row['__export_item_id']
        );
    }

    // Business presentation helper ni para show the active report range in CSV/XLSX/PDF metadata.
    private function date_range_label($filters) {
        $period = isset($filters['period']) ? strtolower(trim((string) $filters['period'])) : '';

        if ($period === 'today') {
            return 'Today';
        }

        if ($period === '7_days') {
            return 'Last 7 days';
        }

        if ($period === '30_days') {
            return 'Last 30 days';
        }

        if ($period === 'custom') {
            $from = isset($filters['date_from']) ? trim((string) $filters['date_from']) : '';
            $to = isset($filters['date_to']) ? trim((string) $filters['date_to']) : '';
            $from_date = DateTime::createFromFormat('!Y-m-d', $from);
            $to_date = DateTime::createFromFormat('!Y-m-d', $to);

            if (
                $from_date !== FALSE &&
                $to_date !== FALSE &&
                $from_date->format('Y-m-d') === $from &&
                $to_date->format('Y-m-d') === $to &&
                $from <= $to
            ) {
                return $from_date->format('M j, Y') . ' - ' . $to_date->format('M j, Y');
            }
        }

        return 'All dates';
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

        if ($report === 'movement') {
            $net_quantity = 0;
            $net_movement_value = 0.0;

            foreach ($rows as $row) {
                $quantity = isset($row['quantity']) ? (int) $row['quantity'] : 0;
                $cost_price = isset($row['cost_price']) ? (float) $row['cost_price'] : 0.0;
                $net_quantity += $quantity;
                $net_movement_value += $quantity * $cost_price;
            }

            $summary['Net Stock Change'] = number_format($net_quantity);
            $summary['Net Movement Value'] = '₱' . number_format($net_movement_value, 2);
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
