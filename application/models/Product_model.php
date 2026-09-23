<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_all($limit = 10, $offset = 0) {
        $this->db->select('p.*, c.category_name, s.supplier_name');
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->join('suppliers s', 's.id = p.supplier_id', 'left');
        $this->db->order_by('p.product_name', 'ASC');
        $this->db->limit((int) $limit, (int) $offset);
        return $this->db->get()->result();
    }

    public function get_active($limit = 10000, $offset = 0) {
        $this->db->select('p.*, c.category_name, s.supplier_name');
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->join('suppliers s', 's.id = p.supplier_id', 'left');
        $this->db->where('p.status', 1);
        $this->db->order_by('p.product_name', 'ASC');
        $this->db->limit((int) $limit, (int) $offset);
        return $this->db->get()->result();
    }

    public function count_all() {
        return $this->db->count_all('products');
    }

    public function get_by_id($id) {
        $this->db->select('p.*, c.category_name, s.supplier_name');
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->join('suppliers s', 's.id = p.supplier_id', 'left');
        $this->db->where('p.id', (int) $id);
        return $this->db->get()->row();
    }

    public function code_exists($code, $exclude_id = NULL) {
        $this->db->where('product_code', trim($code));
        if ($exclude_id !== NULL) {
            $this->db->where('id !=', (int) $exclude_id);
        }
        return $this->db->count_all_results('products') > 0;
    }

    public function has_transaction_history($id) {
        return $this->db->where('product_id', (int) $id)
            ->count_all_results('stock_transaction_items') > 0;
    }

    public function save($data, $id = NULL) {
        if ($id !== NULL) {
            unset($data['stock']);
            $this->db->where('id', (int) $id);
            return $this->db->update('products', $data);
        }
        return $this->db->insert('products', $data);
    }

    public function delete($id) {
        $this->db->where('id', (int) $id);
        return $this->db->delete('products');
    }

    public function get_total_products() {
        return $this->db->count_all('products');
    }

    public function get_total_stock() {
        $this->db->select_sum('stock');
        $row = $this->db->get('products')->row();
        return ($row && $row->stock !== NULL) ? (int) $row->stock : 0;
    }

    public function get_low_stock_products() {
        $this->db->select('p.*, c.category_name');
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->where('p.stock <= p.reorder_level', NULL, FALSE);
        $this->db->where('p.status', 1);
        $this->db->order_by('p.stock', 'ASC');
        return $this->db->get()->result();
    }
}
