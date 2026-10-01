<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/application/libraries/User_service.php';

class Issue015UserSaveModelStub {
    public function get_role_by_id($role_id) {
        return (object) array('id' => (int) $role_id, 'status' => 1);
    }

    public function username_exists($username, $exclude_id = NULL) {
        return FALSE;
    }

    public function save($data, $id = NULL) {
        return TRUE;
    }
}

class Issue015RoleInvariantStub {
    public $capable_users;
    public $locks = 0;

    public function lock_admin_invariant() {
        $this->locks++;
    }

    public function count_admin_capable_users() {
        return $this->capable_users;
    }
}

class Issue015UserDbStub {
    public $rollbacks = 0;
    public $commits = 0;

    public function trans_begin() {}
    public function trans_rollback() { $this->rollbacks++; }
    public function trans_commit() { $this->commits++; }
    public function trans_status() { return TRUE; }
}

class Issue015ActivityLogStub {
    public $calls = 0;

    public function insert_activity_log($data) {
        $this->calls++;
        return TRUE;
    }
}

class Issue015InputStub {
    public function ip_address() {
        return '127.0.0.1';
    }
}

class UserAdminCapabilityTest extends TestCase {
    private function createService($capable_users, &$dependencies) {
        $role_model = new Issue015RoleInvariantStub();
        $role_model->capable_users = $capable_users;
        $dependencies = array(
            'Role_model' => $role_model,
            'User_model' => new Issue015UserSaveModelStub(),
            'Activity_log_model' => new Issue015ActivityLogStub(),
            'db' => new Issue015UserDbStub(),
            'input' => new Issue015InputStub()
        );

        $service = (new ReflectionClass(User_service::class))->newInstanceWithoutConstructor();
        $ci_property = new ReflectionProperty(User_service::class, 'CI');
        $ci_property->setAccessible(TRUE);
        $ci_property->setValue($service, (object) $dependencies);

        return $service;
    }

    private function updateUser($service, $status, $role_id) {
        $current_user = (object) array('id' => 42, 'role_id' => 1, 'status' => 1);

        return $service->save(42, array(
            'first_name' => 'Admin',
            'middle_name' => '',
            'last_name' => 'User',
            'username' => 'admin',
            'role_id' => $role_id,
            'status' => $status,
            'password' => ''
        ), $current_user, 42);
    }

    public function testDemotingLastCapableAdminIsRejected() {
        $service = $this->createService(0, $dependencies);

        $result = $this->updateUser($service, 1, 2);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('at least one active administrator', $result['message']);
        $this->assertSame(1, $dependencies['Role_model']->locks);
        $this->assertSame(1, $dependencies['db']->rollbacks);
        $this->assertSame(0, $dependencies['db']->commits);
        $this->assertSame(0, $dependencies['Activity_log_model']->calls);
    }

    public function testDeactivatingLastCapableAdminIsRejected() {
        $service = $this->createService(0, $dependencies);

        $result = $this->updateUser($service, 0, 1);

        $this->assertFalse($result['success']);
        $this->assertSame(1, $dependencies['db']->rollbacks);
    }

    public function testUserRoleChangeIsAllowedWhenAnotherCapableAdminRemains() {
        $service = $this->createService(1, $dependencies);

        $result = $this->updateUser($service, 1, 2);

        $this->assertTrue($result['success']);
        $this->assertSame(1, $dependencies['db']->commits);
        $this->assertSame(0, $dependencies['db']->rollbacks);
        $this->assertSame(1, $dependencies['Activity_log_model']->calls);
    }
}
