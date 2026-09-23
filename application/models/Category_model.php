<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Category_model extends CI_Model {

    // categories model to handle category management functionality
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    // Function to get categories with pagination and limit support
    public function get_all($limit = 10, $offset = 0) {
        $this->db->order_by('category_name', 'ASC');
        $this->db->limit($limit, $offset);
        return $this->db->get('categories')->result();
    }

    public function count_all() {
        return $this->db->count_all('categories');
    }

    // Function to get a category by its ID
    public function get_by_id($id) {
        return $this->db->get_where('categories', array('id' => $id))->row();
    }

    // Function to save a new category or update an existing category
    public function save($data, $id = NULL) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->update('categories', $data);
        }

        return $this->db->insert('categories', $data);
    }

    // Function to delete a category by its ID
    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('categories');
    }

    // Function to count the number of products in a category
    public function count_products($category_id) {
        $this->db->where('category_id', $category_id);
        return $this->db->count_all_results('products');
    }
}
