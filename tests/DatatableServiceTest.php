<?php

use PHPUnit\Framework\TestCase;

class DatatableInputStub {
    private $request;

    public function __construct($request) {
        $this->request = $request;
    }

    public function get($key = NULL, $xss_clean = TRUE) {
        return $this->request;
    }
}

class DatatableServiceTest extends TestCase {

    public function testRequestReadsServerSidePagingSearchAndOrder() {
        $input = new DatatableInputStub(array(
            'draw' => '3',
            'start' => '25',
            'length' => '25',
            'search' => array('value' => 'admin'),
            'order' => array(
                array('column' => '1', 'dir' => 'desc')
            )
        ));

        $service = new Datatable_service();
        $request = $service->request(
            $input,
            array('u.first_name', 'u.last_name'),
            'u.first_name',
            'asc'
        );

        $this->assertSame(3, $request['draw']);
        $this->assertSame(25, $request['start']);
        $this->assertSame(25, $request['length']);
        $this->assertSame('admin', $request['search']);
        $this->assertSame('u.last_name', $request['order_column']);
        $this->assertSame('desc', $request['order_dir']);
    }


    public function testPaginationAllowsOffsetsBeyondOneHundred() {
        $input = new DatatableInputStub(array(
            'start' => '250',
            'length' => '25'
        ));

        $request = (new Datatable_service())->request(
            $input,
            array('u.username'),
            'u.username',
            'asc'
        );

        $this->assertSame(250, $request['start']);
        $this->assertSame(25, $request['length']);
    }

    public function testInvalidLengthFallsBackToTen() {
        $input = new DatatableInputStub(array(
            'length' => '999'
        ));

        $request = (new Datatable_service())->request(
            $input,
            array('u.username'),
            'u.username',
            'asc'
        );

        $this->assertSame(10, $request['length']);
    }

    public function testUnknownOrderColumnUsesSafeDefault() {
        $input = new DatatableInputStub(array(
            'order' => array(
                array('column' => '99', 'dir' => 'desc')
            )
        ));

        $request = (new Datatable_service())->request(
            $input,
            array('u.username'),
            'u.username',
            'asc'
        );

        $this->assertSame('u.username', $request['order_column']);
        $this->assertSame('asc', $request['order_dir']);
    }

    public function testPayloadMatchesDataTablesServerSideContract() {
        $payload = (new Datatable_service())->payload(
            2,
            100,
            5,
            array(array('sample'))
        );

        $this->assertSame(2, $payload['draw']);
        $this->assertSame(100, $payload['recordsTotal']);
        $this->assertSame(5, $payload['recordsFiltered']);
        $this->assertSame(array(array('sample')), $payload['data']);
    }
}
