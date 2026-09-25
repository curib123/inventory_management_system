<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Supplier_model extends CI_Model {

    // Setup ni sa Supplier_model; CodeIgniter mo-run ani when gi-load ang model, with main integration sa application/controllers/Suppliers.php, application/controllers/Products.php, ug application/controllers/Stock.php.
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // Data helper ni para get all; main caller/integration pangitaa sa application/controllers/Suppliers.php, application/controllers/Products.php, ug application/controllers/Stock.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_all() {
        $this->db->order_by('supplier_name', 'ASC');
        return $this->db->get('suppliers')->result();
    }

    // Data helper ni para get by id; main caller/integration pangitaa sa application/controllers/Suppliers.php, application/controllers/Products.php, ug application/controllers/Stock.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_by_id($id) {
        return $this->db->get_where('suppliers', array('id' => (int) $id))->row();
    }

    // Data helper ni para get active; main caller/integration pangitaa sa application/controllers/Suppliers.php, application/controllers/Products.php, ug application/controllers/Stock.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_active() {
        $this->db->where('status', 1);
        $this->db->order_by('supplier_name', 'ASC');
        return $this->db->get('suppliers')->result();
    }

    // Data helper ni para search active; main caller/integration pangitaa sa application/controllers/Suppliers.php, application/controllers/Products.php, ug application/controllers/Stock.php, so didto tan-awa ang business flow if mag-trace ka.
    public function search_active($query = '', $limit = 20) {
        $query = trim((string) $query);
        $limit = max(1, min(50, (int) $limit));

        $this->db->select('id, supplier_name, contact_person, phone');
        $this->db->from('suppliers');
        $this->db->where('status', 1);

        if ($query !== '') {
            $this->db->group_start();
            $this->db->like('supplier_name', $query);
            $this->db->or_like('contact_person', $query);
            $this->db->or_like('phone', $query);
            $this->db->group_end();
        }

        $this->db->order_by('supplier_name', 'ASC');
        $this->db->limit($limit);

        return $this->db->get()->result();
    }

    // Data helper ni para save; main caller/integration pangitaa sa application/controllers/Suppliers.php, application/controllers/Products.php, ug application/controllers/Stock.php, so didto tan-awa ang business flow if mag-trace ka.
    public function save($data, $id = NULL) {
        if ($id !== NULL) {
            $this->db->where('id', (int) $id);
            return $this->db->update('suppliers', $data);
        }
        return $this->db->insert('suppliers', $data);
    }

    // Data helper ni para delete; main caller/integration pangitaa sa application/controllers/Suppliers.php, application/controllers/Products.php, ug application/controllers/Stock.php, so didto tan-awa ang business flow if mag-trace ka.
    public function delete($id) {
        $id = (int) $id;

        if ($id <= 0 || $this->has_dependencies($id)) {
            return FALSE;
        }

        $this->db->where('id', $id);
        return $this->db->delete('suppliers');
    }

    // Data helper ni para get supplier products; main caller/integration pangitaa sa application/controllers/Suppliers.php, application/controllers/Products.php, ug application/controllers/Stock.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_supplier_products($supplier_id) {
        $this->db->select('p.*');
        $this->db->from('products p');
        $this->db->where('p.supplier_id', (int) $supplier_id);
        $this->db->where('p.status', 1);
        $this->db->order_by('(p.stock <= p.reorder_level)', 'DESC', FALSE);
        $this->db->order_by('p.stock', 'ASC');
        $this->db->order_by('p.product_name', 'ASC');
        return $this->db->get()->result();
    }

    // Data helper ni para count products; main caller/integration pangitaa sa application/controllers/Suppliers.php, application/controllers/Products.php, ug application/controllers/Stock.php, so didto tan-awa ang business flow if mag-trace ka.
    public function count_products($supplier_id) {
        return $this->db
            ->where('supplier_id', (int) $supplier_id)
            ->count_all_results('products');
    }

    // Data helper ni para count transactions; main caller/integration pangitaa sa application/controllers/Suppliers.php, application/controllers/Products.php, ug application/controllers/Stock.php, so didto tan-awa ang business flow if mag-trace ka.
    public function count_transactions($supplier_id) {
        return $this->db
            ->where('supplier_id', (int) $supplier_id)
            ->count_all_results('stock_transactions');
    }

    // Data helper ni para has dependencies; main caller/integration pangitaa sa application/controllers/Suppliers.php, application/controllers/Products.php, ug application/controllers/Stock.php, so didto tan-awa ang business flow if mag-trace ka.
    public function has_dependencies($supplier_id) {
        return $this->count_products($supplier_id) > 0 ||
            $this->count_transactions($supplier_id) > 0;
    }

    // Data helper ni para count all; main caller/integration pangitaa sa application/controllers/Suppliers.php, application/controllers/Products.php, ug application/controllers/Stock.php, so didto tan-awa ang business flow if mag-trace ka.
    public function count_all() {
        return $this->db->count_all('suppliers');
    }

    // Data helper ni para get datatable; main caller/integration pangitaa sa application/controllers/Suppliers.php, application/controllers/Products.php, ug application/controllers/Stock.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_datatable($start, $length, $search, $order_column, $order_dir, $filters = array()) {
        $this->db->select('s.id, s.supplier_name, s.contact_person, s.phone, s.address, s.status,s.created_at,s.updated_at');
        $this->db->from('suppliers s');
        $this->apply_datatable_search($search, $filters);
        if ($order_column) {
            $this->db->order_by($order_column, $order_dir);
        }
        $this->db->limit((int) $length, (int) $start);
        return $this->db->get()->result();
    }

    // Data helper ni para count datatable filtered; main caller/integration pangitaa sa application/controllers/Suppliers.php, application/controllers/Products.php, ug application/controllers/Stock.php, so didto tan-awa ang business flow if mag-trace ka.
    public function count_datatable_filtered($search, $filters = array()) {
        $this->db->from('suppliers s');
        $this->apply_datatable_search($search, $filters);
        return $this->db->count_all_results();
    }

    // Data helper ni para apply datatable search; main caller/integration pangitaa sa application/controllers/Suppliers.php, application/controllers/Products.php, ug application/controllers/Stock.php, so didto tan-awa ang business flow if mag-trace ka.
    private function apply_datatable_search($search, $filters = array()) {
        $status = isset($filters['status']) ? strtolower((string) $filters['status']) : '';
        if ($status === 'active') {
            $this->db->where('s.status', 1);
        } elseif ($status === 'inactive') {
            $this->db->where('s.status', 0);
        }

        if ($search === '') {
            return;
        }

        $this->db->group_start();
        $this->db->like('s.supplier_name', $search);
        $this->db->or_like('s.contact_person', $search);
        $this->db->or_like('s.phone', $search);
        $this->db->or_like('s.address', $search);
        if (strcasecmp($search, 'active') === 0) {
            $this->db->or_where('s.status', 1);
        } elseif (strcasecmp($search, 'inactive') === 0) {
            $this->db->or_where('s.status', 0);
        }
        $this->db->or_like('s.created_at', $search);
        $this->db->or_like('s.updated_at', $search);
        $this->db->group_end();
    }
}
