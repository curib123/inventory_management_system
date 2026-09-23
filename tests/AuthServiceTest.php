<?php

use PHPUnit\Framework\TestCase;

class AuthUserModelStub {
    public function login($username, $password) {
        return FALSE;
    }
}

class AuthServiceTest extends TestCase {

    public function testAuthenticateReturnsSessionDataForValidCredentials() {
        $user = (object) array(
            'id' => 7,
            'username' => 'admin',
            'role_id' => 1,
            'role_name' => 'admin'
        );
        $user_model = $this->createMock(AuthUserModelStub::class);
        $user_model->expects($this->once())
            ->method('login')
            ->with('admin', 'admin123')
            ->willReturn($user);

        $service = new Auth_service();
        $session_data = $service->authenticate($user_model, 'admin', 'admin123');

        $this->assertSame($user->id, $session_data['user_id']);
        $this->assertSame('admin', $session_data['username']);
        $this->assertSame('admin', $session_data['role_name']);
        $this->assertTrue($session_data['logged_in']);
    }

    public function testAuthenticateReturnsFalseForInvalidCredentials() {
        $user_model = $this->createMock(AuthUserModelStub::class);
        $user_model->expects($this->once())
            ->method('login')
            ->with('admin', 'wrong-password')
            ->willReturn(FALSE);

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
            'logged_in' => TRUE
        ), $session_data);
    }
}
