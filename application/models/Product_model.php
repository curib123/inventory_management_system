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

        if ($limit !== NULL) {
            $this->db->limit((int) $limit, (int) $offset);
        }

        return $this->db->get()->result();
    }

    public function get_active_by_supplier($supplier_id) {
        $this->db->select(
            'p.id, p.product_code, p.product_name, p.unit, p.stock, ' .
            'p.reorder_level, p.cost_price, p.selling_price, p.supplier_id'
        );
        $this->db->from('products p');
        $this->db->where('p.supplier_id', (int) $supplier_id);
        $this->db->where('p.status', 1);

        // Low-stock products first, then the lowest stock, then product name.
        $this->db->order_by('(p.stock <= p.reorder_level)', 'DESC', FALSE);
        $this->db->order_by('p.stock', 'ASC');
        $this->db->order_by('p.product_name', 'ASC');

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
        return $this->db
            ->where('product_id', (int) $id)
            ->count_all_results('stock_transaction_items') > 0;
    }

    public function save($data, $id = NULL) {
        if (!$this->relationships_are_valid($data, $id)) {
            return FALSE;
        }

        if ($id !== NULL) {
            unset($data['stock']);
            $this->db->where('id', (int) $id);
            return $this->db->update('products', $data);
        }

        return $this->db->insert('products', $data);
    }

    private function relationships_are_valid($data, $id = NULL) {
        $current = NULL;

        if ($id !== NULL) {
            $current = $this->db
                ->get_where('products', array('id' => (int) $id))
                ->row();

            if (!$current) {
                return FALSE;
            }
        }

        if (array_key_exists('category_id', $data)) {
            $category_id = filter_var(
                $data['category_id'],
                FILTER_VALIDATE_INT,
                array('options' => array(
                    'min_range' => 1,
                    'max_range' => 2147483647
                ))
            );

            if ($category_id === FALSE) {
                return FALSE;
            }

            $category = $this->db
                ->get_where('categories', array('id' => (int) $category_id))
                ->row();

            $preserves_existing_category =
                $current &&
                (int) $current->category_id === (int) $category_id;

            if (
                !$category ||
                (!(int) $category->status && !$preserves_existing_category)
            ) {
                return FALSE;
            }
        }

        if (array_key_exists('supplier_id', $data)) {
            $supplier_raw = $data['supplier_id'];

            if ($supplier_raw !== NULL && $supplier_raw !== '') {
                $supplier_id = filter_var(
                    $supplier_raw,
                    FILTER_VALIDATE_INT,
                    array('options' => array(
                        'min_range' => 1,
                        'max_range' => 2147483647
                    ))
                );

                if ($supplier_id === FALSE) {
                    return FALSE;
                }

                $supplier = $this->db
                    ->get_where('suppliers', array('id' => (int) $supplier_id))
                    ->row();

                $preserves_existing_supplier =
                    $current &&
                    $current->supplier_id !== NULL &&
                    (int) $current->supplier_id === (int) $supplier_id;

                if (
                    !$supplier ||
                    (!(int) $supplier->status && !$preserves_existing_supplier)
                ) {
                    return FALSE;
                }
            }
        }

        return TRUE;
    }

    public function delete($id) {
        return $this->db->delete('products', array('id' => (int) $id));
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

    public function count_datatable_filtered($search, $filters = array()) {
        $this->build_datatable_query($search, $filters);
        return $this->db->count_all_results();
    }

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
