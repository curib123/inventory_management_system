<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends CI_Controller {

    // reports controller to handle report generation and export functionality
    private $report_definitions = array(
        'inventory' => array('title' => 'Inventory Report', 'method' => 'get_inventory_report'),
        'stock-in' => array('title' => 'Stock-In Report', 'method' => 'get_stock_movement_report', 'type' => 'stock_in'),
        'stock-out' => array('title' => 'Stock-Out Report', 'method' => 'get_stock_movement_report', 'type' => 'stock_out'),
        'movement' => array('title' => 'Stock Movement Report', 'method' => 'get_stock_movement_report'),
        'low-stock' => array('title' => 'Low-Stock Report', 'method' => 'get_low_stock_report'),
        'valuation' => array('title' => 'Inventory Valuation', 'method' => 'get_inventory_report')
    );
    // Function to initialize the controller, load necessary libraries, helpers, and models, and check if the user is logged in
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper(array('url', 'html'));

        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }

        $this->load->model('User_model');
        $this->load->library('Report_rules');
        $this->require_permission('view_reports');
        $this->load->model('Report_model');
    }
    // Function to check if the user has the required permission in the index root level
    public function index() {
        $this->show_report('inventory');
    }
    // display the inventory report
    public function inventory() {
        $this->show_report('inventory');
    }
    // display the stock-in report
    public function stock_in() {
        $this->show_report('stock-in');
    }
    // display the stock-out report
    public function stock_out() {
        $this->show_report('stock-out');
    }
    // display the stock movement report
    public function movement() {
        $this->show_report('movement');
    }
    //  display the low stock movement report
    public function low_stock() {
        $this->show_report('low-stock');
    }
    //  display the valuation report
    public function valuation() {
        $this->show_report('valuation');
    }
    // export the csv format report
    public function export($report, $format = 'csv') {
        $definition = $this->get_definition($report);
        $rows = $this->get_rows($definition);
        $format = strtolower($format);

        if (!$this->Report_rules->export_format_is_supported($format)) {
            show_error('Unsupported export format.', 400, 'Export Error');
        }

        if ($format === 'csv') {
            $this->export_csv($definition['title'], $rows);
        } elseif ($format === 'xlsx') {
            $this->export_xlsx($definition['title'], $rows);
        } elseif ($format === 'pdf') {
            $this->export_pdf($definition['title'], $rows);
        }
    }
    //  to show /display report
    private function show_report($report) {
        $definition = $this->get_definition($report);
        $data['report_title'] = $definition['title'];
        $data['report_key'] = $report;
        $data['rows'] = $this->get_rows($definition);
        $data['page_title'] = $definition['title'];

        $this->load->view('templates/header', $data);
        $this->load->view('reports/index', $data);
        $this->load->view('templates/footer');
    }
    // to  get report definations
    private function get_definition($report) {
        try {
            $definition = $this->Report_rules->get($report);
        } catch (InvalidArgumentException $exception) {
            show_404();
        }

        $definition['method'] = $this->report_definitions[$report]['method'];
        return $definition;
    }
    // to get the rows in report model
    private function get_rows($definition) {
        $type = isset($definition['type']) ? $definition['type'] : NULL;
        return $this->Report_model->{$definition['method']}($type);
    }
    //to export the report in csv format
    private function export_csv($title, $rows) {
        $filename = url_title($title, '-', TRUE) . '-' . date('Y-m-d') . '.csv';
        $this->output->set_content_type('text/csv');
        $this->output->set_header('Content-Disposition: attachment; filename="' . $filename . '"');

        $handle = fopen('php://output', 'w');
        if (!empty($rows)) {
            fputcsv($handle, array_keys($rows[0]));
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
        }
        fclose($handle);
        exit;
    }
    //to export the report in excel format using PhpSpreadsheet
    private function export_xlsx($title, $rows) {
        $autoload = FCPATH . 'vendor/autoload.php';
        if (!is_file($autoload)) {
            show_error('Composer dependencies are not installed.', 500, 'Export Error');
        }
        require_once $autoload;

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($title, 0, 31));
        $row_number = 1;

        if (!empty($rows)) {
            $headers = array_keys($rows[0]);
            foreach ($headers as $column => $header) {
                $sheet->setCellValueByColumnAndRow($column + 1, $row_number, $header);
            }
            $row_number++;
            foreach ($rows as $row) {
                foreach (array_values($row) as $column => $value) {
                    $sheet->setCellValueByColumnAndRow($column + 1, $row_number, $value);
                }
                $row_number++;
            }
            foreach (range(1, count($headers)) as $column) {
                $sheet->getColumnDimensionByColumn($column)->setAutoSize(TRUE);
            }
        }

        $filename = url_title($title, '-', TRUE) . '-' . date('Y-m-d') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save('php://output');
        exit;
    }
    //to export the report in pdf using  Dompdf
    private function export_pdf($title, $rows) {
        $autoload = FCPATH . 'vendor/autoload.php';
        if (!is_file($autoload)) {
            show_error('Composer dependencies are not installed.', 500, 'Export Error');
        }
        require_once $autoload;

        $html = $this->load->view('reports/export_pdf', array(
            'report_title' => $title,
            'rows' => $rows
        ), TRUE);
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream(url_title($title, '-', TRUE) . '-' . date('Y-m-d') . '.pdf', array('Attachment' => TRUE));
        exit;
    }
    // fucntion to for checking require permission
    private function require_permission($permission_name) {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id || !$this->User_model->has_permission($user_id, $permission_name)) {
            show_error('You do not have permission to access this page.', 403, 'Access Denied');
        }
    }
}
