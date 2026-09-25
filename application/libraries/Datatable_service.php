
<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Datatable_service
{
    private $allowed_lengths = array(10, 25, 50, 100);

    private $max_search_length = 100;

    // Shared service ni para request; main caller/integration pangitaa sa application/controllers/ nga nag-render sa server-side tables, so didto tan-awa if mangita ka asa ni gigamit.
    public function request(
        $input,
        $columns,
        $default_order_column = NULL,
        $default_order_dir = 'asc'
    ) {
        $request = $input->get(NULL, TRUE);

        if (!is_array($request)) {
            $request = array();
        }


        $draw = isset($request['draw'])
            ? max(0, (int) $request['draw'])
            : 0;


        $start = isset($request['start'])
            ? max(0, (int) $request['start'])
            : 0;

        $length = isset($request['length'])
            ? (int) $request['length']
            : 10;

        if (!in_array($length, $this->allowed_lengths, TRUE)) {
            $length = 10;
        }


        $search = '';

        if (
            isset($request['search']) &&
            is_array($request['search']) &&
            isset($request['search']['value'])
        ) {
            $search = trim((string) $request['search']['value']);
        }

        if (strlen($search) > $this->max_search_length) {
            $search = substr($search, 0, $this->max_search_length);
        }


        $filters = array();

        if (isset($request['table_filters']) && is_array($request['table_filters'])) {
            foreach ($request['table_filters'] as $key => $value) {
                $safe_key = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $key);

                if ($safe_key === '' || !is_scalar($value)) {
                    continue;
                }

                $safe_value = trim((string) $value);

                if (strlen($safe_value) > 50) {
                    $safe_value = substr($safe_value, 0, 50);
                }

                if ($safe_value !== '') {
                    $filters[$safe_key] = $safe_value;
                }
            }
        }

        $order_column = $default_order_column;

        $order_dir = strtolower((string) $default_order_dir) === 'desc'
            ? 'desc'
            : 'asc';


        if (
            isset($request['order'][0]) &&
            is_array($request['order'][0])
        ) {
            $order_index = isset($request['order'][0]['column'])
                ? (int) $request['order'][0]['column']
                : -1;

            $requested_dir = isset($request['order'][0]['dir'])
                ? strtolower((string) $request['order'][0]['dir'])
                : $order_dir;


            if (
                $order_index >= 0 &&
                isset($columns[$order_index]) &&
                $columns[$order_index] !== NULL
            ) {
                $order_column = $columns[$order_index];

                $order_dir = ($requested_dir === 'desc')
                    ? 'desc'
                    : 'asc';
            }
        }


        return array(
            'draw'         => $draw,
            'start'        => $start,
            'length'       => $length,
            'search'       => $search,
            'filters'      => $filters,
            'order_column' => $order_column,
            'order_dir'    => $order_dir
        );
    }


    // Shared service ni para payload; main caller/integration pangitaa sa application/controllers/ nga nag-render sa server-side tables, so didto tan-awa if mangita ka asa ni gigamit.
    public function payload($draw, $total, $filtered, $rows)
    {
        $total = max(0, (int) $total);
        $filtered = max(0, (int) $filtered);

        $filtered = min($filtered, $total);

        return array(
            'draw'            => max(0, (int) $draw),
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => array_values((array) $rows)
        );
    }
}

