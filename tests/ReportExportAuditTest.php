<?php

use PHPUnit\Framework\TestCase;

class CI_Controller {}

require_once dirname(__DIR__) . '/application/controllers/Reports.php';

class TestableReports extends Reports {
    public function __construct() {
        $this->Activity_log_model = new class {
            public $calls = 0;
            public $records = array();

            public function insert_activity_log($data) {
                $this->calls++;
                $this->records[] = $data;
            }
        };

        $this->session = new class {
            public function userdata($key) {
                if ($key === 'user_id') {
                    return 11;
                }

                if ($key === 'username') {
                    return 'audit-user';
                }

                return NULL;
            }
        };

        $this->input = new class {
            public function get($key, $filter = TRUE) {
                if ($key === 'search') {
                    return '';
                }

                if ($key === 'table_filters') {
                    return array();
                }

                return NULL;
            }

            public function ip_address() {
                return '127.0.0.1';
            }
        };

        $this->report_service = new class {
            public function export_format_is_supported($format) {
                return true;
            }

            public function export_payload($report, $search, $filters, $username) {
                return array(
                    'rows' => array(array('id' => 1)),
                    'columns' => array('id' => 'ID'),
                    'meta' => array(
                        'system_name' => 'Inventory Management System',
                        'generated_at' => '2024-01-01 09:00:00',
                        'prepared_by' => 'audit-user',
                        'record_count' => 1,
                        'summary' => array('Total' => 1)
                    )
                );
            }
        };

        $this->authorization_service = new class {
            public function has_permission($user_id, $permission_key) {
                return TRUE;
            }
        };
    }
}

class ReportExportAuditTest extends TestCase {
    public function testSuccessfulExportUsesPostGenerationLog() {
        $controller = new TestableReports();
        $reflection = new ReflectionMethod(Reports::class, 'log_successful_export');

        $reflection->setAccessible(TRUE);
        $reflection->invoke($controller, 'csv');

        $this->assertSame(1, $controller->Activity_log_model->calls);
        $this->assertSame('export_created', $controller->Activity_log_model->records[0]['action']);
        $this->assertSame('Exported csv', $controller->Activity_log_model->records[0]['description']);
    }

    public function testFailedExportDoesNotWriteSuccessLog() {
        $controller = new TestableReports();
        $controller->report_service = new class {
            public function export_format_is_supported($format) {
                return true;
            }

            public function export_payload($report, $search, $filters, $username) {
                throw new RuntimeException('boom');
            }
        };

        try {
            $controller->export('inventory', 'csv');
        } catch (Throwable $exception) {
            // handled by controller in normal runtime
        }

        $this->assertSame(0, $controller->Activity_log_model->calls);
    }
}
