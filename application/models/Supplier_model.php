<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Supplier_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_all() {
        $this->db->order_by('supplier_name', 'ASC');
        return $this->db->get('suppliers')->result();
    }

    public function get_by_id($id) {
        return $this->db->get_where('suppliers', array('id' => (int) $id))->row();
    }

    public function save($data, $id = NULL) {
        if ($id !== NULL) {
            $this->db->where('id', (int) $id);
            return $this->db->update('suppliers', $data);
        }
        return $this->db->insert('suppliers', $data);
    }

    public function delete($id) {
        $this->db->where('id', (int) $id);
        return $this->db->delete('suppliers');
    }

    public function get_supplier_products($supplier_id) {
        $this->db->select('p.*');
        $this->db->from('products p');
        $this->db->where('p.supplier_id', (int) $supplier_id);
        $this->db->order_by('p.product_name', 'ASC');
        return $this->db->get()->result();
    }

    public function count_all() {
        return $this->db->count_all('suppliers');
    }

    public function get_datatable($start, $length, $search, $order_column, $order_dir) {
        $this->db->select('s.id, s.supplier_name, s.contact_person, s.phone, s.address, s.status');
        $this->db->from('suppliers s');
        $this->apply_datatable_search($search);
        if ($order_column) {
            $this->db->order_by($order_column, $order_dir);
        }
        $this->db->limit((int) $length, (int) $start);
        return $this->db->get()->result();
    }

    public function count_datatable_filtered($search) {
        $this->db->from('suppliers s');
        $this->apply_datatable_search($search);
        return $this->db->count_all_results();
    }

    private function apply_datatable_search($search) {
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
        $this->db->group_end();
    }
}
