<?php

use PHPUnit\Framework\TestCase;

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
}
