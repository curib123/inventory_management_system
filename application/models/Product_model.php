<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends CI_Model {

    // Setup ni sa Product_model; CodeIgniter mo-run ani when gi-load ang model, with main integration sa application/controllers/Products.php, application/controllers/Stock.php, ug application/controllers/Dashboard.php.
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // Data helper ni para get all; main caller/integration pangitaa sa application/controllers/Products.php, application/controllers/Stock.php, ug application/controllers/Dashboard.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_all($limit = NULL, $offset = 0) {
        $this->db->select('p.*, c.category_name, s.supplier_name');
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->join('suppliers s', 's.id = p.supplier_id', 'left');
        $this->db->order_by('p.product_name', 'ASC');

        if ($limit !== NULL) {
            $this->db->limit((int) $limit, (int) $offset);
        }

        return $this->db->get()->result();
    }

    // Data helper ni para get active; main caller/integration pangitaa sa application/controllers/Products.php, application/controllers/Stock.php, ug application/controllers/Dashboard.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_active($limit = NULL, $offset = 0) {
        $this->db->select('p.*, c.category_name, s.supplier_name');
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->join('suppliers s', 's.id = p.supplier_id', 'left');
        $this->db->where('p.status', 1);
        $this->db->order_by('p.product_name', 'ASC');

        if ($limit !== NULL) {
            $this->db->limit((int) $limit, (int) $offset);
        }

        return $this->db->get()->result();
    }

    // Data helper ni para search active; main caller/integration pangitaa sa application/controllers/Products.php, application/controllers/Stock.php, ug application/controllers/Dashboard.php, so didto tan-awa ang business flow if mag-trace ka.
    public function search_active($query = '', $supplier_scope = NULL, $limit = 20) {
        $query = trim((string) $query);
        $limit = max(1, min(50, (int) $limit));

        $this->db->select(
            'p.id, p.product_code, p.product_name, p.unit, p.stock, ' .
            'p.reorder_level, p.supplier_id, s.supplier_name'
        );
        $this->db->from('products p');
        $this->db->join('suppliers s', 's.id = p.supplier_id', 'left');
        $this->db->where('p.status', 1);

        if ($supplier_scope === 'unassigned') {
            $this->db->where('p.supplier_id IS NULL', NULL, FALSE);
        } elseif ($supplier_scope !== NULL && (int) $supplier_scope > 0) {
            $this->db->where('p.supplier_id', (int) $supplier_scope);
        }

        if ($query !== '') {
            $this->db->group_start();
            $this->db->like('p.product_code', $query);
            $this->db->or_like('p.product_name', $query);
            $this->db->or_like('p.unit', $query);
            $this->db->or_like('s.supplier_name', $query);
            $this->db->group_end();
        }

        $this->db->order_by('p.product_name', 'ASC');
        $this->db->limit($limit);

        return $this->db->get()->result();
    }

    // Data helper ni para get active by supplier; main caller/integration pangitaa sa application/controllers/Products.php, application/controllers/Stock.php, ug application/controllers/Dashboard.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_active_by_supplier($supplier_id, $mode = 'stock_in') {
        $this->db->select(
            'p.id, p.product_code, p.product_name, p.unit, p.stock, ' .
            'p.reorder_level, p.cost_price, p.selling_price, p.supplier_id'
        );
        $this->db->from('products p');
        $this->db->where('p.supplier_id', (int) $supplier_id);
        $this->db->where('p.status', 1);

        if ($mode === 'stock_out') {
            // For releases, products with available stock should be easiest to reach.
            $this->db->order_by('(p.stock > 0)', 'DESC', FALSE);
            $this->db->order_by('p.product_name', 'ASC');
        } else {
            // For receiving, low-stock items deserve attention first.
            $this->db->order_by('(p.stock <= p.reorder_level)', 'DESC', FALSE);
            $this->db->order_by('p.stock', 'ASC');
            $this->db->order_by('p.product_name', 'ASC');
        }

        return $this->db->get()->result();
    }

    // Data helper ni para count all; main caller/integration pangitaa sa application/controllers/Products.php, application/controllers/Stock.php, ug application/controllers/Dashboard.php, so didto tan-awa ang business flow if mag-trace ka.
    public function count_all() {
        return $this->db->count_all('products');
    }

    // Data helper ni para get by id; main caller/integration pangitaa sa application/controllers/Products.php, application/controllers/Stock.php, ug application/controllers/Dashboard.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_by_id($id) {
        $this->db->select('p.*, c.category_name, s.supplier_name');
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->join('suppliers s', 's.id = p.supplier_id', 'left');
        $this->db->where('p.id', (int) $id);

        return $this->db->get()->row();
    }

    // Data helper ni para code exists; main caller/integration pangitaa sa application/controllers/Products.php, application/controllers/Stock.php, ug application/controllers/Dashboard.php, so didto tan-awa ang business flow if mag-trace ka.
    public function code_exists($code, $exclude_id = NULL) {
        $this->db->where('product_code', trim($code));

        if ($exclude_id !== NULL) {
            $this->db->where('id !=', (int) $exclude_id);
        }

        return $this->db->count_all_results('products') > 0;
    }

    // Data helper ni para has transaction history; main caller/integration pangitaa sa application/controllers/Products.php, application/controllers/Stock.php, ug application/controllers/Dashboard.php, so didto tan-awa ang business flow if mag-trace ka.
    public function has_transaction_history($id) {
        return $this->db
            ->where('product_id', (int) $id)
            ->count_all_results('stock_transaction_items') > 0;
    }

    // Persistence helper ni para save product row; application/libraries/Product_service.php ang caller, while relationship ug uniqueness rules didto tanan.
    public function save($data, $id = NULL) {
        if ($id !== NULL) {
            return $this->db->update('products', $data, array('id' => (int) $id));
        }

        return $this->db->insert('products', $data);
    }

    // Data helper ni para delete; main caller/integration pangitaa sa application/controllers/Products.php, application/controllers/Stock.php, ug application/controllers/Dashboard.php, so didto tan-awa ang business flow if mag-trace ka.
    public function delete($id) {
        return $this->db->delete('products', array('id' => (int) $id));
    }

    // Data helper ni para get total products; main caller/integration pangitaa sa application/controllers/Products.php, application/controllers/Stock.php, ug application/controllers/Dashboard.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_total_products() {
        return $this->db->count_all('products');
    }

    // Data helper ni para get total stock; main caller/integration pangitaa sa application/controllers/Products.php, application/controllers/Stock.php, ug application/controllers/Dashboard.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_total_stock() {
        $this->db->select_sum('stock');
        $row = $this->db->get('products')->row();

        return ($row && $row->stock !== NULL) ? (int) $row->stock : 0;
    }

    // Data helper ni para get low stock products; main caller/integration pangitaa sa application/controllers/Products.php, application/controllers/Stock.php, ug application/controllers/Dashboard.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_low_stock_products() {
        $this->db->select('p.*, c.category_name');
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->where('p.stock <= p.reorder_level', NULL, FALSE);
        $this->db->where('p.status', 1);
        $this->db->order_by('p.stock', 'ASC');

        return $this->db->get()->result();
    }

    // Data helper ni para get datatable; main caller/integration pangitaa sa application/controllers/Products.php, application/controllers/Stock.php, ug application/controllers/Dashboard.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_datatable($start, $length, $search, $order_column, $order_dir, $filters = array()) {
        $this->build_datatable_query($search, $filters);
        $this->db->select(
            'p.id, p.product_code, p.product_name, c.category_name, ' .
            's.supplier_name, p.stock, p.selling_price, p.status'
        );

        if ($order_column) {
            $this->db->order_by($order_column, $order_dir);
        }

        $this->db->limit((int) $length, (int) $start);
        return $this->db->get()->result();
    }

    // Data helper ni para count datatable filtered; main caller/integration pangitaa sa application/controllers/Products.php, application/controllers/Stock.php, ug application/controllers/Dashboard.php, so didto tan-awa ang business flow if mag-trace ka.
    public function count_datatable_filtered($search, $filters = array()) {
        $this->build_datatable_query($search, $filters);
        return $this->db->count_all_results();
    }

    // Data helper ni para build datatable query; main caller/integration pangitaa sa application/controllers/Products.php, application/controllers/Stock.php, ug application/controllers/Dashboard.php, so didto tan-awa ang business flow if mag-trace ka.
    private function build_datatable_query($search, $filters = array()) {
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->join('suppliers s', 's.id = p.supplier_id', 'left');

        $status = isset($filters['status']) ? strtolower((string) $filters['status']) : '';
        if ($status === 'active') {
            $this->db->where('p.status', 1);
        } elseif ($status === 'inactive') {
            $this->db->where('p.status', 0);
        }

        $stock = isset($filters['stock']) ? strtolower((string) $filters['stock']) : '';
        if ($stock === 'out') {
            $this->db->where('p.stock <=', 0);
        } elseif ($stock === 'low') {
            $this->db->where('p.stock >', 0);
            $this->db->where('p.stock <= p.reorder_level', NULL, FALSE);
        } elseif ($stock === 'healthy') {
            $this->db->where('p.stock > p.reorder_level', NULL, FALSE);
        }

        if ($search === '') {
            return;
        }

        $this->db->group_start();
        $this->db->like('p.product_code', $search);
        $this->db->or_like('p.product_name', $search);
        $this->db->or_like('c.category_name', $search);
        $this->db->or_like('s.supplier_name', $search);
        $this->db->or_like('p.unit', $search);

        if (strcasecmp($search, 'active') === 0) {
            $this->db->or_where('p.status', 1);
        } elseif (strcasecmp($search, 'inactive') === 0) {
            $this->db->or_where('p.status', 0);
        }

        $this->db->group_end();
    }
}
