<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends CI_Controller {

    // Setup ni sa Reports controller; CodeIgniter mo-run ani automatically, while route mapping makita sa application/config/routes.php.
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper(array('url', 'html'));
        $this->load->library(array('Datatable_service', 'Report_service'));

        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }

        $this->load->model('User_model');
        $this->load->library('Report_rules');
        $this->require_permission('reports.view');
        $this->load->model('Report_model');
    }

    // Mao ni ang index flow sa Reports; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function index() { $this->show_report('inventory'); }
    // Mao ni ang inventory flow sa Reports; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function inventory() { $this->show_report('inventory'); }
    // Mao ni ang stock in flow sa Reports; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function stock_in() { $this->show_report('stock-in'); }
    // Mao ni ang stock out flow sa Reports; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function stock_out() { $this->show_report('stock-out'); }
    // Mao ni ang movement flow sa Reports; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function movement() { $this->show_report('movement'); }
    // Mao ni ang low stock flow sa Reports; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function low_stock() { $this->show_report('low-stock'); }
    // Mao ni ang valuation flow sa Reports; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function valuation() { $this->show_report('valuation'); }

    // Mao ni ang datatable flow sa Reports; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function datatable($report) {
        $this->get_definition($report);

        $order_columns = $this->report_service->order_columns($report);
        $default_order = $this->report_service->default_order($report);
        $request = $this->datatable_service->request(
            $this->input,
            $order_columns,
            $default_order['column'],
            $default_order['dir']
        );

        $report_data = $this->report_service->datatable($report, $request);
        $rows = $report_data['rows'];
        $fields = array_keys($this->report_service->columns($report));
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
            $report_data['total'],
            $report_data['filtered'],
            $data_rows
        );

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }

    // Mao ni ang export flow sa Reports; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
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
            $search = trim((string) $this->input->get('search', TRUE));
            $filters = $this->input->get('table_filters', TRUE);
            $filters = is_array($filters) ? $filters : array();

            $payload = $this->report_service->export_payload(
                $report,
                $search,
                $filters,
                $this->session->userdata('username')
            );
            $rows = $payload['rows'];
            $columns = $payload['columns'];
            $meta = $payload['meta'];

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

    // Internal helper ni para show report; tawagon ra sulod application/controllers/Reports.php, so ari ra pud pangitaa ang caller if mag-trace ka.
    private function show_report($report) {
        $definition = $this->get_definition($report);
        $data['report_title'] = $definition['title'];
        $data['report_key'] = $report;
        $data['columns'] = $this->report_service->columns($report);
        $data['page_title'] = $definition['title'];

        $this->load->view('templates/header', $data);
        $this->load->view('reports/index', $data);
        $this->load->view('templates/footer');
    }

    // Internal helper ni para get definition; tawagon ra sulod application/controllers/Reports.php, so ari ra pud pangitaa ang caller if mag-trace ka.
    private function get_definition($report) {
        try {
            return $this->report_service->definition($report);
        } catch (InvalidArgumentException $exception) {
            show_404();
            exit;
        }
    }

// Internal helper ni para export csv; tawagon ra sulod application/controllers/Reports.php, so ari ra pud pangitaa ang caller if mag-trace ka.
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

// Internal helper ni para write csv metadata; tawagon ra sulod application/controllers/Reports.php, so ari ra pud pangitaa ang caller if mag-trace ka.
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

// Internal helper ni para write csv header; tawagon ra sulod application/controllers/Reports.php, so ari ra pud pangitaa ang caller if mag-trace ka.
private function write_csv_header($handle, $columns)
{
    $this->write_csv_row(
        $handle,
        array_values($columns)
    );
}

// Internal helper ni para write csv data; tawagon ra sulod application/controllers/Reports.php, so ari ra pud pangitaa ang caller if mag-trace ka.
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

// Internal helper ni para write csv row; tawagon ra sulod application/controllers/Reports.php, so ari ra pud pangitaa ang caller if mag-trace ka.
private function write_csv_row($handle, $values)
{
    if (fputcsv($handle, $values, ',', '"', '') === false) {
        throw new RuntimeException('Unable to write the CSV report.');
    }
}

