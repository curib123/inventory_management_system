<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

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

    public function get_active($limit = NULL, $offset = 0) {
        $this->db->select('p.*, c.category_name, s.supplier_name');
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->join('suppliers s', 's.id = p.supplier_id', 'left');
        $this->db->where('p.status', 1);
        $this->db->order_by('p.product_name', 'ASC');

        // Optional limit ra ni bai; if NULL, kuhaon tanan active products.
        if ($limit !== NULL) {
            $this->db->limit((int) $limit, (int) $offset);
        }

        return $this->db->get()->result();
    }

    public function count_all() {
        return $this->db->count_all('products');
    }

    public function get_by_id($id) {
        $this->db->select('p.*, c.category_name, s.supplier_name');
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->join('suppliers s', 's.id = p.supplier_id', 'left');
        $this->db->where('p.id', (int) $id);
        return $this->db->get()->row();
    }

    public function code_exists($code, $exclude_id = NULL) {
        $this->db->where('product_code', trim($code));
        if ($exclude_id !== NULL) {
            $this->db->where('id !=', (int) $exclude_id);
        }
        return $this->db->count_all_results('products') > 0;
    }

    public function has_transaction_history($id) {
        return $this->db->where('product_id', (int) $id)
            ->count_all_results('stock_transaction_items') > 0;
    }

    public function save($data, $id = NULL) {
        if ($id !== NULL) {
            unset($data['stock']);
            $this->db->where('id', (int) $id);
            return $this->db->update('products', $data);
        }
        return $this->db->insert('products', $data);
    }

    public function delete($id) {
        $this->db->where('id', (int) $id);
        return $this->db->delete('products');
    }

    public function get_total_products() {
        return $this->db->count_all('products');
    }

    public function get_total_stock() {
        $this->db->select_sum('stock');
        $row = $this->db->get('products')->row();
        return ($row && $row->stock !== NULL) ? (int) $row->stock : 0;
    }

    public function get_low_stock_products() {
        $this->db->select('p.*, c.category_name');
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->where('p.stock <= p.reorder_level', NULL, FALSE);
        $this->db->where('p.status', 1);
        $this->db->order_by('p.stock', 'ASC');
        return $this->db->get()->result();
    }

    public function get_datatable($start, $length, $search, $order_column, $order_dir) {
        $this->build_datatable_query($search);
        $this->db->select('p.id, p.product_code, p.product_name, c.category_name, s.supplier_name, p.stock, p.selling_price, p.status,p.created_at,p.updated_at');
        if ($order_column) {
            $this->db->order_by($order_column, $order_dir);
        }
        $this->db->limit((int) $length, (int) $start);
        return $this->db->get()->result();
    }

    public function count_datatable_filtered($search) {
        $this->build_datatable_query($search);
        return $this->db->count_all_results();
    }

    private function build_datatable_query($search) {
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->join('suppliers s', 's.id = p.supplier_id', 'left');

        if ($search !== '') {
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
            $this->db->or_like('p.created_at',$search);
            $this-db->or_like('p.updated_at',$search);
            $this->db->group_end();
        }
    }
}
