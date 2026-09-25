<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Report_model extends CI_Model {
 
    // Report model ni; diri gi-centralize ang queries para limpyo ra ang controllers.
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    // Kuhaon diri ang inventory details, apil category, supplier, stock, ug inventory value.
    public function get_inventory_report() {
        $this->db->select('p.product_code, p.product_name, c.category_name, s.supplier_name, p.unit, p.stock, p.cost_price, (p.stock * p.cost_price) AS inventory_value');
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->join('suppliers s', 's.id = p.supplier_id', 'left');
        $this->db->order_by('p.product_name', 'ASC');
        return $this->db->get()->result_array();
    }
    // Stock movement report ni; apil transaction, product, supplier, ug user info para complete ra.
    public function get_stock_movement_report($type = NULL) {
        $this->db->select('t.transaction_no, t.type, p.product_code, p.product_name, i.quantity, i.cost_price, s.supplier_name, u.username, t.remarks, t.created_at');
        $this->db->from('stock_transactions t');
        $this->db->join('stock_transaction_items i', 'i.transaction_id = t.id');
        $this->db->join('products p', 'p.id = i.product_id');
        $this->db->join('suppliers s', 's.id = t.supplier_id', 'left');
        $this->db->join('users u', 'u.id = t.created_by');
        if ($type) {
            $this->db->where('t.type', $type);
        }
        $this->db->order_by('t.created_at', 'DESC');
        return $this->db->get()->result_array();
    }
    // Low-stock report ni; makita dayon unsay kulang based sa reorder level.
    public function get_low_stock_report() {
        $this->db->select('p.product_code, p.product_name, c.category_name, p.unit, p.stock, p.reorder_level, (p.reorder_level - p.stock) AS shortage');
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->where('p.stock <= p.reorder_level', NULL, FALSE);
        $this->db->where('p.status', 1);
        $this->db->order_by('p.stock', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_datatable($report, $start, $length, $search, $order_column, $order_dir, $filters = array()) {
        $this->build_datatable_query($report, $search, $filters);
        $this->select_report_columns($report);
        if ($order_column) {
            $this->db->order_by($order_column, $order_dir);
        }
        $this->db->limit((int) $length, (int) $start);
        return $this->db->get()->result_array();
    }

    public function get_export_rows($report, $search = '', $filters = array()) {
        $this->build_datatable_query($report, trim((string) $search), (array) $filters);
        $this->select_report_columns($report);

        if ($report === 'inventory' || $report === 'valuation') {
            $this->db->order_by('p.product_name', 'ASC');
        } elseif ($report === 'low-stock') {
            $this->db->order_by('p.stock', 'ASC');
            $this->db->order_by('p.product_name', 'ASC');
        } else {
            $this->db->order_by('t.created_at', 'DESC');
            $this->db->order_by('t.id', 'DESC');
        }

        return $this->db->get()->result_array();
    }

    public function count_datatable_total($report) {
        $this->build_datatable_query($report, '');
        return $this->db->count_all_results();
    }

    public function count_datatable_filtered($report, $search, $filters = array()) {
        $this->build_datatable_query($report, $search, $filters);
        return $this->db->count_all_results();
    }

    private function select_report_columns($report) {
        if ($report === 'inventory' || $report === 'valuation') {
            $this->db->select('p.product_code, p.product_name, c.category_name, s.supplier_name, p.unit, p.stock, p.cost_price, (p.stock * p.cost_price) AS inventory_value', FALSE);
            return;
        }

        if ($report === 'low-stock') {
            $this->db->select('p.product_code, p.product_name, c.category_name, p.unit, p.stock, p.reorder_level, (p.reorder_level - p.stock) AS shortage', FALSE);
            return;
        }

        $this->db->select('t.transaction_no, t.type, p.product_code, p.product_name, i.quantity, i.cost_price, s.supplier_name, u.username, t.remarks, t.created_at');
    }

    private function build_datatable_query($report, $search, $filters = array()) {
        if ($report === 'inventory' || $report === 'valuation') {
            $this->db->from('products p');
            $this->db->join('categories c', 'c.id = p.category_id', 'left');
            $this->db->join('suppliers s', 's.id = p.supplier_id', 'left');

            $stock = isset($filters['stock']) ? strtolower((string) $filters['stock']) : '';
            if ($stock === 'out') {
                $this->db->where('p.stock <=', 0);
            } elseif ($stock === 'low') {
                $this->db->where('p.stock >', 0);
                $this->db->where('p.stock <= p.reorder_level', NULL, FALSE);
            } elseif ($stock === 'healthy') {
                $this->db->where('p.stock > p.reorder_level', NULL, FALSE);
            }

            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('p.product_code', $search);
                $this->db->or_like('p.product_name', $search);
                $this->db->or_like('c.category_name', $search);
                $this->db->or_like('s.supplier_name', $search);
                $this->db->or_like('p.unit', $search);
                $this->db->group_end();
            }
            return;
        }

        if ($report === 'low-stock') {
            $this->db->from('products p');
            $this->db->join('categories c', 'c.id = p.category_id', 'left');
            $this->db->where('p.stock <= p.reorder_level', NULL, FALSE);
            $this->db->where('p.status', 1);

            $severity = isset($filters['severity']) ? strtolower((string) $filters['severity']) : '';
            if ($severity === 'out') {
                $this->db->where('p.stock <=', 0);
            } elseif ($severity === 'low') {
                $this->db->where('p.stock >', 0);
            }

            if ($search !== '') {
                $this->db->group_start();
                $this->db->like('p.product_code', $search);
                $this->db->or_like('p.product_name', $search);
                $this->db->or_like('c.category_name', $search);
                $this->db->or_like('p.unit', $search);
                $this->db->group_end();
            }
            return;
        }

        $this->db->from('stock_transactions t');
        $this->db->join('stock_transaction_items i', 'i.transaction_id = t.id');
        $this->db->join('products p', 'p.id = i.product_id');
        $this->db->join('suppliers s', 's.id = t.supplier_id', 'left');
        $this->db->join('users u', 'u.id = t.created_by');

        if ($report === 'stock-in') {
            $this->db->where('t.type', 'stock_in');
        } elseif ($report === 'stock-out') {
            $this->db->where('t.type', 'stock_out');
        } elseif ($report === 'movement') {
            $type = isset($filters['type']) ? strtolower((string) $filters['type']) : '';

            if (in_array($type, array('stock_in', 'stock_out', 'adjustment'), TRUE)) {
                $this->db->where('t.type', $type);
            }
        }

        $period = isset($filters['period']) ? strtolower((string) $filters['period']) : '';

        if ($period === 'today') {
            $this->db->where('t.created_at >=', date('Y-m-d 00:00:00'));
            $this->db->where('t.created_at <=', date('Y-m-d 23:59:59'));
        } elseif ($period === '7_days') {
            $this->db->where('t.created_at >=', date('Y-m-d 00:00:00', strtotime('-6 days')));
        } elseif ($period === '30_days') {
            $this->db->where('t.created_at >=', date('Y-m-d 00:00:00', strtotime('-29 days')));
        }

        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('t.transaction_no', $search);
            $this->db->or_like('t.type', $search);
            $this->db->or_like('p.product_code', $search);
            $this->db->or_like('p.product_name', $search);
            $this->db->or_like('s.supplier_name', $search);
            $this->db->or_like('u.username', $search);
            $this->db->or_like('t.remarks', $search);
            $this->db->or_like('t.created_at', $search);
            $this->db->group_end();
        }
    }
}
