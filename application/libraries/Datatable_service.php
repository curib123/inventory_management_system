
<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Datatable_service
{
    /**
     * Allowed DataTable page lengths.
     */
    private $allowed_lengths = array(10, 25, 50, 100);

    /**
     * Maximum search string length.
     */
    private $max_search_length = 100;

    /**
     * Parse and sanitize DataTable request.
     *
     * @param CI_Input $input
     * @param array    $columns
     * @param string|null $default_order_column
     * @param string $default_order_dir
     * @return array
     */
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


        /*
         * ---------------------------------------------------------
         * Draw
         * ---------------------------------------------------------
         */
        $draw = isset($request['draw'])
            ? max(0, (int) $request['draw'])
            : 0;


        /*
         * ---------------------------------------------------------
         * Pagination
         * ---------------------------------------------------------
         */
        $start = isset($request['start'])
            ? max(0, (int) $request['start'])
            : 0;

        $length = isset($request['length'])
            ? (int) $request['length']
            : 10;

        /*
         * Only allow predefined page sizes.
         */
        if (!in_array($length, $this->allowed_lengths, TRUE)) {
            $length = 10;
        }


        /*
         * ---------------------------------------------------------
         * Search
         * ---------------------------------------------------------
         */
        $search = '';

        if (
            isset($request['search']) &&
            is_array($request['search']) &&
            isset($request['search']['value'])
        ) {
            $search = trim((string) $request['search']['value']);
        }

        /*
         * Prevent excessively long search requests.
         */
        if (strlen($search) > $this->max_search_length) {
            $search = substr($search, 0, $this->max_search_length);
        }


        /*
         * ---------------------------------------------------------
         * Ordering
         * ---------------------------------------------------------
         */
        $order_column = $default_order_column;

        $order_dir = strtolower((string) $default_order_dir) === 'desc'
            ? 'desc'
            : 'asc';


        /*
         * DataTables sends:
         *
         * order[0][column]
         * order[0][dir]
         *
         * We NEVER trust the column name from the browser.
         *
         * The column index is mapped against the whitelist
         * supplied by the controller.
         */
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


            /*
             * Validate column index.
             */
            if (
                $order_index >= 0 &&
                isset($columns[$order_index]) &&
                $columns[$order_index] !== NULL
            ) {
                $order_column = $columns[$order_index];

                /*
                 * Only ASC/DESC are allowed.
                 */
                $order_dir = ($requested_dir === 'desc')
                    ? 'desc'
                    : 'asc';
            }
        }


        /*
         * ---------------------------------------------------------
         * Response
         * ---------------------------------------------------------
         */
        return array(
            'draw'         => $draw,
            'start'        => $start,
            'length'       => $length,
            'search'       => $search,
            'order_column' => $order_column,
            'order_dir'    => $order_dir
        );
    }


    /**
     * Create DataTable response payload.
     *
     * @param int   $draw
     * @param int   $total
     * @param int   $filtered
     * @param array $rows
     * @return array
     */
    public function payload($draw, $total, $filtered, $rows)
    {
        $total = max(0, (int) $total);
        $filtered = max(0, (int) $filtered);

        /*
         * Filtered records can never logically exceed
         * the total number of records.
         */
        $filtered = min($filtered, $total);

        return array(
            'draw'            => max(0, (int) $draw),
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => array_values((array) $rows)
        );
    }
}