// Internal helper ni para csv safe value; tawagon ra sulod application/controllers/Reports.php, so ari ra pud pangitaa ang caller if mag-trace ka.
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

    if ($trimmed !== '') {
        $firstCharacter = $trimmed[0];

        if (in_array($firstCharacter, array('=', '+', '-', '@'), true)) {
            return "'" . $value;
        }
    }

    return $value;
}


    // Internal helper ni para export xlsx; tawagon ra sulod application/controllers/Reports.php, so ari ra pud pangitaa ang caller if mag-trace ka.
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
            ->setDescription('Enterprise inventory report generated by ' . $meta['system_name']);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($title, 0, 31));
        $sheet->getSheetView()->setZoomScale(90);
        $sheet->setShowGridlines(FALSE);

        $column_count = max(1, count($columns));
        $last_column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($column_count);
        $header_row = 8;
        $data_start_row = 9;

        // Branded report heading.
        $sheet->mergeCells('A1:' . $last_column . '1');
        $sheet->setCellValue('A1', strtoupper($meta['system_name']));
        $sheet->mergeCells('A2:' . $last_column . '2');
        $sheet->setCellValue('A2', $title);
        $sheet->mergeCells('A3:' . $last_column . '3');
        $sheet->setCellValue(
            'A3',
            'Generated ' . $meta['generated_at'] .
            '  •  Prepared by ' . ($meta['prepared_by'] ?: 'System User') .
            '  •  ' . number_format((int) $meta['record_count']) . ' records'
        );

        $sheet->getStyle('A1:' . $last_column . '1')->applyFromArray(array(
            'font' => array(
                'bold' => TRUE,
                'size' => 11,
                'color' => array('argb' => 'DBEAFE')
            ),
            'fill' => array(
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => array('argb' => '0F172A')
            ),
            'alignment' => array(
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            )
        ));
        $sheet->getRowDimension(1)->setRowHeight(26);

        $sheet->getStyle('A2:' . $last_column . '2')->applyFromArray(array(
            'font' => array(
                'bold' => TRUE,
                'size' => 17,
                'color' => array('argb' => '0F172A')
            ),
            'alignment' => array(
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            )
        ));
        $sheet->getRowDimension(2)->setRowHeight(28);

        $sheet->getStyle('A3:' . $last_column . '3')->applyFromArray(array(
            'font' => array(
                'size' => 9,
                'color' => array('argb' => '64748B')
            ),
            'borders' => array(
                'bottom' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                    'color' => array('argb' => '2563EB')
                )
            )
        ));
        $sheet->getRowDimension(3)->setRowHeight(22);

        // KPI summary cards. Two columns are used per card where space allows.
        $summary_index = 0;

        foreach ((array) $meta['summary'] as $label => $value) {
            $start_index = 1 + ($summary_index * 2);

            if ($start_index > $column_count) {
                break;
            }

            $end_index = min($start_index + 1, $column_count);
            $start_letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($start_index);
            $end_letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($end_index);

            if ($start_index !== $end_index) {
                $sheet->mergeCells($start_letter . '5:' . $end_letter . '5');
                $sheet->mergeCells($start_letter . '6:' . $end_letter . '6');
            }

            $sheet->setCellValue($start_letter . '5', strtoupper((string) $label));
            $sheet->setCellValue($start_letter . '6', (string) $value);

            $card_range = $start_letter . '5:' . $end_letter . '6';
            $sheet->getStyle($card_range)->applyFromArray(array(
                'fill' => array(
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => array('argb' => 'F8FAFC')
                ),
                'borders' => array(
                    'outline' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => array('argb' => 'CBD5E1')
                    )
                ),
                'alignment' => array(
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                )
            ));

            $sheet->getStyle($start_letter . '5:' . $end_letter . '5')->getFont()
                ->setBold(TRUE)
                ->setSize(8)
                ->getColor()->setARGB('64748B');
            $sheet->getStyle($start_letter . '6:' . $end_letter . '6')->getFont()
                ->setBold(TRUE)
                ->setSize(12)
                ->getColor()->setARGB('0F172A');

            $summary_index++;
        }

        $sheet->getRowDimension(5)->setRowHeight(18);
        $sheet->getRowDimension(6)->setRowHeight(25);

        // Data header.
        $column_index = 1;
        foreach ($columns as $field => $label) {
            $column_letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($column_index);
            $sheet->setCellValue($column_letter . $header_row, $label);
            $column_index++;
        }

        $sheet->getStyle('A' . $header_row . ':' . $last_column . $header_row)->applyFromArray(array(
            'font' => array(
                'bold' => TRUE,
                'size' => 9,
                'color' => array('argb' => 'FFFFFF')
            ),
            'fill' => array(
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => array('argb' => '1D4ED8')
            ),
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array('argb' => '1E40AF')
                )
            ),
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => TRUE
            )
        ));
        $sheet->getRowDimension($header_row)->setRowHeight(27);

        $row_number = $data_start_row;

        foreach ($rows as $row) {
            $column_index = 1;

            foreach ($columns as $field => $label) {
                $column_letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($column_index);
                $coordinate = $column_letter . $row_number;
                $value = array_key_exists($field, $row) ? $row[$field] : '';

                $this->set_excel_cell_value($sheet, $coordinate, $field, $value);

                if ($field === 'type') {
                    $normalized_type = strtolower((string) $value);
                    $fill = 'EFF6FF';
                    $font_color = '1D4ED8';

                    if ($normalized_type === 'stock_in') {
                        $fill = 'ECFDF5';
                        $font_color = '047857';
                    } elseif ($normalized_type === 'stock_out') {
                        $fill = 'FEF2F2';
                        $font_color = 'B91C1C';
                    } elseif ($normalized_type === 'adjustment') {
                        $fill = 'FFFBEB';
                        $font_color = 'B45309';
                    }

                    $sheet->getStyle($coordinate)->applyFromArray(array(
                        'font' => array(
                            'bold' => TRUE,
                            'color' => array('argb' => $font_color)
                        ),
                        'fill' => array(
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => array('argb' => $fill)
                        ),
                        'alignment' => array(
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
                        )
                    ));
                }

                $column_index++;
            }

            $row_number++;
        }

        if (empty($rows)) {
            $sheet->mergeCells('A' . $data_start_row . ':' . $last_column . $data_start_row);
            $sheet->setCellValue('A' . $data_start_row, 'No report data found for this report.');
            $sheet->getStyle('A' . $data_start_row)->applyFromArray(array(
                'font' => array(
                    'italic' => TRUE,
                    'color' => array('argb' => '64748B')
                ),
                'alignment' => array(
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
                )
            ));
            $sheet->getRowDimension($data_start_row)->setRowHeight(28);
            $row_number = $data_start_row + 1;
        }

        $last_data_row = max($header_row, $row_number - 1);

        if (!empty($rows)) {
            $data_range = 'A' . $data_start_row . ':' . $last_column . $last_data_row;

            $sheet->getStyle($data_range)->applyFromArray(array(
                'font' => array(
                    'size' => 9,
                    'color' => array('argb' => '334155')
                ),
                'borders' => array(
                    'allBorders' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR,
                        'color' => array('argb' => 'E2E8F0')
                    )
                ),
                'alignment' => array(
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                    'wrapText' => TRUE
                )
            ));

            for ($data_row = $data_start_row; $data_row <= $last_data_row; $data_row++) {
                $sheet->getRowDimension($data_row)->setRowHeight(22);

                if (($data_row - $data_start_row) % 2 === 1) {
                    $sheet->getStyle('A' . $data_row . ':' . $last_column . $data_row)
                        ->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('F8FAFC');
                }
            }
        }

        $column_index = 1;

        foreach ($columns as $field => $label) {
            $column_letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($column_index);
            $sheet->getColumnDimension($column_letter)->setWidth($this->excel_column_width($field));

            if (
                !empty($rows) &&
                in_array($field, array('stock', 'quantity', 'reorder_level', 'shortage', 'cost_price', 'inventory_value'), TRUE)
            ) {
                $sheet->getStyle($column_letter . $data_start_row . ':' . $column_letter . $last_data_row)
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            }

            $column_index++;
        }

        $sheet->freezePane('A' . $data_start_row);
        $sheet->setAutoFilter('A' . $header_row . ':' . $last_column . $header_row);
        $sheet->setSelectedCell('A' . $data_start_row);

        $sheet->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
            ->setFitToPage(TRUE)
            ->setFitToWidth(1)
            ->setFitToHeight(0);

        $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($header_row, $header_row);
        $sheet->getPageMargins()
            ->setTop(0.45)
            ->setRight(0.35)
            ->setBottom(0.55)
            ->setLeft(0.35);

        $sheet->getHeaderFooter()->setOddHeader('&L&B' . $title . '&R' . $meta['generated_at']);
        $sheet->getHeaderFooter()->setOddFooter(
            '&L' . $meta['system_name'] . '&CPage &P of &N&RConfidential business report'
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

    // Internal helper ni para set excel cell value; tawagon ra sulod application/controllers/Reports.php, so ari ra pud pangitaa ang caller if mag-trace ka.
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
            $sheet->getStyle($coordinate)->getNumberFormat()->setFormatCode('"₱"#,##0.00');
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

    // Internal helper ni para excel column width; tawagon ra sulod application/controllers/Reports.php, so ari ra pud pangitaa ang caller if mag-trace ka.
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

    // Internal helper ni para export pdf; tawagon ra sulod application/controllers/Reports.php, so ari ra pud pangitaa ang caller if mag-trace ka.
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
            max(0, $canvas->get_width() - 105),
            max(0, $canvas->get_height() - 22),
            'Page {PAGE_NUM} of {PAGE_COUNT}',
            $font,
            7,
            array(0.39, 0.45, 0.55)
        );

        $this->prepare_download_output();
        $dompdf->stream(
            $this->report_filename($title, 'pdf'),
            array('Attachment' => FALSE)
        );

        exit;
    }

    // Internal helper ni para prepare display rows; tawagon ra sulod application/controllers/Reports.php, so ari ra pud pangitaa ang caller if mag-trace ka.
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

    // Internal helper ni para format display value; tawagon ra sulod application/controllers/Reports.php, so ari ra pud pangitaa ang caller if mag-trace ka.
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

    // Internal helper ni para report filename; tawagon ra sulod application/controllers/Reports.php, so ari ra pud pangitaa ang caller if mag-trace ka.
    private function report_filename($title, $extension) {
        $base = url_title($title, '-', TRUE);

        if ($base === '') {
            $base = 'report';
        }

        return $base . '-' . date('Y-m-d-His') . '.' . $extension;
    }

    // Internal helper ni para prepare download output; tawagon ra sulod application/controllers/Reports.php, so ari ra pud pangitaa ang caller if mag-trace ka.
    private function prepare_download_output() {
        if (function_exists('ini_set')) {
            @ini_set('zlib.output_compression', 'Off');
        }

        while (ob_get_level() > 0) {
            @ob_end_clean();
        }
    }

    // Internal helper ni para load composer; tawagon ra sulod application/controllers/Reports.php, so ari ra pud pangitaa ang caller if mag-trace ka.
    private function load_composer() {
        $autoload = FCPATH . 'vendor/autoload.php';

        if (!is_file($autoload)) {
            throw new RuntimeException(
                'Composer dependencies are missing. Run composer install in the project root.'
            );
        }

        require_once $autoload;
    }

    // Internal helper ni para handle export failure; tawagon ra sulod application/controllers/Reports.php, so ari ra pud pangitaa ang caller if mag-trace ka.
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

    // Internal helper ni para require permission; tawagon ra sulod application/controllers/Reports.php, so ari ra pud pangitaa ang caller if mag-trace ka.
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
