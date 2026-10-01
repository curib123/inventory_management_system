<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/application/libraries/Report_service.php';

class ReportRulesTest extends TestCase {

    // QA ni para stock-in report mapping; definition should point to the correct transaction type and model method.
    public function testStockInReportUsesStockInTransactionType() {
        $definition = (new Report_rules())->get('stock-in');

        $this->assertSame('Stock-In Report', $definition['title']);
        $this->assertSame('stock_in', $definition['type']);
        $this->assertSame('get_stock_movement_report', $definition['method']);
    }

    // QA ni para unknown report key; unsupported report should throw instead of silently using another definition.
    public function testUnknownReportIsRejected() {
        $this->expectException(InvalidArgumentException::class);

        (new Report_rules())->get('unknown');
    }

    // QA ni para export format policy; CSV/XLSX/PDF allowed while unsupported formats should fail.
    public function testSupportedExportFormatsAreAccepted() {
        $rules = new Report_rules();

        $this->assertTrue($rules->export_format_is_supported('CSV'));
        $this->assertTrue($rules->export_format_is_supported('xlsx'));
        $this->assertTrue($rules->export_format_is_supported('pdf'));
        $this->assertFalse($rules->export_format_is_supported('xml'));
    }

    public function testMovementReportSnapshotColumnsHaveMatchingOrderMappings() {
        $service = (new ReflectionClass(Report_service::class))->newInstanceWithoutConstructor();
        $columns = $service->columns('movement');
        $order_columns = $service->order_columns('movement');

        $this->assertSame(count($columns), count($order_columns));
        $this->assertContains('category_name', array_keys($columns));
        $this->assertContains('unit', array_keys($columns));
        $this->assertContains('i.category_name_snapshot', $order_columns);
        $this->assertContains('i.unit_snapshot', $order_columns);
    }
}
