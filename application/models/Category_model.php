<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Category_model extends CI_Model {

    // Setup ni sa Category_model; CodeIgniter mo-run ani when gi-load ang model, with main integration sa application/controllers/Categories.php ug application/controllers/Products.php.
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // Data helper ni para get all; main caller/integration pangitaa sa application/controllers/Categories.php ug application/controllers/Products.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_all($limit = NULL, $offset = 0) {
        $this->db->order_by('category_name', 'ASC');
        if ($limit !== NULL) {
            $this->db->limit((int) $limit, (int) $offset);
        }
        return $this->db->get('categories')->result();
    }

    // Data helper ni para get active; main caller/integration pangitaa sa application/controllers/Categories.php ug application/controllers/Products.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_active() {
        $this->db->where('status', 1);
        $this->db->order_by('category_name', 'ASC');
        return $this->db->get('categories')->result();
    }

    // Data helper ni para search active; main caller/integration pangitaa sa application/controllers/Categories.php ug application/controllers/Products.php, so didto tan-awa ang business flow if mag-trace ka.
    public function search_active($query = '', $limit = 20) {
        $query = trim((string) $query);
        $limit = max(1, min(50, (int) $limit));

        $this->db->select('id, category_name');
        $this->db->from('categories');
        $this->db->where('status', 1);

        if ($query !== '') {
            $this->db->like('category_name', $query);
        }

        $this->db->order_by('category_name', 'ASC');
        $this->db->limit($limit);

        return $this->db->get()->result();
    }

    // Data helper ni para count all; main caller/integration pangitaa sa application/controllers/Categories.php ug application/controllers/Products.php, so didto tan-awa ang business flow if mag-trace ka.
    public function count_all() {
        return $this->db->count_all('categories');
    }

    // Data helper ni para get by id; main caller/integration pangitaa sa application/controllers/Categories.php ug application/controllers/Products.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_by_id($id) {
        return $this->db->get_where('categories', array('id' => (int) $id))->row();
    }

    // Data helper ni para name exists; main caller/integration pangitaa sa application/controllers/Categories.php ug application/controllers/Products.php, so didto tan-awa ang business flow if mag-trace ka.
    public function name_exists($name, $exclude_id = NULL) {
        $this->db->where('category_name', trim($name));
        if ($exclude_id !== NULL) {
            $this->db->where('id !=', (int) $exclude_id);
        }
        return $this->db->count_all_results('categories') > 0;
    }

    // Data helper ni para save; main caller/integration pangitaa sa application/controllers/Categories.php ug application/controllers/Products.php, so didto tan-awa ang business flow if mag-trace ka.
    public function save($data, $id = NULL) {
        if ($id !== NULL) {
            $this->db->where('id', (int) $id);
            return $this->db->update('categories', $data);
        }
        return $this->db->insert('categories', $data);
    }

    // Data helper ni para delete; main caller/integration pangitaa sa application/controllers/Categories.php ug application/controllers/Products.php, so didto tan-awa ang business flow if mag-trace ka.
    public function delete($id) {
        $this->db->where('id', (int) $id);
        return $this->db->delete('categories');
    }

    // Data helper ni para count products; main caller/integration pangitaa sa application/controllers/Categories.php ug application/controllers/Products.php, so didto tan-awa ang business flow if mag-trace ka.
    public function count_products($category_id) {
        $this->db->where('category_id', (int) $category_id);
        return $this->db->count_all_results('products');
    }

    // Data helper ni para get datatable; main caller/integration pangitaa sa application/controllers/Categories.php ug application/controllers/Products.php, so didto tan-awa ang business flow if mag-trace ka.
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

    // Data helper ni para count datatable filtered; main caller/integration pangitaa sa application/controllers/Categories.php ug application/controllers/Products.php, so didto tan-awa ang business flow if mag-trace ka.
    public function count_datatable_filtered($search, $filters = array()) {
        $this->db->from('categories c');
        $this->apply_datatable_search($search, 'c', $filters);
        return $this->db->count_all_results();
    }

    // Data helper ni para apply datatable search; main caller/integration pangitaa sa application/controllers/Categories.php ug application/controllers/Products.php, so didto tan-awa ang business flow if mag-trace ka.
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
