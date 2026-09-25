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

    public function get_active() {
        $this->db->where('status', 1);
        $this->db->order_by('category_name', 'ASC');
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

    public function get_datatable($start, $length, $search, $order_column, $order_dir, $filters = array()) {
        $this->db->select('c.id, c.category_name, c.status, COUNT(p.id) AS product_count');
        $this->db->from('categories c');
        $this->db->join('products p', 'p.category_id = c.id', 'left');
        $this->apply_datatable_search($search, 'c', $filters);
        $this->db->group_by(array('c.id', 'c.category_name', 'c.status'));
        if ($order_column) {
            $this->db->order_by($order_column, $order_dir);
        }
        $this->db->limit((int) $length, (int) $start);
        return $this->db->get()->result();
    }

    public function count_datatable_filtered($search, $filters = array()) {
        $this->db->from('categories c');
        $this->apply_datatable_search($search, 'c', $filters);
        return $this->db->count_all_results();
    }

    private function apply_datatable_search($search, $alias, $filters = array()) {
        $status = isset($filters['status']) ? strtolower((string) $filters['status']) : '';
        if ($status === 'active') {
            $this->db->where($alias . '.status', 1);
        } elseif ($status === 'inactive') {
            $this->db->where($alias . '.status', 0);
        }

        if ($search === '') {
            return;
        }

        $this->db->group_start();
        $this->db->like($alias . '.category_name', $search);
        if (strcasecmp($search, 'active') === 0) {
            $this->db->or_where($alias . '.status', 1);
        } elseif (strcasecmp($search, 'inactive') === 0) {
            $this->db->or_where($alias . '.status', 0);
        }
        $this->db->group_end();
    }
}
