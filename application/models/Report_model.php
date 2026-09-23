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
}
