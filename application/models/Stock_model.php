<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Stock_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('Stock_rules');
    }

    public function create_transaction($type, $supplier_id, $remarks, $user_id, $items) {
        if (!in_array($type, array('stock_in', 'stock_out'), TRUE) || empty($items)) {
            return array('success' => FALSE, 'message' => 'A valid stock transaction with at least one item is required.');
        }

        $supplier_id = ($supplier_id === '' || $supplier_id === NULL) ? NULL : (int) $supplier_id;
        if ($type === 'stock_in' && !$supplier_id) {
            return array('success' => FALSE, 'message' => 'A supplier is required for stock-in transactions.');
        }

        $normalized = array();
        foreach ((array) $items as $item) {
            $product_id = isset($item['product_id']) ? (int) $item['product_id'] : 0;
            $quantity = isset($item['quantity']) ? (int) $item['quantity'] : 0;
            if ($product_id <= 0 || $quantity <= 0) {
                continue;
            }
            if (!isset($normalized[$product_id])) {
                $normalized[$product_id] = 0;
            }
            $normalized[$product_id] += $quantity;
        }

        if (empty($normalized)) {
            return array('success' => FALSE, 'message' => 'At least one valid product and quantity is required.');
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

            if ($type === 'stock_in' && (int) $product->supplier_id !== $supplier_id) {
                $this->db->trans_rollback();
                return array('success' => FALSE, 'message' => $product->product_name . ' is not assigned to the selected supplier.');
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

    public function create_adjustment($product_id, $actual_stock, $reason, $user_id) {
        $product_id = (int) $product_id;
        $actual_stock = (int) $actual_stock;
        $reason = trim((string) $reason);

        $this->db->trans_begin();
        $product = $this->get_product_for_update($product_id);
        if (!$product || !$product->status || $actual_stock < 0 || $reason === '') {
            $this->db->trans_rollback();
            return array('success' => FALSE, 'message' => 'An active product, non-negative stock, and reason are required.');
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

    public function get_transactions($limit = 20, $offset = 0) {
        $this->db->select('t.*, u.username, s.supplier_name');
        $this->db->from('stock_transactions t');
        $this->db->join('users u', 'u.id = t.created_by');
        $this->db->join('suppliers s', 's.id = t.supplier_id', 'left');
        $this->db->order_by('t.created_at', 'DESC');
        $this->db->order_by('t.id', 'DESC');
        $this->db->limit((int) $limit, (int) $offset);
        return $this->db->get()->result();
    }

    public function count_transactions() {
        return $this->db->count_all('stock_transactions');
    }

    public function get_transaction($id) {
        $this->db->select('t.*, u.username, s.supplier_name');
        $this->db->from('stock_transactions t');
        $this->db->join('users u', 'u.id = t.created_by');
        $this->db->join('suppliers s', 's.id = t.supplier_id', 'left');
        $this->db->where('t.id', (int) $id);
        return $this->db->get()->row();
    }

    public function get_transaction_items($transaction_id) {
        $this->db->select('i.*, p.product_code, p.product_name, p.unit');
        $this->db->from('stock_transaction_items i');
        $this->db->join('products p', 'p.id = i.product_id');
        $this->db->where('i.transaction_id', (int) $transaction_id);
        return $this->db->get()->result();
    }

    public function get_adjustments($limit = 20, $offset = 0) {
        $this->db->select('a.*, p.product_name, p.product_code, u.username');
        $this->db->from('stock_adjustments a');
        $this->db->join('products p', 'p.id = a.product_id');
        $this->db->join('users u', 'u.id = a.created_by');
        $this->db->order_by('a.created_at', 'DESC');
        $this->db->order_by('a.id', 'DESC');
        $this->db->limit((int) $limit, (int) $offset);
        return $this->db->get()->result();
    }

    public function count_adjustments() {
        return $this->db->count_all('stock_adjustments');
    }

    public function get_low_stock_products() {
        $this->db->where('stock <= reorder_level', NULL, FALSE);
        $this->db->where('status', 1);
        $this->db->order_by('stock', 'ASC');
        return $this->db->get('products')->result();
    }

    public function count_low_stock_products() {
        $this->db->where('stock <= reorder_level', NULL, FALSE);
        $this->db->where('status', 1);
        return $this->db->count_all_results('products');
    }

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

    public function get_inventory_value() {
        $this->db->select('COALESCE(SUM(stock * cost_price), 0) AS inventory_value', FALSE);
        $row = $this->db->get('products')->row();
        return $row ? (float) $row->inventory_value : 0;
    }

    public function get_stock_by_category() {
        $this->db->select('c.category_name, COUNT(p.id) AS product_count, COALESCE(SUM(p.stock), 0) AS total_stock');
        $this->db->from('categories c');
        $this->db->join('products p', 'p.category_id = c.id AND p.status = 1', 'left');
        $this->db->where('c.status', 1);
        $this->db->group_by('c.id');
        $this->db->order_by('c.category_name', 'ASC');
        return $this->db->get()->result();
    }

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

    private function get_product_for_update($product_id) {
        return $this->db->query('SELECT * FROM products WHERE id = ? FOR UPDATE', array((int) $product_id))->row();
    }

    private function log_activity($user_id, $action, $description) {
        $this->db->insert('activity_logs', array(
            'user_id' => (int) $user_id,
            'action' => $action,
            'description' => $description,
            'ip_address' => $this->input->ip_address()
        ));
    }
}
