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
        return $this->db->get_where('suppliers', array('id' => $id))->row();
    }

    public function save($data, $id = NULL) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->update('suppliers', $data);
        }

        return $this->db->insert('suppliers', $data);
    }

    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('suppliers');
    }
}
