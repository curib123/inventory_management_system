<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Stock_model extends CI_Model {

    // Setup ni sa Stock_model; CodeIgniter mo-run ani when gi-load ang model, with main integration sa application/controllers/Stock.php, application/controllers/Dashboard.php, ug application/controllers/Reports.php.
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('Stock_rules');
    }

    // Data helper ni para create transaction; main caller/integration pangitaa sa application/controllers/Stock.php, application/controllers/Dashboard.php, ug application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    public function create_transaction($type, $supplier_id, $remarks, $user_id, $items) {
        if (!in_array($type, array('stock_in', 'stock_out'), TRUE) || empty($items)) {
            return array('success' => FALSE, 'message' => 'A valid stock transaction with at least one item is required.');
        }

        $supplier_id = filter_var(
            $supplier_id,
            FILTER_VALIDATE_INT,
            array('options' => array(
                'min_range' => 1,
                'max_range' => Stock_rules::MAX_STOCK
            ))
        );

        if ($supplier_id === FALSE) {
            return array(
                'success' => FALSE,
                'message' => 'A valid supplier is required for stock transactions.'
            );
        }

        $supplier_id = (int) $supplier_id;
        $active_supplier = $this->db
            ->where('id', $supplier_id)
            ->where('status', 1)
            ->count_all_results('suppliers') > 0;

        if (!$active_supplier) {
            return array('success' => FALSE, 'message' => 'The selected supplier is invalid or inactive.');
        }

        // Normalize first para duplicate product rows ma-combine before stock update.
        $normalized = $this->normalize_items($items);

        if ($normalized === FALSE || empty($normalized)) {
            return array(
                'success' => FALSE,
                'message' => 'Every stock item must contain a valid product and a positive whole-number quantity.'
            );
        }

        $this->db->trans_begin();
        $transaction_no = strtoupper($type) . '-' . date('YmdHis') . '-' . strtoupper(substr(uniqid(), -6));
        $this->db->insert('stock_transactions', array(
            'transaction_no' => $transaction_no,
            'type' => $type,
            'supplier_id' => $supplier_id,
            'remarks' => trim((string) $remarks),
            'created_by' => (int) $user_id
        ));
        $transaction_id = (int) $this->db->insert_id();

        if ($transaction_id <= 0) {
            $this->db->trans_rollback();
            return array('success' => FALSE, 'message' => 'Unable to create the stock transaction.');
        }

        foreach ($normalized as $product_id => $quantity) {
            $product = $this->get_product_for_update($product_id);
            if (!$product || !(int) $product->status) {
                $this->db->trans_rollback();
                return array('success' => FALSE, 'message' => 'One of the selected products is invalid or inactive.');
            }

            if ((int) $product->supplier_id !== $supplier_id) {
                $this->db->trans_rollback();
                return array(
                    'success' => FALSE,
                    'message' => $product->product_name . ' is not assigned to the selected supplier.'
                );
            }

            try {
                $new_stock = $this->stock_rules->calculate_stock($product->stock, $quantity, $type);
            } catch (UnderflowException $exception) {
                $this->db->trans_rollback();
                return array('success' => FALSE, 'message' => 'Insufficient stock for ' . $product->product_name . '.');
            } catch (InvalidArgumentException $exception) {
                $this->db->trans_rollback();
                return array('success' => FALSE, 'message' => $exception->getMessage());
            }

            $this->db->insert('stock_transaction_items', array(
                'transaction_id' => $transaction_id,
                'product_id' => $product_id,
                'quantity' => $quantity,
                'cost_price' => $product->cost_price
            ));
            $this->db->update('products', array('stock' => $new_stock), array('id' => $product_id));
        }

        $this->log_activity($user_id, $type, 'Processed ' . $transaction_no);
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('success' => FALSE, 'message' => 'The stock transaction could not be completed.');
        }

        $this->db->trans_commit();
        return array('success' => TRUE, 'transaction_no' => $transaction_no);
    }

    // Data helper ni para create adjustment; main caller/integration pangitaa sa application/controllers/Stock.php, application/controllers/Dashboard.php, ug application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    public function create_adjustment($product_id, $actual_stock, $reason, $user_id) {
        $product_id = filter_var(
            $product_id,
            FILTER_VALIDATE_INT,
            array('options' => array(
                'min_range' => 1,
                'max_range' => Stock_rules::MAX_STOCK
            ))
        );

        if ($product_id === FALSE) {
            return array(
                'success' => FALSE,
                'message' => 'A valid product is required for the stock adjustment.'
            );
        }

        try {
            $actual_stock = $this->stock_rules->validate_stock_value($actual_stock);
        } catch (InvalidArgumentException $exception) {
            return array(
                'success' => FALSE,
                'message' => $exception->getMessage()
            );
        }

        $product_id = (int) $product_id;
        $reason = trim((string) $reason);

        if ($reason === '') {
            return array(
                'success' => FALSE,
                'message' => 'A reason is required for the stock adjustment.'
            );
        }

        $this->db->trans_begin();
        $product = $this->get_product_for_update($product_id);
        if (!$product || !(int) $product->status) {
            $this->db->trans_rollback();
            return array(
                'success' => FALSE,
                'message' => 'An active product is required for the stock adjustment.'
            );
        }

        $difference = $actual_stock - (int) $product->stock;
        if ($difference === 0) {
            $this->db->trans_rollback();
            return array('success' => FALSE, 'message' => 'Actual stock is already equal to system stock. No adjustment is needed.');
        }

        $transaction_no = 'ADJUSTMENT-' . date('YmdHis') . '-' . strtoupper(substr(uniqid(), -6));
        $this->db->insert('stock_transactions', array(
            'transaction_no' => $transaction_no,
            'type' => 'adjustment',
            'supplier_id' => NULL,
            'remarks' => $reason,
            'created_by' => (int) $user_id
        ));
        $transaction_id = (int) $this->db->insert_id();
        if ($transaction_id <= 0) {
            $this->db->trans_rollback();
            return array('success' => FALSE, 'message' => 'Unable to create the adjustment transaction.');
        }

        $this->db->insert('stock_adjustments', array(
            'product_id' => $product_id,
            'system_stock' => $product->stock,
            'actual_stock' => $actual_stock,
            'difference' => $difference,
            'reason' => $reason,
            'created_by' => (int) $user_id
        ));
        $this->db->insert('stock_transaction_items', array(
            'transaction_id' => $transaction_id,
            'product_id' => $product_id,
            'quantity' => abs($difference),
            'cost_price' => $product->cost_price
        ));
        $this->db->update('products', array('stock' => $actual_stock), array('id' => $product_id));
        $this->log_activity($user_id, 'stock_adjustment', 'Processed ' . $transaction_no);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('success' => FALSE, 'message' => 'The stock adjustment could not be completed.');
        }

        $this->db->trans_commit();
        return array('success' => TRUE, 'transaction_no' => $transaction_no);
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

    // Data helper ni para normalize items; main caller/integration pangitaa sa application/controllers/Stock.php, application/controllers/Dashboard.php, ug application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    private function normalize_items($items) {
        $normalized = array();

        foreach ((array) $items as $item) {
            if (!is_array($item) ||
                !isset($item['product_id']) ||
                !isset($item['quantity']) ||
                !is_scalar($item['product_id']) ||
                !is_scalar($item['quantity'])) {
                return FALSE;
            }

            $product_id = filter_var(
                $item['product_id'],
                FILTER_VALIDATE_INT,
                array('options' => array(
                    'min_range' => 1,
                    'max_range' => 2147483647
                ))
            );
            $quantity = filter_var(
                $item['quantity'],
                FILTER_VALIDATE_INT,
                array('options' => array(
                    'min_range' => 1,
                    'max_range' => 2147483647
                ))
            );

            if ($product_id === FALSE || $quantity === FALSE) {
                return FALSE;
            }

            $product_id = (int) $product_id;
            $quantity = (int) $quantity;

            if (!isset($normalized[$product_id])) {
                $normalized[$product_id] = 0;
            }

            if ($normalized[$product_id] > 2147483647 - $quantity) {
                return FALSE;
            }

            $normalized[$product_id] += $quantity;
        }

        return $normalized;
    }

    // Data helper ni para get product for update; main caller/integration pangitaa sa application/controllers/Stock.php, application/controllers/Dashboard.php, ug application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    private function get_product_for_update($product_id) {
        return $this->db->query('SELECT * FROM products WHERE id = ? FOR UPDATE', array((int) $product_id))->row();
    }

    // Data helper ni para log activity; main caller/integration pangitaa sa application/controllers/Stock.php, application/controllers/Dashboard.php, ug application/controllers/Reports.php, so didto tan-awa ang business flow if mag-trace ka.
    private function log_activity($user_id, $action, $description) {
        $this->db->insert('activity_logs', array(
            'user_id' => (int) $user_id,
            'action' => $action,
            'description' => $description,
            'ip_address' => $this->input->ip_address()
        ));
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
