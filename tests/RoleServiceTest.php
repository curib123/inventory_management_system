<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/application/libraries/Role_service.php';

function &get_instance() {
    return $GLOBALS['ci_role_service_test'];
}

class RoleServiceTest extends TestCase {
    protected function setUp(): void {
        $GLOBALS['ci_role_service_test'] = new class {
            public $Role_model;
            public $Activity_log_model;
            public $config;
            public $db;
            public $session;
            public $input;
            public $load;

            public function __construct() {
                $this->load = new class {
                    public function model($names = array()) {
                    }
                };

                $this->config = new class {
                    public function load($name) {
                    }

                    public function item($key) {
                        return array();
                    }
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

                $this->db = new class {
                    public $transaction_status = TRUE;
                    public $inserted = array();
                    public function trans_begin() {}
                    public function trans_rollback() {}
                    public function trans_commit() {}
                    public function trans_status() {
                        return $this->transaction_status;
                    }
                };

                $this->Role_model = new class {
                    public $last_permission_ids;

                    public function get_by_id($id) {
                        return (object) array('id' => (int) $id, 'role_name' => 'warehouse_staff');
                    }

                    public function name_exists($name, $exclude_id = NULL) {
                        return FALSE;
                    }

                    public function save($data, $id = NULL) {
                        return (int) ($id !== NULL ? $id : 1);
                    }

                    public function get_permissions() {
                        return array(
                            (object) array('id' => 1, 'permission_key' => 'roles.view'),
                            (object) array('id' => 2, 'permission_key' => 'roles.permissions'),
                            (object) array('id' => 3, 'permission_key' => 'roles.edit')
                        );
                    }

                    public function replace_permissions($role_id, $permission_ids) {
                        $this->last_permission_ids = $permission_ids;
                        return TRUE;
                    }
                };

                $this->Activity_log_model = new class {
                    public $last_activity = NULL;

                    public function insert_activity_log($data) {
                        $this->last_activity = $data;
                    }
                };
            }
        };
    }

    public function testPermissionOnlyUpdateUsesExistingRoleNameInAuditLog() {
        $service = new Role_service();

        $result = $service->save(
            7,
            NULL,
            array(1, 2),
            FALSE,
            TRUE
        );

        $this->assertTrue($result['success']);
        $this->assertSame('Role permissions updated successfully.', $result['message']);
        $this->assertStringContainsString('warehouse_staff', $GLOBALS['ci_role_service_test']->Activity_log_model->last_activity['description']);
    }
}
