<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_all() {
        $this->db->select('products.*, suppliers.supplier_name');
        $this->db->from('products');
        $this->db->join('suppliers', 'suppliers.id = products.supplier_id', 'left');
        $this->db->order_by('products.product_name', 'ASC');
        return $this->db->get()->result();
    }

    public function get_by_id($id) {
        return $this->db->get_where('products', array('id' => $id))->row();
    }

    public function save($data, $id = NULL) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->update('products', $data);
        }

        return $this->db->insert('products', $data);
    }

    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('products');
    }

    public function get_total_products() {
        return $this->db->count_all('products');
    }

    public function get_total_stock() {
        $this->db->select_sum('stock');
        $query = $this->db->get('products');
        $result = $query->row();
        return ($result && $result->stock) ? $result->stock : 0;
    }
}
