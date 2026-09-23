<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Report_model extends CI_Model {
 
    // reports model to handle report generation functionality
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    // Function to get the inventory report with product details, category, supplier, and inventory value
    public function get_inventory_report() {
        $this->db->select('p.product_code, p.product_name, c.category_name, s.supplier_name, p.unit, p.stock, p.cost_price, (p.stock * p.cost_price) AS inventory_value');
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->join('suppliers s', 's.id = p.supplier_id', 'left');
        $this->db->order_by('p.product_name', 'ASC');
        return $this->db->get()->result_array();
    }
    // Function to get the stock movement report with transaction details, product, supplier, and user information
    public function get_stock_movement_report($type = NULL) {
        $this->db->select('t.transaction_no, t.type, p.product_code, p.product_name, i.quantity, i.cost_price, s.supplier_name, u.username, t.remarks, t.created_at');
        $this->db->from('stock_transactions t');
        $this->db->join('stock_transaction_items i', 'i.transaction_id = t.id');
        $this->db->join('products p', 'p.id = i.product_id');
        $this->db->join('suppliers s', 's.id = t.supplier_id', 'left');
        $this->db->join('users u', 'u.id = t.created_by');
        if ($type) {
            $this->db->where('t.type', $type);
        }
        $this->db->order_by('t.created_at', 'DESC');
        return $this->db->get()->result_array();
    }
    // Function to get the low stock report with product details, category, and shortage information
    public function get_low_stock_report() {
        $this->db->select('p.product_code, p.product_name, c.category_name, p.unit, p.stock, p.reorder_level, (p.reorder_level - p.stock) AS shortage');
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->where('p.stock <= p.reorder_level', NULL, FALSE);
        $this->db->where('p.status', 1);
        $this->db->order_by('p.stock', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_datatable($report, $start, $length, $search, $order_column, $order_dir) {
        $this->build_datatable_query($report, $search);
        $this->select_report_columns($report);
        if ($order_column) {
            $this->db->order_by($order_column, $order_dir);
        }
        $this->db->limit((int) $length, (int) $start);
        return $this->db->get()->result_array();
    }

    public function count_datatable_total($report) {
        $this->build_datatable_query($report, '');
        return $this->db->count_all_results();
    }

    public function count_datatable_filtered($report, $search) {
        $this->build_datatable_query($report, $search);
        return $this->db->count_all_results();
    }

    private function select_report_columns($report) {
        if ($report === 'inventory' || $report === 'valuation') {
            $this->db->select('p.product_code, p.product_name, c.category_name, s.supplier_name, p.unit, p.stock, p.cost_price, (p.stock * p.cost_price) AS inventory_value', FALSE);
            return;
        }

        if ($report === 'low-stock') {
            $this->db->select('p.product_code, p.product_name, c.category_name, p.unit, p.stock, p.reorder_level, (p.reorder_level - p.stock) AS shortage', FALSE);
            return;
        }

        $this->db->select('t.transaction_no, t.type, p.product_code, p.product_name, i.quantity, i.cost_price, s.supplier_name, u.username, t.remarks, t.created_at');
    }

    private function build_datatable_query($report, $search) {
        if ($report === 'inventory' || $report === 'valuation') {
            $this->db->from('products p');
            $this->db->join('categories c', 'c.id = p.category_id', 'left');
            $this->db->join('suppliers s', 's.id = p.supplier_id', 'left');

            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('p.product_code', $search);
                $this->db->or_like('p.product_name', $search);
                $this->db->or_like('c.category_name', $search);
                $this->db->or_like('s.supplier_name', $search);
                $this->db->or_like('p.unit', $search);
                $this->db->group_end();
            }
            return;
        }

        if ($report === 'low-stock') {
            $this->db->from('products p');
            $this->db->join('categories c', 'c.id = p.category_id', 'left');
            $this->db->where('p.stock <= p.reorder_level', NULL, FALSE);
            $this->db->where('p.status', 1);

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
        $this->db->join('products p', 'p.id = i.product_id');
        $this->db->join('suppliers s', 's.id = t.supplier_id', 'left');
        $this->db->join('users u', 'u.id = t.created_by');

        if ($report === 'stock-in') {
            $this->db->where('t.type', 'stock_in');
        } elseif ($report === 'stock-out') {
            $this->db->where('t.type', 'stock_out');
        }

        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('t.transaction_no', $search);
            $this->db->or_like('t.type', $search);
            $this->db->or_like('p.product_code', $search);
            $this->db->or_like('p.product_name', $search);
            $this->db->or_like('s.supplier_name', $search);
            $this->db->or_like('u.username', $search);
            $this->db->or_like('t.remarks', $search);
            $this->db->or_like('t.created_at', $search);
            $this->db->group_end();
        }
    }
}
