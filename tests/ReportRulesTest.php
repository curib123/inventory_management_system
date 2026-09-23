<?php

use PHPUnit\Framework\TestCase;

class ReportRulesTest extends TestCase {

    public function testStockInReportUsesStockInTransactionType() {
        $definition = (new Report_rules())->get('stock-in');

        $this->assertSame('Stock-In Report', $definition['title']);
        $this->assertSame('stock_in', $definition['type']);
    }

    public function testUnknownReportIsRejected() {
        $this->expectException(InvalidArgumentException::class);

        (new Report_rules())->get('unknown');
    }

    public function testSupportedExportFormatsAreAccepted() {
        $rules = new Report_rules();

        $this->assertTrue($rules->export_format_is_supported('CSV'));
        $this->assertTrue($rules->export_format_is_supported('xlsx'));
        $this->assertTrue($rules->export_format_is_supported('pdf'));
        $this->assertFalse($rules->export_format_is_supported('xml'));
    }
}
