<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/application/libraries/Category_service.php';

class CategoryServiceTest extends TestCase {
    protected function setUp(): void {
        $GLOBALS['ci_category_service_test'] = new class {
            public $Category_model;
            public $Activity_log_model;
            public $session;
            public $input;
            public $load;

            public function __construct() {
                $this->load = new class {
                    public function model($names = array()) {}
                };

                $this->session = new class {
                    public function userdata($key) {
                        return 42;
                    }
                };

                $this->input = new class {
                    public function ip_address() {
                        return '127.0.0.1';
                    }
                };

                $this->Category_model = new class {
                    public $save_result = TRUE;
                    public $save_calls = 0;

                    public function name_exists($name, $exclude_id = NULL) {
                        return FALSE;
                    }

                    public function save($data, $id = NULL) {
                        $this->save_calls++;
                        return $this->save_result;
                    }
                };

                $this->Activity_log_model = new class {
                    public $insert_calls = 0;

                    public function insert_activity_log($data) {
                        $this->insert_calls++;
                    }
                };
            }
        };
    }

    public function testFailedCategorySaveDoesNotWriteActivityLog() {
        $this->resetGlobalStub();
        $GLOBALS['ci_category_service_test']->Category_model->save_result = FALSE;

        $service = new Category_service($GLOBALS['ci_category_service_test']);
        $result = $service->save(NULL, 'Hardware', 1);

        $this->assertFalse($result['success']);
        $this->assertSame(0, $GLOBALS['ci_category_service_test']->Activity_log_model->insert_calls);
    }

    public function testSuccessfulCategorySaveWritesOneActivityLog() {
        $this->resetGlobalStub();
        $GLOBALS['ci_category_service_test']->Category_model->save_result = TRUE;

        $service = new Category_service($GLOBALS['ci_category_service_test']);
        $result = $service->save(NULL, 'Hardware', 1);

        $this->assertTrue($result['success']);
        $this->assertSame(1, $GLOBALS['ci_category_service_test']->Activity_log_model->insert_calls);
    }

    private function resetGlobalStub() {
        $GLOBALS['ci_category_service_test']->Category_model->save_result = TRUE;
        $GLOBALS['ci_category_service_test']->Category_model->save_calls = 0;
        $GLOBALS['ci_category_service_test']->Activity_log_model->insert_calls = 0;
    }
}
