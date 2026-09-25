<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper(array('url', 'html'));
        $this->load->library('Datatable_service');

        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }

        $this->load->model('User_model');
        $this->load->library('Report_rules');
        $this->require_permission('reports.view');
        $this->load->model('Report_model');
    }

    public function index() { $this->show_report('inventory'); }
    public function inventory() { $this->show_report('inventory'); }
    public function stock_in() { $this->show_report('stock-in'); }
    public function stock_out() { $this->show_report('stock-out'); }
    public function movement() { $this->show_report('movement'); }
    public function low_stock() { $this->show_report('low-stock'); }
    public function valuation() { $this->show_report('valuation'); }

    public function datatable($report) {
        $this->get_definition($report);

        $order_columns = $this->get_report_order_columns($report);
        $default_order = $this->get_report_default_order($report);
        $request = $this->datatable_service->request(
            $this->input,
            $order_columns,
            $default_order['column'],
            $default_order['dir']
        );

        $rows = $this->Report_model->get_datatable(
            $report,
            $request['start'],
            $request['length'],
            $request['search'],
            $request['order_column'],
            $request['order_dir'],
            $request['filters']
        );

        $fields = array_keys($this->get_report_columns($report));
        $data_rows = array();

        foreach ($rows as $row) {
            $values = array();

            foreach ($fields as $field) {
                $values[] = html_escape(isset($row[$field]) ? (string) $row[$field] : '');
            }

            $data_rows[] = $values;
        }

        $payload = $this->datatable_service->payload(
            $request['draw'],
            $this->Report_model->count_datatable_total($report),
            $this->Report_model->count_datatable_filtered($report, $request['search'], $request['filters']),
            $data_rows
        );

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }

    public function export($report, $format = 'csv') {
        $this->require_permission('reports.export');

        $format = strtolower((string) $format);

        if (!$this->report_rules->export_format_is_supported($format)) {
            show_error(
                'That export format is not supported. Use CSV, Excel, or PDF.',
                400,
                'Export Format Not Supported'
            );
        }

        $definition = $this->get_definition($report);

        try {
            $rows = $this->get_rows($definition);
            $columns = $this->get_report_columns($report);
            $meta = $this->build_report_meta($report, $definition['title'], $rows);

            if ($format === 'csv') {
                $this->export_csv($definition['title'], $columns, $rows, $meta);
                return;
            }

            if ($format === 'xlsx') {
                $this->export_xlsx($definition['title'], $columns, $rows, $meta);
                return;
            }

            $this->export_pdf($definition['title'], $columns, $rows, $meta);
        } catch (Throwable $exception) {
            $this->handle_export_failure($exception, $definition['title']);
        }
    }

    private function show_report($report) {
        $definition = $this->get_definition($report);
        $data['report_title'] = $definition['title'];
        $data['report_key'] = $report;
        $data['columns'] = $this->get_report_columns($report);
        $data['page_title'] = $definition['title'];

        $this->load->view('templates/header', $data);
        $this->load->view('reports/index', $data);
        $this->load->view('templates/footer');
    }

    private function get_report_columns($report) {
        if ($report === 'inventory' || $report === 'valuation') {
            return array(
                'product_code' => 'Product Code',
                'product_name' => 'Product Name',
                'category_name' => 'Category',
                'supplier_name' => 'Supplier',
                'unit' => 'Unit',
                'stock' => 'Stock',
                'cost_price' => 'Cost Price',
                'inventory_value' => 'Inventory Value'
            );
        }

        if ($report === 'low-stock') {
            return array(
                'product_code' => 'Product Code',
                'product_name' => 'Product Name',
                'category_name' => 'Category',
                'unit' => 'Unit',
                'stock' => 'Stock',
                'reorder_level' => 'Reorder Level',
                'shortage' => 'Shortage'
            );
        }

        return array(
            'transaction_no' => 'Transaction No.',
            'type' => 'Type',
            'product_code' => 'Product Code',
            'product_name' => 'Product Name',
            'quantity' => 'Quantity',
            'cost_price' => 'Cost Price',
            'supplier_name' => 'Supplier',
            'username' => 'Processed By',
            'remarks' => 'Remarks',
            'created_at' => 'Date'
        );
    }

    private function get_report_order_columns($report) {
        if ($report === 'inventory' || $report === 'valuation') {
            return array(
                'p.product_code',
                'p.product_name',
                'c.category_name',
                's.supplier_name',
                'p.unit',
                'p.stock',
                'p.cost_price',
                'inventory_value'
            );
        }

        if ($report === 'low-stock') {
            return array(
                'p.product_code',
                'p.product_name',
                'c.category_name',
                'p.unit',
                'p.stock',
                'p.reorder_level',
                'shortage'
            );
        }

        return array(
            't.transaction_no',
            't.type',
            'p.product_code',
            'p.product_name',
            'i.quantity',
            'i.cost_price',
            's.supplier_name',
            'u.username',
            't.remarks',
            't.created_at'
        );
    }

    private function get_report_default_order($report) {
        if ($report === 'inventory' || $report === 'valuation') {
            return array('column' => 'p.product_name', 'dir' => 'asc');
        }

        if ($report === 'low-stock') {
            return array('column' => 'p.stock', 'dir' => 'asc');
        }

        return array('column' => 't.created_at', 'dir' => 'desc');
    }

    private function get_definition($report) {
        try {
            return $this->report_rules->get($report);
        } catch (InvalidArgumentException $exception) {
            show_404();
            exit;
        }
    }

    private function get_rows($definition) {
        $method = $definition['method'];

        if (!method_exists($this->Report_model, $method)) {
            throw new RuntimeException('The configured report data method is unavailable.');
        }

        if (isset($definition['type']) && $definition['type'] !== NULL) {
            return $this->Report_model->{$method}($definition['type']);
        }

        return $this->Report_model->{$method}();
    }

    private function build_report_meta($report, $title, $rows) {
        return array(
            'system_name' => 'Inventory Management System',
            'report_key' => $report,
            'report_title' => $title,
            'generated_at' => date('F j, Y g:i A'),
            'prepared_by' => (string) $this->session->userdata('username'),
            'record_count' => count($rows),
            'summary' => $this->build_report_summary($report, $rows)
        );
    }

    private function build_report_summary($report, $rows) {
        $summary = array('Records' => number_format(count($rows)));

        if ($report === 'inventory' || $report === 'valuation') {
            $total_stock = 0;
            $total_value = 0.0;

            foreach ($rows as $row) {
                $total_stock += isset($row['stock']) ? (int) $row['stock'] : 0;
                $total_value += isset($row['inventory_value']) ? (float) $row['inventory_value'] : 0;
            }

            $summary['Total Stock'] = number_format($total_stock);
            $summary['Inventory Value'] = number_format($total_value, 2);
            return $summary;
        }

        if ($report === 'low-stock') {
            $shortage = 0;

            foreach ($rows as $row) {
                $shortage += isset($row['shortage']) ? (int) $row['shortage'] : 0;
            }

            $summary['Total Shortage'] = number_format($shortage);
            return $summary;
        }

        $total_quantity = 0;
        $movement_value = 0.0;

        foreach ($rows as $row) {
            $quantity = isset($row['quantity']) ? (int) $row['quantity'] : 0;
            $cost_price = isset($row['cost_price']) ? (float) $row['cost_price'] : 0;
            $total_quantity += $quantity;
            $movement_value += $quantity * $cost_price;
        }

        $summary['Total Quantity'] = number_format($total_quantity);
        $summary['Movement Value'] = number_format($movement_value, 2);

        return $summary;
    }

