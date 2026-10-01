<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/application/libraries/Report_rules.php';
require_once dirname(__DIR__) . '/application/libraries/Report_service.php';

class Issue014ChunkedReportModelStub {
    public $count = 1200;
    public $calls = 0;
    public $largest_chunk = 0;

    public function count_export_rows($report, $search, $filters) {
        return $this->count;
    }

    public function get_export_rows_chunk($report, $search, $filters, $cursor, $limit) {
        $this->calls++;
        $start_id = is_array($cursor) ? (int) $cursor['id'] : 0;
        $end_id = min($this->count, $start_id + $limit);
        $rows = array();

        for ($id = $start_id + 1; $id <= $end_id; $id++) {
            $rows[] = array(
                'stock' => 1,
                'inventory_value' => 2.0,
                '__export_name' => sprintf('Product %04d', $id),
                '__export_cursor_id' => $id
            );
        }

        $this->largest_chunk = max($this->largest_chunk, count($rows));
        return $rows;
    }
}

class ReportExportStreamingTest extends TestCase {
    public function testExportMetadataSummarizesRowsAcrossBoundedChunks() {
        $model = new Issue014ChunkedReportModelStub();
        $ci = (object) array(
            'Report_model' => $model,
            'report_rules' => new Report_rules()
        );
        $service = (new ReflectionClass(Report_service::class))->newInstanceWithoutConstructor();
        $ci_property = new ReflectionProperty(Report_service::class, 'CI');
        $ci_property->setAccessible(TRUE);
        $ci_property->setValue($service, $ci);

        $payload = $service->export_metadata('inventory', '', array(), 'manager', 500);

        $this->assertSame(1200, $payload['meta']['record_count']);
        $this->assertSame('1,200', $payload['meta']['summary']['Total Stock']);
        $this->assertSame('₱2,400.00', $payload['meta']['summary']['Inventory Value']);
        $this->assertSame(3, $model->calls);
        $this->assertSame(500, $model->largest_chunk);
    }
}
