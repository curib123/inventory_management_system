<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/application/libraries/User_service.php';

class UserSessionIdentityModelStub {
    public $user;
    public $requested_id;

    public function get_by_id($user_id) {
        $this->requested_id = (int) $user_id;
        return $this->user;
    }
}

class UserSessionIdentityTest extends TestCase {
    private function createService(UserSessionIdentityModelStub $user_model) {
        $service = (new ReflectionClass(User_service::class))->newInstanceWithoutConstructor();
        $ci_property = new ReflectionProperty(User_service::class, 'CI');
        $ci_property->setAccessible(TRUE);
        $ci_property->setValue($service, (object) array('User_model' => $user_model));

        return $service;
    }

    public function testSessionIdentityUsesFreshRoleAndProfileValues() {
        $user_model = new UserSessionIdentityModelStub();
        $user_model->user = (object) array(
            'id' => 12,
            'first_name' => 'Updated',
            'last_name' => 'Administrator',
            'username' => 'new-admin',
            'role_id' => 3,
            'role_name' => 'manager',
            'status' => 1,
            'role_status' => 1,
            'must_change_password' => 0
        );
        $service = $this->createService($user_model);

        $identity = $service->session_identity(12);

        $this->assertSame(12, $user_model->requested_id);
        $this->assertSame(array(
            'first_name' => 'Updated',
            'last_name' => 'Administrator',
            'username' => 'new-admin',
            'role_id' => 3,
            'role_name' => 'manager',
            'must_change_password' => FALSE
        ), $identity);
    }

    public function testSessionIdentityRejectsInactiveUserOrRole() {
        $user_model = new UserSessionIdentityModelStub();
        $user_model->user = (object) array('status' => 0, 'role_status' => 1);
        $service = $this->createService($user_model);

        $this->assertFalse($service->session_identity(12));

        $user_model->user = (object) array('status' => 1, 'role_status' => 0);

        $this->assertFalse($service->session_identity(12));
    }
}