private function export_csv($title, $columns, $rows, $meta)
{
    $filename = $this->report_filename($title, 'csv');

    $this->prepare_download_output();

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('X-Content-Type-Options: nosniff');

    $handle = fopen('php://output', 'w');

    if ($handle === false) {
        throw new RuntimeException('Unable to open the CSV output stream.');
    }

    try {
        // UTF-8 BOM for Microsoft Excel compatibility.
        fwrite($handle, "\xEF\xBB\xBF");

        $this->write_csv_metadata($handle, $title, $meta);
        $this->write_csv_header($handle, $columns);
        $this->write_csv_data($handle, $columns, $rows);
    } finally {
        fclose($handle);
    }

    exit;
}

private function write_csv_metadata($handle, $title, $meta)
{
    // CSV cannot store visual styling, so keep a clean business-report structure.
    $this->write_csv_row($handle, array('REPORT INFORMATION'));
    $this->write_csv_row($handle, array('System', $meta['system_name'] ?? ''));
    $this->write_csv_row($handle, array('Report', $title));
    $this->write_csv_row($handle, array('Generated', $meta['generated_at'] ?? ''));
    $this->write_csv_row($handle, array(
        'Prepared By',
        !empty($meta['prepared_by']) ? $meta['prepared_by'] : 'System User'
    ));
    $this->write_csv_row($handle, array(
        'Record Count',
        isset($meta['record_count']) ? (int) $meta['record_count'] : 0
    ));

    $this->write_csv_row($handle, array());
    $this->write_csv_row($handle, array('SUMMARY'));

    foreach (($meta['summary'] ?? array()) as $label => $value) {
        $this->write_csv_row($handle, array(
            $label,
            $this->csv_safe_value($value)
        ));
    }

    $this->write_csv_row($handle, array());
    $this->write_csv_row($handle, array('DATA'));
}

