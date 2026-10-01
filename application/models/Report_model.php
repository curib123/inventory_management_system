<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Report_model extends CI_Model {
 
    // Setup ni sa Report_model; CodeIgniter mo-run ani when gi-load ang model, with main integration sa application/controllers/Reports.php.
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    // Data helper ni para get inventory report; main caller/integration pangitaa sa application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_inventory_report() {
        $this->db->select("p.product_code, p.product_name, c.category_name, COALESCE(s.supplier_name, 'Unassigned Products') AS supplier_name, p.unit, p.stock, p.cost_price, (p.stock * p.cost_price) AS inventory_value", FALSE);
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->join('suppliers s', 's.id = p.supplier_id', 'left');
        $this->db->order_by('p.product_name', 'ASC');
        return $this->db->get()->result_array();
    }
    // Data helper ni para get stock movement report; main caller/integration pangitaa sa application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_stock_movement_report($type = NULL) {
        $quantity = $type === NULL
            ? "CASE WHEN t.type = 'stock_in' THEN i.quantity WHEN t.type = 'stock_out' THEN -i.quantity WHEN t.type = 'adjustment' THEN COALESCE(a.difference, i.quantity) ELSE i.quantity END"
            : 'i.quantity';
        $this->db->select("t.transaction_no, t.type, i.product_code_snapshot AS product_code, i.product_name_snapshot AS product_name, i.category_name_snapshot AS category_name, i.unit_snapshot AS unit, " . $quantity . " AS quantity, i.cost_price, COALESCE(s.supplier_name, 'Unassigned Products') AS supplier_name, u.username, t.remarks, t.created_at, a.system_stock, a.actual_stock, a.difference", FALSE);
        $this->db->from('stock_transactions t');
        $this->db->join('stock_transaction_items i', 'i.transaction_id = t.id');
        $this->db->join('stock_adjustments a', 'a.transaction_id = t.id AND a.product_id = i.product_id', 'left');
        $this->db->join('suppliers s', 's.id = t.supplier_id', 'left');
        $this->db->join('users u', 'u.id = t.created_by');
        if ($type) {
            $this->db->where('t.type', $type);
        }
        $this->db->order_by('t.created_at', 'DESC');
        return $this->db->get()->result_array();
    }
    // Data helper ni para get low stock report; main caller/integration pangitaa sa application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_low_stock_report() {
        $this->db->select('p.product_code, p.product_name, c.category_name, p.unit, p.stock, p.reorder_level, (p.reorder_level - p.stock) AS shortage');
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->where('p.stock <= p.reorder_level', NULL, FALSE);
        $this->db->where('p.status', 1);
        $this->db->order_by('p.stock', 'ASC');
        return $this->db->get()->result_array();
    }

    // Data helper ni para get datatable; main caller/integration pangitaa sa application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_datatable($report, $start, $length, $search, $order_column, $order_dir, $filters = array()) {
        $this->build_datatable_query($report, $search, $filters);
        $this->select_report_columns($report);
        if ($order_column) {
            $this->db->order_by($order_column, $order_dir);
        }
        $this->db->limit((int) $length, (int) $start);
        return $this->db->get()->result_array();
    }

    // Data helper ni para get export rows; main caller/integration pangitaa sa application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_export_rows($report, $search = '', $filters = array()) {
        $this->build_datatable_query($report, trim((string) $search), (array) $filters);
        $this->select_report_columns($report);

        if ($report === 'inventory' || $report === 'valuation') {
            $this->db->order_by('p.product_name', 'ASC');
        } elseif ($report === 'low-stock') {
            $this->db->order_by('p.stock', 'ASC');
            $this->db->order_by('p.product_name', 'ASC');
        } else {
            $this->db->order_by('t.created_at', 'DESC');
            $this->db->order_by('t.id', 'DESC');
        }

        return $this->db->get()->result_array();
    }

    public function count_export_rows($report, $search = '', $filters = array()) {
        $this->build_datatable_query($report, trim((string) $search), (array) $filters);
        return (int) $this->db->count_all_results();
    }

    public function get_export_rows_chunk($report, $search, $filters, $cursor, $limit) {
        $this->build_datatable_query($report, trim((string) $search), (array) $filters);

        if ($report === 'inventory' || $report === 'valuation') {
            if (is_array($cursor)) {
                $this->db->group_start();
                $this->db->where('p.product_name >', $cursor['name']);
                $this->db->or_group_start();
                $this->db->where('p.product_name', $cursor['name']);
                $this->db->where('p.id >', (int) $cursor['id']);
                $this->db->group_end();
                $this->db->group_end();
            }
        } elseif ($report === 'low-stock') {
            if (is_array($cursor)) {
                $this->db->group_start();
                $this->db->where('p.stock >', (int) $cursor['stock']);
                $this->db->or_group_start();
                $this->db->where('p.stock', (int) $cursor['stock']);
                $this->db->where('p.product_name >', $cursor['name']);
                $this->db->group_end();
                $this->db->or_group_start();
                $this->db->where('p.stock', (int) $cursor['stock']);
                $this->db->where('p.product_name', $cursor['name']);
                $this->db->where('p.id >', (int) $cursor['id']);
                $this->db->group_end();
                $this->db->group_end();
            }
        } elseif (is_array($cursor)) {
            $this->db->group_start();
            $this->db->where('t.created_at <', $cursor['created_at']);
            $this->db->or_group_start();
            $this->db->where('t.created_at', $cursor['created_at']);
            $this->db->where('t.id <', (int) $cursor['transaction_id']);
            $this->db->group_end();
            $this->db->or_group_start();
            $this->db->where('t.created_at', $cursor['created_at']);
            $this->db->where('t.id', (int) $cursor['transaction_id']);
            $this->db->where('i.id >', (int) $cursor['item_id']);
            $this->db->group_end();
            $this->db->group_end();
        }

        $this->select_report_columns($report);
        if ($report === 'inventory' || $report === 'valuation') {
            $this->db->select('p.product_name AS __export_name, p.id AS __export_cursor_id', FALSE);
            $this->db->order_by('p.product_name', 'ASC');
            $this->db->order_by('p.id', 'ASC');
        } elseif ($report === 'low-stock') {
            $this->db->select('p.stock AS __export_stock, p.product_name AS __export_name, p.id AS __export_cursor_id', FALSE);
            $this->db->order_by('p.stock', 'ASC');
            $this->db->order_by('p.product_name', 'ASC');
            $this->db->order_by('p.id', 'ASC');
        } else {
            $this->db->select('t.created_at AS __export_created_at, t.id AS __export_transaction_id, i.id AS __export_item_id', FALSE);
            $this->db->order_by('t.created_at', 'DESC');
            $this->db->order_by('t.id', 'DESC');
            $this->db->order_by('i.id', 'ASC');
        }

        $this->db->limit(max(1, min(1000, (int) $limit)));

        return $this->db->get()->result_array();
    }

    // Data helper ni para count datatable total; main caller/integration pangitaa sa application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    public function count_datatable_total($report) {
        $this->build_datatable_query($report, '');
        return $this->db->count_all_results();
    }

    // Data helper ni para count datatable filtered; main caller/integration pangitaa sa application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    public function count_datatable_filtered($report, $search, $filters = array()) {
        $this->build_datatable_query($report, $search, $filters);
        return $this->db->count_all_results();
    }

    // Data helper ni para select report columns; main caller/integration pangitaa sa application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    private function select_report_columns($report) {
        if ($report === 'inventory' || $report === 'valuation') {
            $this->db->select("p.product_code, p.product_name, c.category_name, COALESCE(s.supplier_name, 'Unassigned Products') AS supplier_name, p.unit, p.stock, p.cost_price, (p.stock * p.cost_price) AS inventory_value", FALSE);
            return;
        }

        if ($report === 'low-stock') {
            $this->db->select('p.product_code, p.product_name, c.category_name, p.unit, p.stock, p.reorder_level, (p.reorder_level - p.stock) AS shortage', FALSE);
            return;
        }

        $quantity = $report === 'movement'
            ? "CASE WHEN t.type = 'stock_in' THEN i.quantity WHEN t.type = 'stock_out' THEN -i.quantity WHEN t.type = 'adjustment' THEN COALESCE(a.difference, i.quantity) ELSE i.quantity END"
            : 'i.quantity';
        $columns = "t.transaction_no, t.type, i.product_code_snapshot AS product_code, i.product_name_snapshot AS product_name, i.category_name_snapshot AS category_name, i.unit_snapshot AS unit, " . $quantity . " AS quantity, i.cost_price, COALESCE(s.supplier_name, 'Unassigned Products') AS supplier_name, u.username, t.remarks, t.created_at";
        if ($report === 'movement') {
            $columns .= ', a.system_stock, a.actual_stock, a.difference';
        }
        $this->db->select($columns, FALSE);
    }

    // Data helper ni para build datatable query; main caller/integration pangitaa sa application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    private function build_datatable_query($report, $search, $filters = array()) {
        if ($report === 'inventory' || $report === 'valuation') {
            $this->db->from('products p');
            $this->db->join('categories c', 'c.id = p.category_id', 'left');
            $this->db->join('suppliers s', 's.id = p.supplier_id', 'left');

            $this->apply_product_reference_filters('p', 'p.supplier_id', $filters);

            $stock = isset($filters['stock']) ? strtolower((string) $filters['stock']) : '';
            if ($stock === 'out') {
                $this->db->where('p.stock <=', 0);
            } elseif ($stock === 'low') {
                $this->db->where('p.stock >', 0);
                $this->db->where('p.stock <= p.reorder_level', NULL, FALSE);
            } elseif ($stock === 'healthy') {
                $this->db->where('p.stock > p.reorder_level', NULL, FALSE);
            }

            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('p.product_code', $search);
                $this->db->or_like('p.product_name', $search);
                $this->db->or_like('c.category_name', $search);
                $this->db->or_like('s.supplier_name', $search);

                if (stripos('Unassigned Products', $search) !== FALSE) {
                    $this->db->or_where('p.supplier_id IS NULL', NULL, FALSE);
                }

                $this->db->or_like('p.unit', $search);
                $this->db->group_end();
            }
            return;
        }

        if ($report === 'low-stock') {
            $this->db->from('products p');
            $this->db->join('categories c', 'c.id = p.category_id', 'left');
            $this->db->join('suppliers s', 's.id = p.supplier_id', 'left');
            $this->db->where('p.stock <= p.reorder_level', NULL, FALSE);
            $this->db->where('p.status', 1);

            $this->apply_product_reference_filters('p', 'p.supplier_id', $filters);

            $severity = isset($filters['severity']) ? strtolower((string) $filters['severity']) : '';
            if ($severity === 'out') {
                $this->db->where('p.stock <=', 0);
            } elseif ($severity === 'low') {
                $this->db->where('p.stock >', 0);
            }

            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('p.product_code', $search);
                $this->db->or_like('p.product_name', $search);
                $this->db->or_like('c.category_name', $search);
                $this->db->or_like('p.unit', $search);
                $this->db->group_end();
            }
            return;
        }

        $this->db->from('stock_transactions t');
        $this->db->join('stock_transaction_items i', 'i.transaction_id = t.id');
        $this->db->join('stock_adjustments a', 'a.transaction_id = t.id AND a.product_id = i.product_id', 'left');
        $this->db->join('suppliers s', 's.id = t.supplier_id', 'left');
        $this->db->join('users u', 'u.id = t.created_by');

        if ($report === 'stock-in') {
            $this->db->where('t.type', 'stock_in');
        } elseif ($report === 'stock-out') {
            $this->db->where('t.type', 'stock_out');
        } elseif ($report === 'movement') {
            $type = isset($filters['type']) ? strtolower((string) $filters['type']) : '';

            if (in_array($type, array('stock_in', 'stock_out', 'adjustment'), TRUE)) {
                $this->db->where('t.type', $type);
            }
        }

        $this->apply_report_date_filter('t.created_at', $filters);
        $this->apply_product_reference_filters('i', 't.supplier_id', $filters, 'i.category_id_snapshot');

        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('t.transaction_no', $search);
            $this->db->or_like('t.type', $search);
            $this->db->or_like('i.product_code_snapshot', $search);
            $this->db->or_like('i.product_name_snapshot', $search);
            $this->db->or_like('i.category_name_snapshot', $search);
            $this->db->or_like('i.unit_snapshot', $search);
            $this->db->or_like('s.supplier_name', $search);

            if (stripos('Unassigned Products', $search) !== FALSE) {
                $this->db->or_where('t.supplier_id IS NULL', NULL, FALSE);
            }

            $this->db->or_like('u.username', $search);
            $this->db->or_like('t.remarks', $search);
            $this->db->or_like('t.created_at', $search);
            $this->db->group_end();
        }
    }

    // Query helper ni para shared category/supplier filters across snapshot and transaction reports.
    private function apply_product_reference_filters($product_alias, $supplier_column, $filters, $category_column = NULL) {
        $category_id = isset($filters['category']) ? (int) $filters['category'] : 0;

        if ($category_id > 0) {
            $this->db->where($category_column ?: $product_alias . '.category_id', $category_id);
        }

        $supplier = isset($filters['supplier'])
            ? strtolower(trim((string) $filters['supplier']))
            : '';

        if ($supplier === 'unassigned') {
            $this->db->where($supplier_column . ' IS NULL', NULL, FALSE);
        } elseif ($supplier !== '' && ctype_digit($supplier) && (int) $supplier > 0) {
            $this->db->where($supplier_column, (int) $supplier);
        }
    }

    // Query helper ni para consistent date presets ug custom ranges across every report type.
    private function apply_report_date_filter($column, $filters) {
        $period = isset($filters['period']) ? strtolower(trim((string) $filters['period'])) : '';

        if ($period === 'today') {
            $this->db->where($column . ' >=', date('Y-m-d 00:00:00'));
            $this->db->where($column . ' <=', date('Y-m-d 23:59:59'));
            return;
        }

        if ($period === '7_days') {
            $this->db->where($column . ' >=', date('Y-m-d 00:00:00', strtotime('-6 days')));
            return;
        }

        if ($period === '30_days') {
            $this->db->where($column . ' >=', date('Y-m-d 00:00:00', strtotime('-29 days')));
            return;
        }

        if ($period !== 'custom') {
            return;
        }

        $from = $this->normalize_report_date(isset($filters['date_from']) ? $filters['date_from'] : '');
        $to = $this->normalize_report_date(isset($filters['date_to']) ? $filters['date_to'] : '');

        if ($from === NULL || $to === NULL || $from > $to) {
            return;
        }

        $this->db->where($column . ' >=', $from . ' 00:00:00');
        $this->db->where($column . ' <=', $to . ' 23:59:59');
    }

    // Query helper ni para reject malformed dates before they reach SQL conditions.
    private function normalize_report_date($value) {
        $value = trim((string) $value);

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return NULL;
        }

        $date = DateTime::createFromFormat('!Y-m-d', $value);
        $errors = DateTime::getLastErrors();

        if ($date === FALSE || ($errors !== FALSE && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            return NULL;
        }

        return $date->format('Y-m-d');
    }
}
