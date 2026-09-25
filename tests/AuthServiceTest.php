<?php

use PHPUnit\Framework\TestCase;

class AuthUserModelStub {
    public function find_active_by_username($username) {
        return FALSE;
    }
}

class AuthServiceTest extends TestCase {

    public function testAuthenticateReturnsSessionDataForValidCredentials() {
        $user = (object) array(
            'id' => 7,
            'username' => 'admin',
            'role_id' => 1,
            'role_name' => 'admin',
            'password' => password_hash('StrongPass123!', PASSWORD_DEFAULT),
            'must_change_password' => 0
        );
        $user_model = $this->createMock(AuthUserModelStub::class);
        $user_model->expects($this->once())
            ->method('find_active_by_username')
            ->with('admin')
            ->willReturn($user);

        $service = new Auth_service();
        $session_data = $service->authenticate($user_model, 'admin', 'StrongPass123!');

        $this->assertSame($user->id, $session_data['user_id']);
        $this->assertSame('admin', $session_data['username']);
        $this->assertSame('admin', $session_data['role_name']);
        $this->assertTrue($session_data['logged_in']);
    }

    public function testSessionDataIncludesPasswordChangeState() {
        $user = (object) array(
            'id' => 9,
            'username' => 'inventory-user',
            'role_id' => 2,
            'role_name' => 'staff',
            'must_change_password' => 1
        );

        $session_data = (new Auth_service())->session_data($user);

        $this->assertTrue($session_data['must_change_password']);
        $this->assertFalse($session_data['password_change_deferred']);
        $this->assertTrue($session_data['logged_in']);
    }

    public function testAuthenticateReturnsFalseForInvalidCredentials() {
        $user = (object) array(
            'id' => 7,
            'username' => 'admin',
            'role_id' => 1,
            'role_name' => 'admin',
            'password' => password_hash('StrongPass123!', PASSWORD_DEFAULT),
            'must_change_password' => 0
        );
        $user_model = $this->createMock(AuthUserModelStub::class);
        $user_model->expects($this->once())
            ->method('find_active_by_username')
            ->with('admin')
            ->willReturn($user);

        $service = new Auth_service();

        $this->assertFalse($service->authenticate($user_model, 'admin', 'wrong-password'));
    }

    public function testSessionDataContainsRoleInformation() {
        $user = (object) array(
            'id' => 12,
            'username' => 'staff-user',
            'role_id' => 2,
            'role_name' => 'staff'
        );

        $session_data = (new Auth_service())->session_data($user);

        $this->assertSame(array(
            'user_id' => 12,
            'username' => 'staff-user',
            'role_id' => 2,
            'role_name' => 'staff',
            'must_change_password' => FALSE,
            'password_change_deferred' => FALSE,
            'logged_in' => TRUE
        ), $session_data);
    }
}
