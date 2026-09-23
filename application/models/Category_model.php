<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Category_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_all($limit = NULL, $offset = 0) {
        $this->db->order_by('category_name', 'ASC');
        if ($limit !== NULL) {
            $this->db->limit((int) $limit, (int) $offset);
        }
        return $this->db->get('categories')->result();
    }

    public function count_all() {
        return $this->db->count_all('categories');
    }

    public function get_by_id($id) {
        return $this->db->get_where('categories', array('id' => (int) $id))->row();
    }

    public function name_exists($name, $exclude_id = NULL) {
        $this->db->where('category_name', trim($name));
        if ($exclude_id !== NULL) {
            $this->db->where('id !=', (int) $exclude_id);
        }
        return $this->db->count_all_results('categories') > 0;
    }

    public function save($data, $id = NULL) {
        if ($id !== NULL) {
            $this->db->where('id', (int) $id);
            return $this->db->update('categories', $data);
        }
        return $this->db->insert('categories', $data);
    }

    public function delete($id) {
        $this->db->where('id', (int) $id);
        return $this->db->delete('categories');
    }

    public function count_products($category_id) {
        $this->db->where('category_id', (int) $category_id);
        return $this->db->count_all_results('products');
    }
}
