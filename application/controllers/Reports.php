<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends CI_Controller {

    private $report_definitions = array(
        'inventory' => array('title' => 'Inventory Report', 'method' => 'get_inventory_report'),
        'stock-in' => array('title' => 'Stock-In Report', 'method' => 'get_stock_movement_report', 'type' => 'stock_in'),
        'stock-out' => array('title' => 'Stock-Out Report', 'method' => 'get_stock_movement_report', 'type' => 'stock_out'),
        'movement' => array('title' => 'Stock Movement Report', 'method' => 'get_stock_movement_report'),
        'low-stock' => array('title' => 'Low-Stock Report', 'method' => 'get_low_stock_report'),
        'valuation' => array('title' => 'Inventory Valuation', 'method' => 'get_inventory_report')
    );

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

    public function index() { $this->show_report('inventory'); }
    public function inventory() { $this->show_report('inventory'); }
    public function stock_in() { $this->show_report('stock-in'); }
    public function stock_out() { $this->show_report('stock-out'); }
    public function movement() { $this->show_report('movement'); }
    public function low_stock() { $this->show_report('low-stock'); }
    public function valuation() { $this->show_report('valuation'); }

    public function export($report, $format = 'csv') {
        $format = strtolower((string) $format);
        if (!$this->report_rules->export_format_is_supported($format)) {
            show_error('Unsupported export format.', 400, 'Export Error');
        }

        $definition = $this->get_definition($report);
        $rows = $this->get_rows($definition);

        if ($format === 'csv') {
            $this->export_csv($definition['title'], $rows);
            return;
        }
        if ($format === 'xlsx') {
            $this->export_xlsx($definition['title'], $rows);
            return;
        }
        $this->export_pdf($definition['title'], $rows);
    }

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

    private function get_definition($report) {
        try {
            $definition = $this->report_rules->get($report);
        } catch (InvalidArgumentException $exception) {
            show_404();
            exit;
        }
        if (!isset($this->report_definitions[$report])) {
            show_404();
            exit;
        }
        $definition['method'] = $this->report_definitions[$report]['method'];
        return $definition;
    }

    private function get_rows($definition) {
        $method = $definition['method'];
        if (!method_exists($this->Report_model, $method)) {
            show_error('Report method is unavailable.', 500, 'Report Error');
        }
        if (isset($definition['type']) && $definition['type'] !== NULL) {
            return $this->Report_model->{$method}($definition['type']);
        }
        return $this->Report_model->{$method}();
    }

    private function export_csv($title, $rows) {
        $filename = url_title($title, '-', TRUE) . '-' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $handle = fopen('php://output', 'w');
        if ($handle === FALSE) {
            show_error('Unable to open CSV output.', 500, 'Export Error');
        }
        if (!empty($rows)) {
            fputcsv($handle, array_keys($rows[0]));
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
        }
        fclose($handle);
        exit;
    }

    private function export_xlsx($title, $rows) {
        $this->load_composer();
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($title, 0, 31));
        $row_number = 1;

        if (!empty($rows)) {
            $headers = array_keys($rows[0]);
            foreach ($headers as $column => $header) {
                $column_letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($column + 1);
                $sheet->setCellValue($column_letter . $row_number, $header);
            }
            $row_number++;
            foreach ($rows as $row) {
                foreach (array_values($row) as $column => $value) {
                    $column_letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($column + 1);
                    $sheet->setCellValue($column_letter . $row_number, $value);
                }
                $row_number++;
            }
            foreach (range(1, count($headers)) as $column) {
                $column_letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($column);
                $sheet->getColumnDimension($column_letter)->setAutoSize(TRUE);
            }
        }

        $filename = url_title($title, '-', TRUE) . '-' . date('Y-m-d') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save('php://output');
        exit;
    }

    private function export_pdf($title, $rows) {
        $this->load_composer();
        $html = $this->load->view('reports/export_pdf', array('report_title' => $title, 'rows' => $rows), TRUE);
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream(url_title($title, '-', TRUE) . '-' . date('Y-m-d') . '.pdf', array('Attachment' => TRUE));
        exit;
    }

    private function load_composer() {
        $autoload = FCPATH . 'vendor/autoload.php';
        if (!is_file($autoload)) {
            show_error('Composer dependencies are not installed. Run composer install in the project root.', 500, 'Export Error');
        }
        require_once $autoload;
    }

    private function require_permission($permission_name) {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id || !$this->User_model->has_permission($user_id, $permission_name)) {
            show_error('You do not have permission to access this page.', 403, 'Access Denied');
        }
    }
}
