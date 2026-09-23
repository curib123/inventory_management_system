<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Supplier_model extends CI_Model {

    // suppliers model to handle supplier management functionality
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    // Function to get all suppliers ordered by name
    public function get_all() {
        $this->db->order_by('supplier_name', 'ASC');
        return $this->db->get('suppliers')->result();
    }
    // Function to get a supplier by its ID
    public function get_by_id($id) {
        return $this->db->get_where('suppliers', array('id' => $id))->row();
    }
    // Function to save a new supplier or update an existing supplier
    public function save($data, $id = NULL) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->update('suppliers', $data);
        }

        return $this->db->insert('suppliers', $data);
    }
    // Function to delete a supplier by its ID
    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('suppliers');
    }
    // Function to get the total number of suppliers
    public function get_supplier_products($supplier_id) {
        $this->db->select('p.*');
        $this->db->from('products p');
        $this->db->where('p.supplier_id', $supplier_id);
        $this->db->order_by('p.product_name', 'ASC');
        return $this->db->get()->result();
    }
}
