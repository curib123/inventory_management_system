<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Datatable_service {

    private $allowed_lengths = array(10, 25, 50, 100);

    public function request($input, $columns, $default_order_column = NULL, $default_order_dir = 'asc') {
        $request = $input->get(NULL, TRUE);
        $request = is_array($request) ? $request : array();

        $draw = isset($request['draw']) ? max(0, (int) $request['draw']) : 0;
        $start = isset($request['start']) ? max(0, (int) $request['start']) : 0;

        $length = isset($request['length']) ? (int) $request['length'] : 10;
        if (!in_array($length, $this->allowed_lengths, TRUE)) {
            $length = 10;
        }

        $search = '';
        if (isset($request['search']) && is_array($request['search']) && isset($request['search']['value'])) {
            $search = trim((string) $request['search']['value']);
        }

        $order_column = $default_order_column;
        $order_dir = strtolower((string) $default_order_dir) === 'desc' ? 'desc' : 'asc';

        if (isset($request['order'][0]) && is_array($request['order'][0])) {
            $order_index = isset($request['order'][0]['column']) ? (int) $request['order'][0]['column'] : -1;
            $requested_dir = isset($request['order'][0]['dir']) ? strtolower((string) $request['order'][0]['dir']) : $order_dir;

            if (isset($columns[$order_index]) && $columns[$order_index] !== NULL) {
                $order_column = $columns[$order_index];
                $order_dir = $requested_dir === 'desc' ? 'desc' : 'asc';
            }
        }

        return array(
            'draw' => $draw,
            'start' => $start,
            'length' => $length,
            'search' => $search,
            'order_column' => $order_column,
            'order_dir' => $order_dir
        );
    }

    public function payload($draw, $total, $filtered, $rows) {
        return array(
            'draw' => (int) $draw,
            'recordsTotal' => (int) $total,
            'recordsFiltered' => (int) $filtered,
            'data' => array_values((array) $rows)
        );
    }
}
