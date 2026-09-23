<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends CI_Model {

     // Product model to handle product-related database operations
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    // Function to get all products with their category and supplier names
    public function get_all($limit = 10, $offset = 0) {
        $this->db->select('p.*, c.category_name, s.supplier_name');
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->join('suppliers s', 's.id = p.supplier_id', 'left');
        $this->db->order_by('p.product_name', 'ASC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }

    public function count_all() {
        return $this->db->count_all('products');
    }
    // Function to get a product by its ID with its category and supplier names
    public function get_by_id($id) {
        $this->db->select('p.*, c.category_name, s.supplier_name');
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->join('suppliers s', 's.id = p.supplier_id', 'left');
        $this->db->where('p.id', $id);
        return $this->db->get()->row();
    }
    // Function to save a new product or update an existing product
    public function save($data, $id = NULL) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->update('products', $data);
        }

        return $this->db->insert('products', $data);
    }
    // Function to delete a product by its ID
    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('products');
    }
    // Function to get the total number of products
    public function get_total_products() {
        return $this->db->count_all('products');
    }
    // Function to get the total stock of all products
    public function get_total_stock() {
        $this->db->select_sum('stock');
        $query = $this->db->get('products');
        $row = $query->row();
        return ($row && $row->stock) ? (int) $row->stock : 0;
    }
    // Function to get products with stock less than or equal to their reorder level
    public function get_low_stock_products() {
        $this->db->select('p.*, c.category_name');
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->where('p.stock <= p.reorder_level');
        $this->db->order_by('p.stock', 'ASC');
        return $this->db->get()->result();
    }
    // Function to update the stock of a product based on the type of transaction (in or out)   
    public function update_stock($product_id, $quantity, $type = 'in') {
        $product = $this->get_by_id($product_id);

        if (!$product) {
            return FALSE;
        }

        $new_stock = ($type === 'out') ? ($product->stock - $quantity) : ($product->stock + $quantity);

        return $this->db->update('products', array('stock' => $new_stock), array('id' => $product_id));
    }
}
