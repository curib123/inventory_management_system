<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Login_attempt_model extends CI_Model {

    const MAX_ATTEMPTS = 5;
    const WINDOW_MINUTES = 10;
    const COOLDOWN_MINUTES = 15;

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function is_locked($username, $ip_address) {
        $keys = $this->attempt_keys($username, $ip_address);
        $conditions = array();
        $bindings = array();

        foreach ($keys as $key) {
            $conditions[] = '(scope_type = ? AND identifier_hash = ?)';
            $bindings[] = $key['scope_type'];
            $bindings[] = $key['identifier_hash'];
        }

        $query = $this->db->query(
            'SELECT 1 FROM login_attempts WHERE locked_until > NOW() AND (' .
            implode(' OR ', $conditions) . ') LIMIT 1',
            $bindings
        );

        return $query->num_rows() > 0;
    }

    public function lockout_status($username, $ip_address) {
        $keys = $this->attempt_keys($username, $ip_address);
        $conditions = array();
        $bindings = array();

        foreach ($keys as $key) {
            $conditions[] = '(scope_type = ? AND identifier_hash = ?)';
            $bindings[] = $key['scope_type'];
            $bindings[] = $key['identifier_hash'];
        }

        $row = $this->db->query(
            'SELECT MAX(locked_until) AS locked_until, ' .
            'COALESCE(MAX(TIMESTAMPDIFF(SECOND, NOW(), locked_until)), 0) AS remaining_seconds ' .
            'FROM login_attempts WHERE locked_until > NOW() AND (' .
            implode(' OR ', $conditions) . ')',
            $bindings
        )->row_array();

        return array(
            'locked_until' => !empty($row['locked_until']) ? $row['locked_until'] : NULL,
            'remaining_seconds' => max(0, (int) (isset($row['remaining_seconds']) ? $row['remaining_seconds'] : 0))
        );
    }

    public function record_failure($username, $ip_address) {
        $keys = $this->attempt_keys($username, $ip_address);

        foreach ($keys as $key) {
            $this->db->query(
                'INSERT INTO login_attempts ' .
                '(scope_type, identifier_hash, attempt_count, window_started_at, locked_until, updated_at) ' .
                'VALUES (?, ?, 1, NOW(), NULL, NOW()) ' .
                'ON DUPLICATE KEY UPDATE ' .
                'locked_until = CASE ' .
                'WHEN window_started_at <= DATE_SUB(NOW(), INTERVAL ' . self::WINDOW_MINUTES . ' MINUTE) THEN NULL ' .
                'WHEN attempt_count >= ' . (self::MAX_ATTEMPTS - 1) . ' THEN DATE_ADD(NOW(), INTERVAL ' . self::COOLDOWN_MINUTES . ' MINUTE) ' .
                'ELSE locked_until END, ' .
                'attempt_count = CASE ' .
                'WHEN window_started_at <= DATE_SUB(NOW(), INTERVAL ' . self::WINDOW_MINUTES . ' MINUTE) THEN 1 ' .
                'ELSE attempt_count + 1 END, ' .
                'window_started_at = CASE ' .
                'WHEN window_started_at <= DATE_SUB(NOW(), INTERVAL ' . self::WINDOW_MINUTES . ' MINUTE) THEN NOW() ' .
                'ELSE window_started_at END, ' .
                'updated_at = NOW()',
                array($key['scope_type'], $key['identifier_hash'])
            );
        }

        $this->db->query(
            'DELETE FROM login_attempts WHERE updated_at < DATE_SUB(NOW(), INTERVAL 1 DAY)'
        );

        return $this->is_locked($username, $ip_address);
    }

    public function clear_attempts($username, $ip_address) {
        $keys = $this->attempt_keys($username, $ip_address);
        $conditions = array();
        $bindings = array();

        foreach ($keys as $key) {
            $conditions[] = '(scope_type = ? AND identifier_hash = ?)';
            $bindings[] = $key['scope_type'];
            $bindings[] = $key['identifier_hash'];
        }

        $this->db->query(
            'DELETE FROM login_attempts WHERE ' . implode(' OR ', $conditions),
            $bindings
        );
    }

    private function attempt_keys($username, $ip_address) {
        return array(
            array(
                'scope_type' => 'username',
                'identifier_hash' => hash('sha256', strtolower(trim((string) $username)))
            ),
            array(
                'scope_type' => 'ip',
                'identifier_hash' => hash('sha256', trim((string) $ip_address))
            )
        );
    }
}