private function write_csv_header($handle, $columns)
{
    $this->write_csv_row(
        $handle,
        array_values($columns)
    );
}

private function write_csv_data($handle, $columns, $rows)
{
    foreach ($rows as $row) {
        $values = array();

        foreach ($columns as $field => $label) {
            $value = array_key_exists($field, $row) ? $row[$field] : '';

            if ($field === 'type' && $value !== '') {
                $value = ucwords(str_replace('_', ' ', (string) $value));
            }

            if ($field === 'created_at' && trim((string) $value) !== '') {
                $timestamp = strtotime((string) $value);

                if ($timestamp !== FALSE) {
                    $value = date('Y-m-d H:i', $timestamp);
                }
            }

            $values[] = $this->csv_safe_value($value);
        }

        $this->write_csv_row($handle, $values);
    }
}

private function write_csv_row($handle, $values)
{
    if (fputcsv($handle, $values, ',', '"', '') === false) {
        throw new RuntimeException('Unable to write the CSV report.');
    }
}

private function csv_safe_value($value)
{
    if ($value === null) {
        return '';
    }

    if (is_int($value) || is_float($value)) {
        return $value;
    }

    $value = (string) $value;
    $trimmed = ltrim($value);

    /*
     * Prevent CSV/Excel formula injection.
     *
     * Values beginning with =, +, -, or @ can be interpreted
     * as formulas by spreadsheet applications.
     */
    if ($trimmed !== '') {
        $firstCharacter = $trimmed[0];

        if (in_array($firstCharacter, array('=', '+', '-', '@'), true)) {
            return "'" . $value;
        }
    }

    return $value;
}


    private function export_xlsx($title, $columns, $rows, $meta) {
        $this->load_composer();

        if (!class_exists('\\PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
            throw new RuntimeException('PhpSpreadsheet is unavailable. Run composer install in the project root.');
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator($meta['prepared_by'] ?: $meta['system_name'])
            ->setTitle($title)
            ->setSubject('Inventory business report')
            ->setDescription('Professional report export generated by ' . $meta['system_name']);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($title, 0, 31));
        $sheet->getSheetView()->setZoomScale(90);

        $column_count = max(1, count($columns));
        $last_column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($column_count);
        $header_row = 6;
        $data_start_row = 7;

        $sheet->mergeCells('A1:' . $last_column . '1');
        $sheet->setCellValue('A1', $meta['system_name']);

        $sheet->mergeCells('A2:' . $last_column . '2');
        $sheet->setCellValue('A2', $title);

        $sheet->mergeCells('A3:' . $last_column . '3');
        $sheet->setCellValue(
            'A3',
            'Generated: ' . $meta['generated_at'] .
            '   |   Prepared by: ' . ($meta['prepared_by'] ?: 'System User')
        );

        $summary_parts = array();

        foreach ($meta['summary'] as $label => $value) {
            $summary_parts[] = $label . ': ' . $value;
        }

        $sheet->mergeCells('A4:' . $last_column . '4');
        $sheet->setCellValue('A4', implode('   |   ', $summary_parts));

        foreach ($columns as $field => $label) {
            $column_index = array_search($field, array_keys($columns), TRUE) + 1;
            $column_letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($column_index);
            $sheet->setCellValue($column_letter . $header_row, $label);
        }

        $row_number = $data_start_row;

        foreach ($rows as $row) {
            $column_index = 1;

            foreach ($columns as $field => $label) {
                $coordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($column_index) . $row_number;
                $value = array_key_exists($field, $row) ? $row[$field] : '';

                $this->set_excel_cell_value($sheet, $coordinate, $field, $value);
                $column_index++;
            }

            $row_number++;
        }

        if (empty($rows)) {
            $sheet->mergeCells('A7:' . $last_column . '7');
            $sheet->setCellValue('A7', 'No report data found for this report.');
            $sheet->getStyle('A7')->getFont()->setItalic(TRUE);
            $sheet->getStyle('A7')->getFont()->getColor()->setARGB('64748B');
            $row_number = 8;
        }

        $last_data_row = max($header_row, $row_number - 1);

        $sheet->getStyle('A1:' . $last_column . '1')->applyFromArray(array(
            'font' => array(
                'bold' => TRUE,
                'size' => 16,
                'color' => array('argb' => 'FFFFFF')
            ),
            'fill' => array(
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => array('argb' => '0F172A')
            ),
            'alignment' => array(
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            )
        ));

        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getStyle('A2:' . $last_column . '2')->getFont()->setBold(TRUE)->setSize(13);
        $sheet->getStyle('A3:' . $last_column . '4')->getFont()->getColor()->setARGB('64748B');
        $sheet->getStyle('A3:' . $last_column . '4')->getFont()->setSize(9);

        $sheet->getStyle('A' . $header_row . ':' . $last_column . $header_row)->applyFromArray(array(
            'font' => array(
                'bold' => TRUE,
                'color' => array('argb' => 'FFFFFF')
            ),
            'fill' => array(
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => array('argb' => '2563EB')
            ),
            'alignment' => array(
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            )
        ));

        $sheet->getRowDimension($header_row)->setRowHeight(24);
        $sheet->getStyle('A' . $header_row . ':' . $last_column . $header_row)
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('A' . $header_row . ':' . $last_column . $last_data_row)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
            ->getColor()
            ->setARGB('CBD5E1');

        if (!empty($rows)) {
            $data_range = 'A' . $data_start_row . ':' . $last_column . $last_data_row;

            $sheet->getStyle($data_range)->applyFromArray(array(
                'borders' => array(
                    'allBorders' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => array('argb' => 'CBD5E1')
                    )
                ),
                'alignment' => array(
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP
                )
            ));

            for ($row = $data_start_row; $row <= $last_data_row; $row++) {
                if (($row - $data_start_row) % 2 === 1) {
                    $sheet->getStyle('A' . $row . ':' . $last_column . $row)
                        ->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('F8FAFC');
                }
            }
        }

        $sheet->getStyle('A' . $header_row . ':' . $last_column . $last_data_row)
            ->getAlignment()
            ->setWrapText(TRUE);

        $column_index = 1;

        foreach ($columns as $field => $label) {
            $column_letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($column_index);
            $sheet->getColumnDimension($column_letter)->setWidth($this->excel_column_width($field));

            if (in_array($field, array('stock', 'quantity', 'reorder_level', 'shortage', 'cost_price', 'inventory_value'), TRUE)) {
                $sheet->getStyle($column_letter . $data_start_row . ':' . $column_letter . $last_data_row)
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            }

            $column_index++;
        }

        $sheet->freezePane('A' . $data_start_row);
        $sheet->setAutoFilter('A' . $header_row . ':' . $last_column . $header_row);
        $sheet->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
            ->setFitToPage(TRUE)
            ->setFitToWidth(1)
            ->setFitToHeight(0);

        $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, $header_row);
        $sheet->getPageMargins()
            ->setTop(0.45)
            ->setRight(0.35)
            ->setBottom(0.55)
            ->setLeft(0.35);

        $sheet->getHeaderFooter()->setOddFooter(
            '&L' . $meta['system_name'] . '&CPage &P of &N&R' . date('Y-m-d')
        );
        $sheet->getPageSetup()->setPrintArea('A1:' . $last_column . $last_data_row);

        $filename = $this->report_filename($title, 'xlsx');
        $temp_file = tempnam(sys_get_temp_dir(), 'inventory-report-');

        if ($temp_file === FALSE) {
            throw new RuntimeException('Unable to create a temporary Excel report file.');
        }

        try {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->setPreCalculateFormulas(FALSE);
            $writer->save($temp_file);

            clearstatcache(TRUE, $temp_file);
            $file_size = filesize($temp_file);

            if ($file_size === FALSE || $file_size <= 0) {
                throw new RuntimeException('The Excel report file could not be written correctly.');
            }

            $this->prepare_download_output();
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . $file_size);
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('X-Content-Type-Options: nosniff');

            readfile($temp_file);
        } finally {
            if (is_file($temp_file)) {
                @unlink($temp_file);
            }

            $spreadsheet->disconnectWorksheets();
        }

        exit;
    }

    private function set_excel_cell_value($sheet, $coordinate, $field, $value) {
        $integer_fields = array('stock', 'reorder_level', 'shortage', 'quantity');
        $decimal_fields = array('cost_price', 'inventory_value');
        $identifier_fields = array('product_code', 'transaction_no');

        if ($value === NULL) {
            $value = '';
        }

        if (in_array($field, $integer_fields, TRUE) && is_numeric($value)) {
            $sheet->setCellValue($coordinate, (int) $value);
            $sheet->getStyle($coordinate)->getNumberFormat()->setFormatCode('#,##0');
            return;
        }

        if (in_array($field, $decimal_fields, TRUE) && is_numeric($value)) {
            $sheet->setCellValue($coordinate, (float) $value);
            $sheet->getStyle($coordinate)->getNumberFormat()->setFormatCode('#,##0.00');
            return;
        }

        if ($field === 'created_at' && trim((string) $value) !== '') {
            try {
                $date = new DateTime((string) $value);
                $sheet->setCellValue(
                    $coordinate,
                    \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($date)
                );
                $sheet->getStyle($coordinate)
                    ->getNumberFormat()
                    ->setFormatCode('mmm d, yyyy h:mm AM/PM');
                return;
            } catch (Exception $exception) {
                // Keep the original text if the stored date cannot be parsed.
            }
        }

        if ($field === 'type') {
            $value = ucwords(str_replace('_', ' ', (string) $value));
        }

        $sheet->setCellValueExplicit(
            $coordinate,
            (string) $value,
            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
        );

        if (in_array($field, $identifier_fields, TRUE)) {
            $sheet->getStyle($coordinate)
                ->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        }
    }

    private function excel_column_width($field) {
        $widths = array(
            'transaction_no' => 19,
            'type' => 13,
            'product_code' => 16,
            'product_name' => 28,
            'category_name' => 21,
            'supplier_name' => 24,
            'unit' => 12,
            'stock' => 12,
            'quantity' => 12,
            'reorder_level' => 15,
            'shortage' => 12,
            'cost_price' => 15,
            'inventory_value' => 18,
            'username' => 18,
            'remarks' => 32,
            'created_at' => 22
        );

        return isset($widths[$field]) ? $widths[$field] : 18;
    }

    private function export_pdf($title, $columns, $rows, $meta) {
        $this->load_composer();

        $display_rows = $this->prepare_display_rows($columns, $rows);
        $html = $this->load->view('reports/export_pdf', array(
            'report_title' => $title,
            'columns' => $columns,
            'rows' => $display_rows,
            'report_meta' => $meta
        ), TRUE);

        if (!class_exists('\\Dompdf\\Dompdf')) {
            throw new RuntimeException('Dompdf is unavailable. Run composer install in the project root.');
        }

        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'Helvetica');
        $options->set('isRemoteEnabled', FALSE);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $font = $dompdf->getFontMetrics()->getFont('Helvetica', 'normal');
        $canvas = $dompdf->getCanvas();
        $canvas->page_text(
            700,
            570,
            'Page {PAGE_NUM} of {PAGE_COUNT}',
            $font,
            8,
            array(0.39, 0.45, 0.55)
        );

        $this->prepare_download_output();
        $dompdf->stream(
            $this->report_filename($title, 'pdf'),
            array('Attachment' => FALSE)
        );

        exit;
    }

    private function prepare_display_rows($columns, $rows) {
        $prepared = array();

        foreach ($rows as $row) {
            $display_row = array();

            foreach ($columns as $field => $label) {
                $value = array_key_exists($field, $row) ? $row[$field] : '';
                $display_row[$field] = $this->format_display_value($field, $value);
            }

            $prepared[] = $display_row;
        }

        return $prepared;
    }

    private function format_display_value($field, $value) {
        if ($value === NULL || $value === '') {
            return '—';
        }

        if (in_array($field, array('stock', 'quantity', 'reorder_level', 'shortage'), TRUE) && is_numeric($value)) {
            return number_format((float) $value, 0);
        }

        if (in_array($field, array('cost_price', 'inventory_value'), TRUE) && is_numeric($value)) {
            return number_format((float) $value, 2);
        }

        if ($field === 'type') {
            return ucwords(str_replace('_', ' ', (string) $value));
        }

        if ($field === 'created_at') {
            $timestamp = strtotime((string) $value);

            if ($timestamp !== FALSE) {
                return date('M j, Y g:i A', $timestamp);
            }
        }

        return (string) $value;
    }

    private function report_filename($title, $extension) {
        $base = url_title($title, '-', TRUE);

        if ($base === '') {
            $base = 'report';
        }

        return $base . '-' . date('Y-m-d-His') . '.' . $extension;
    }

    private function prepare_download_output() {
        if (function_exists('ini_set')) {
            @ini_set('zlib.output_compression', 'Off');
        }

        while (ob_get_level() > 0) {
            @ob_end_clean();
        }
    }

    private function load_composer() {
        $autoload = FCPATH . 'vendor/autoload.php';

        if (!is_file($autoload)) {
            throw new RuntimeException(
                'Composer dependencies are missing. Run composer install in the project root.'
            );
        }

        require_once $autoload;
    }

    private function handle_export_failure($exception, $title) {
        $reference = strtoupper(substr(hash(
            'sha256',
            microtime(TRUE) . '|' . get_class($exception) . '|' . $exception->getMessage()
        ), 0, 10));

        log_message(
            'error',
            'Report export failed [' . $reference . '] ' .
            $title . ': ' .
            get_class($exception) . ': ' .
            $exception->getMessage()
        );

        header('X-Error-Reference: ' . $reference);

        show_error(
            'The report could not be generated. Possible causes include missing export dependencies, a temporary server or file-system problem, or invalid report data. Please retry. If it continues, give support reference ' . $reference . ' to the administrator.',
            500,
            'Report Export Failed'
        );
    }

    private function require_permission($permission_key) {
        $user_id = $this->session->userdata('user_id');

        if (!$user_id || !$this->User_model->has_permission($user_id, $permission_key)) {
            show_error(
                'You do not have permission to access this page.',
                403,
                'Access Denied'
            );
        }
    }
}
