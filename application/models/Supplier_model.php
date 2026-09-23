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
}
