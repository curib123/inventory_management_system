<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Stock_model extends CI_Model {

    // Setup ni sa Stock_model; CodeIgniter mo-run ani when gi-load ang model, with main integration sa application/controllers/Stock.php, application/controllers/Dashboard.php, ug application/controllers/Reports.php.
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // Persistence helper ni para insert transaction header; application/libraries/Stock_service.php ang caller, business rules didto tanan.
    public function insert_transaction($data) {
        if (!$this->db->insert('stock_transactions', $data)) {
            return 0;
        }

        return (int) $this->db->insert_id();
    }

    // Persistence helper ni para insert transaction item; application/libraries/Stock_service.php ang caller after service validation.
    public function insert_transaction_item($data) {
        return $this->db->insert('stock_transaction_items', $data);
    }

    // Persistence helper ni para insert adjustment audit row; application/libraries/Stock_service.php ang caller after reconciliation rules pass.
    public function insert_adjustment($data) {
        return $this->db->insert('stock_adjustments', $data);
    }

    // Persistence helper ni para update product stock; application/libraries/Stock_service.php ang caller after Stock_rules calculation.
    public function update_product_stock($product_id, $stock) {
        return $this->db->update(
            'products',
            array('stock' => (int) $stock),
            array('id' => (int) $product_id)
        );
    }

    // Persistence helper ni para lock product row; application/libraries/Stock_service.php ang caller inside DB transaction para safe concurrent stock update.
    public function get_product_for_update($product_id) {
        return $this->db
            ->query('SELECT * FROM products WHERE id = ? FOR UPDATE', array((int) $product_id))
            ->row();
    }

    // Persistence helper ni para activity log; application/libraries/Stock_service.php ang caller after successful stock action.
    public function insert_activity_log($data) {
        return $this->db->insert('activity_logs', $data);
    }

    // Data helper ni para get transactions; main caller/integration pangitaa sa application/controllers/Stock.php, application/controllers/Dashboard.php, ug application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_transactions($limit = NULL, $offset = 0) {
        $this->db->select('t.*, u.username, s.supplier_name');
        $this->db->from('stock_transactions t');
        $this->db->join('users u', 'u.id = t.created_by');
        $this->db->join('suppliers s', 's.id = t.supplier_id', 'left');
        $this->db->order_by('t.created_at', 'DESC');
        $this->db->order_by('t.id', 'DESC');
        if ($limit !== NULL) {
            $this->db->limit((int) $limit, (int) $offset);
        }
        return $this->db->get()->result();
    }

    // Data helper ni para count transactions; main caller/integration pangitaa sa application/controllers/Stock.php, application/controllers/Dashboard.php, ug application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    public function count_transactions() {
        return $this->db->count_all('stock_transactions');
    }

    // Data helper ni para get transaction; main caller/integration pangitaa sa application/controllers/Stock.php, application/controllers/Dashboard.php, ug application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_transaction($id) {
        $this->db->select('t.*, u.username, s.supplier_name');
        $this->db->from('stock_transactions t');
        $this->db->join('users u', 'u.id = t.created_by');
        $this->db->join('suppliers s', 's.id = t.supplier_id', 'left');
        $this->db->where('t.id', (int) $id);
        return $this->db->get()->row();
    }

    // Data helper ni para get transaction items; main caller/integration pangitaa sa application/controllers/Stock.php, application/controllers/Dashboard.php, ug application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_transaction_items($transaction_id) {
        $this->db->select('i.*, p.product_code, p.product_name, p.unit');
        $this->db->from('stock_transaction_items i');
        $this->db->join('products p', 'p.id = i.product_id');
        $this->db->where('i.transaction_id', (int) $transaction_id);
        return $this->db->get()->result();
    }

    // Data helper ni para get adjustments; main caller/integration pangitaa sa application/controllers/Stock.php, application/controllers/Dashboard.php, ug application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_adjustments($limit = NULL, $offset = 0) {
        $this->db->select('a.*, p.product_name, p.product_code, u.username');
        $this->db->from('stock_adjustments a');
        $this->db->join('products p', 'p.id = a.product_id');
        $this->db->join('users u', 'u.id = a.created_by');
        $this->db->order_by('a.created_at', 'DESC');
        $this->db->order_by('a.id', 'DESC');
        if ($limit !== NULL) {
            $this->db->limit((int) $limit, (int) $offset);
        }
        return $this->db->get()->result();
    }

    // Data helper ni para count adjustments; main caller/integration pangitaa sa application/controllers/Stock.php, application/controllers/Dashboard.php, ug application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    public function count_adjustments() {
        return $this->db->count_all('stock_adjustments');
    }

    // Data helper ni para get low stock products; main caller/integration pangitaa sa application/controllers/Stock.php, application/controllers/Dashboard.php, ug application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_low_stock_products() {
        $this->db->where('stock <= reorder_level', NULL, FALSE);
        $this->db->where('status', 1);
        $this->db->order_by('stock', 'ASC');
        return $this->db->get('products')->result();
    }

    // Data helper ni para count low stock products; main caller/integration pangitaa sa application/controllers/Stock.php, application/controllers/Dashboard.php, ug application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    public function count_low_stock_products() {
        $this->db->where('stock <= reorder_level', NULL, FALSE);
        $this->db->where('status', 1);
        return $this->db->count_all_results('products');
    }

    // Data helper ni para get today quantity; main caller/integration pangitaa sa application/controllers/Stock.php, application/controllers/Dashboard.php, ug application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_today_quantity($type) {
        $this->db->select_sum('i.quantity', 'total_quantity');
        $this->db->from('stock_transaction_items i');
        $this->db->join('stock_transactions t', 't.id = i.transaction_id');
        $this->db->where('t.type', $type);
        $this->db->where('t.created_at >=', date('Y-m-d 00:00:00'));
        $this->db->where('t.created_at <=', date('Y-m-d 23:59:59'));
        $row = $this->db->get()->row();
        return $row && $row->total_quantity ? (int) $row->total_quantity : 0;
    }

    // Data helper ni para get inventory value; main caller/integration pangitaa sa application/controllers/Stock.php, application/controllers/Dashboard.php, ug application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_inventory_value() {
        $this->db->select('COALESCE(SUM(stock * cost_price), 0) AS inventory_value', FALSE);
        $row = $this->db->get('products')->row();
        return $row ? (float) $row->inventory_value : 0;
    }

    // Data helper ni para get stock health summary; main caller/integration pangitaa sa application/controllers/Stock.php, application/controllers/Dashboard.php, ug application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_stock_health_summary() {
        $this->db->select(
            'SUM(CASE WHEN status = 1 AND stock > reorder_level THEN 1 ELSE 0 END) AS healthy, ' .
            'SUM(CASE WHEN status = 1 AND stock > 0 AND stock <= reorder_level THEN 1 ELSE 0 END) AS low_stock, ' .
            'SUM(CASE WHEN status = 1 AND stock <= 0 THEN 1 ELSE 0 END) AS out_of_stock',
            FALSE
        );

        $row = $this->db->get('products')->row();

        return array(
            'healthy' => $row ? (int) $row->healthy : 0,
            'low_stock' => $row ? (int) $row->low_stock : 0,
            'out_of_stock' => $row ? (int) $row->out_of_stock : 0
        );
    }

    // Data helper ni para get stock by category; main caller/integration pangitaa sa application/controllers/Stock.php, application/controllers/Dashboard.php, ug application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_stock_by_category() {
        $this->db->select('c.category_name, COUNT(p.id) AS product_count, COALESCE(SUM(p.stock), 0) AS total_stock');
        $this->db->from('categories c');
        $this->db->join('products p', 'p.category_id = c.id AND p.status = 1', 'left');
        $this->db->where('c.status', 1);
        $this->db->group_by(array('c.id', 'c.category_name'));
        $this->db->order_by('c.category_name', 'ASC');
        return $this->db->get()->result();
    }

    // Data helper ni para get monthly movement summary; main caller/integration pangitaa sa application/controllers/Stock.php, application/controllers/Dashboard.php, ug application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_monthly_movement_summary() {
        $this->db->select("DATE_FORMAT(t.created_at, '%Y-%m') AS month, SUM(CASE WHEN t.type = 'stock_in' THEN i.quantity ELSE 0 END) AS stock_in, SUM(CASE WHEN t.type = 'stock_out' THEN i.quantity ELSE 0 END) AS stock_out", FALSE);
        $this->db->from('stock_transactions t');
        $this->db->join('stock_transaction_items i', 'i.transaction_id = t.id');
        $this->db->where("t.created_at >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 11 MONTH), '%Y-%m-01')", NULL, FALSE);
        $this->db->where_in('t.type', array('stock_in', 'stock_out'));
        $this->db->group_by("DATE_FORMAT(t.created_at, '%Y-%m')", FALSE);
        $this->db->order_by('month', 'ASC');
        return $this->db->get()->result();
    }

    // Data helper ni para get transactions datatable; main caller/integration pangitaa sa application/controllers/Stock.php, application/controllers/Dashboard.php, ug application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_transactions_datatable($start, $length, $search, $order_column, $order_dir, $filters = array()) {
        $this->build_transactions_datatable_query($search, $filters);
        $this->db->select('t.id, t.transaction_no, t.type, t.created_at, u.username, s.supplier_name');
        if ($order_column) {
            $this->db->order_by($order_column, $order_dir);
        }
        $this->db->order_by('t.id', 'DESC');
        $this->db->limit((int) $length, (int) $start);
        return $this->db->get()->result();
    }

    // Data helper ni para count transactions filtered; main caller/integration pangitaa sa application/controllers/Stock.php, application/controllers/Dashboard.php, ug application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    public function count_transactions_filtered($search, $filters = array()) {
        $this->build_transactions_datatable_query($search, $filters);
        return $this->db->count_all_results();
    }

    // Data helper ni para get adjustments datatable; main caller/integration pangitaa sa application/controllers/Stock.php, application/controllers/Dashboard.php, ug application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_adjustments_datatable($start, $length, $search, $order_column, $order_dir, $filters = array()) {
        $this->build_adjustments_datatable_query($search, $filters);
        $this->db->select('a.id, a.system_stock, a.actual_stock, a.difference, a.reason, a.created_at, p.product_code, p.product_name, u.username');
        if ($order_column) {
            $this->db->order_by($order_column, $order_dir);
        }
        $this->db->order_by('a.id', 'DESC');
        $this->db->limit((int) $length, (int) $start);
        return $this->db->get()->result();
    }

    // Data helper ni para count adjustments filtered; main caller/integration pangitaa sa application/controllers/Stock.php, application/controllers/Dashboard.php, ug application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    public function count_adjustments_filtered($search, $filters = array()) {
        $this->build_adjustments_datatable_query($search, $filters);
        return $this->db->count_all_results();
    }

    // Data helper ni para get low stock datatable; main caller/integration pangitaa sa application/controllers/Stock.php, application/controllers/Dashboard.php, ug application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_low_stock_datatable($start, $length, $search, $order_column, $order_dir, $filters = array()) {
        $this->build_low_stock_datatable_query($search, $filters);
        $this->db->select('p.product_code, p.product_name, p.stock, p.reorder_level, p.unit');
        if ($order_column) {
            $this->db->order_by($order_column, $order_dir);
        }
        $this->db->limit((int) $length, (int) $start);
        return $this->db->get()->result();
    }

    // Data helper ni para count low stock filtered; main caller/integration pangitaa sa application/controllers/Stock.php, application/controllers/Dashboard.php, ug application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    public function count_low_stock_filtered($search, $filters = array()) {
        $this->build_low_stock_datatable_query($search, $filters);
        return $this->db->count_all_results();
    }

    // Data helper ni para build transactions datatable query; main caller/integration pangitaa sa application/controllers/Stock.php, application/controllers/Dashboard.php, ug application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    private function build_transactions_datatable_query($search, $filters = array()) {
        $this->db->from('stock_transactions t');
        $this->db->join('users u', 'u.id = t.created_by');
        $this->db->join('suppliers s', 's.id = t.supplier_id', 'left');

        $type = isset($filters['type']) ? strtolower((string) $filters['type']) : '';
        if (in_array($type, array('stock_in', 'stock_out', 'adjustment'), TRUE)) {
            $this->db->where('t.type', $type);
        }

        $this->apply_period_filter('t.created_at', $filters);

        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('t.transaction_no', $search);
            $this->db->or_like('t.type', $search);
            $this->db->or_like('s.supplier_name', $search);
            $this->db->or_like('u.username', $search);
            $this->db->or_like('t.created_at', $search);
            $this->db->or_like('t.remarks', $search);
            $this->db->group_end();
        }
    }

    // Data helper ni para build adjustments datatable query; main caller/integration pangitaa sa application/controllers/Stock.php, application/controllers/Dashboard.php, ug application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    private function build_adjustments_datatable_query($search, $filters = array()) {
        $this->db->from('stock_adjustments a');
        $this->db->join('products p', 'p.id = a.product_id');
        $this->db->join('users u', 'u.id = a.created_by');

        $difference = isset($filters['difference']) ? strtolower((string) $filters['difference']) : '';
        if ($difference === 'increase') {
            $this->db->where('a.difference >', 0);
        } elseif ($difference === 'decrease') {
            $this->db->where('a.difference <', 0);
        }

        $this->apply_period_filter('a.created_at', $filters);

        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('p.product_code', $search);
            $this->db->or_like('p.product_name', $search);
            $this->db->or_like('a.reason', $search);
            $this->db->or_like('u.username', $search);
            $this->db->or_like('a.created_at', $search);
            $this->db->group_end();
        }
    }

    // Data helper ni para apply period filter; main caller/integration pangitaa sa application/controllers/Stock.php, application/controllers/Dashboard.php, ug application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    private function apply_period_filter($column, $filters) {
        $period = isset($filters['period']) ? strtolower((string) $filters['period']) : '';

        if ($period === 'today') {
            $this->db->where($column . ' >=', date('Y-m-d 00:00:00'));
            $this->db->where($column . ' <=', date('Y-m-d 23:59:59'));
        } elseif ($period === '7_days') {
            $this->db->where($column . ' >=', date('Y-m-d 00:00:00', strtotime('-6 days')));
        } elseif ($period === '30_days') {
            $this->db->where($column . ' >=', date('Y-m-d 00:00:00', strtotime('-29 days')));
        }
    }

    // Data helper ni para build low stock datatable query; main caller/integration pangitaa sa application/controllers/Stock.php, application/controllers/Dashboard.php, ug application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    private function build_low_stock_datatable_query($search, $filters = array()) {
        $this->db->from('products p');
        $this->db->where('p.stock <= p.reorder_level', NULL, FALSE);
        $this->db->where('p.status', 1);

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
            $this->db->or_like('p.unit', $search);
            $this->db->group_end();
        }
    }
}
