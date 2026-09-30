<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Activity_log_model extends CI_Model {

    // Setup ni sa Activity_log_model; CodeIgniter mo-run ani when Activity_logs controller loads the audit page.
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // Data helper ni para activity log table rows; filtering stays server-side para scalable ang audit history.
    public function get_datatable($start, $length, $search, $order_column, $order_dir, $filters = array()) {
        $this->build_datatable_query($search, $filters);
        $this->db->select(
            'l.id, l.user_id, l.action, l.description, l.ip_address, l.created_at, ' .
            'u.username, u.first_name, u.last_name'
        );

        if ($order_column) {
            $this->db->order_by($order_column, $order_dir);
        }

        $this->db->order_by('l.id', 'DESC');
        $this->db->limit((int) $length, (int) $start);

        return $this->db->get()->result();
    }

    // Data helper ni para total audit records before search/filter.
    public function count_all() {
        return $this->db->count_all('activity_logs');
    }

    // Data helper ni para filtered audit count; same query rules sa visible table.
    public function count_datatable_filtered($search, $filters = array()) {
        $this->build_datatable_query($search, $filters);
        return $this->db->count_all_results();
    }

    // Filter helper ni para users nga naa gyud activity, para concise ang audit filter dropdown.
    public function get_filter_users() {
        $this->db->distinct();
        $this->db->select('u.id, u.username, u.first_name, u.last_name');
        $this->db->from('activity_logs l');
        $this->db->join('users u', 'u.id = l.user_id');
        $this->db->order_by('u.first_name', 'ASC');
        $this->db->order_by('u.last_name', 'ASC');

        return $this->db->get()->result();
    }

    // Filter helper ni para actual action values stored in the audit table.
    public function get_filter_actions() {
        $this->db->distinct();
        $this->db->select('action');
        $this->db->from('activity_logs');
        $this->db->where('action !=', '');
        $this->db->order_by('action', 'ASC');

        return $this->db->get()->result();
    }

    // Query builder ni para user/action/date filters ug full audit search.
    private function build_datatable_query($search, $filters = array()) {
        $this->db->from('activity_logs l');
        $this->db->join('users u', 'u.id = l.user_id');

        $user_id = isset($filters['user']) ? (int) $filters['user'] : 0;

        if ($user_id > 0) {
            $this->db->where('l.user_id', $user_id);
        }

        $action = isset($filters['action']) ? trim((string) $filters['action']) : '';

        if ($action !== '') {
            $this->db->where('l.action', $action);
        }

        $this->apply_period_filter('l.created_at', $filters);

        $search = trim((string) $search);

        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('l.action', $search);
            $this->db->or_like('l.description', $search);
            $this->db->or_like('l.ip_address', $search);
            $this->db->or_like('u.username', $search);
            $this->db->or_like('u.first_name', $search);
            $this->db->or_like('u.last_name', $search);
            $this->db->or_like('l.created_at', $search);
            $this->db->group_end();
        }
    }

    // Date helper ni para same presets/custom range behavior as report and stock tables.
    private function apply_period_filter($column, $filters) {
        $period = isset($filters['period']) ? strtolower(trim((string) $filters['period'])) : '';

        if ($period === 'today') {
            $this->db->where($column . ' >=', date('Y-m-d 00:00:00'));
            $this->db->where($column . ' <=', date('Y-m-d 23:59:59'));
            return;
        }

        if ($period === '7_days') {
            $this->db->where($column . ' >=', date('Y-m-d 00:00:00', strtotime('-6 days')));
            return;
        }

        if ($period === '30_days') {
            $this->db->where($column . ' >=', date('Y-m-d 00:00:00', strtotime('-29 days')));
            return;
        }

        if ($period !== 'custom') {
            return;
        }

        $from = $this->normalize_date(isset($filters['date_from']) ? $filters['date_from'] : '');
        $to = $this->normalize_date(isset($filters['date_to']) ? $filters['date_to'] : '');

        if ($from === NULL || $to === NULL || $from > $to) {
            return;
        }

        $this->db->where($column . ' >=', $from . ' 00:00:00');
        $this->db->where($column . ' <=', $to . ' 23:59:59');
    }

    // Validation helper ni para malformed dates dili maapil sa SQL conditions.
    private function normalize_date($value) {
        $value = trim((string) $value);

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return NULL;
        }

        $date = DateTime::createFromFormat('!Y-m-d', $value);
        $errors = DateTime::getLastErrors();

        if ($date === FALSE || ($errors !== FALSE && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            return NULL;
        }

        return $date->format('Y-m-d');
    }
}
