<?php

use PHPUnit\Framework\TestCase;

class AuthUserModelStub {
    // Test stub query ni para active user lookup; Auth_service ra ang caller during unit test.
    public function find_active_by_username($username) {
        return FALSE;
    }

    // Test stub count ni para setup-state check; Auth_service ra ang caller during unit test.
    public function count_all() {
        return 0;
    }
}

class AuthServiceTest extends TestCase {

    // QA ni para valid login; sakto nga credentials should return complete session data from Auth_service.
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

        $service = new Auth_service(array('user_model' => $user_model));
        $session_data = $service->authenticate('admin', 'StrongPass123!');

        $this->assertSame($user->id, $session_data['user_id']);
        $this->assertSame('admin', $session_data['username']);
        $this->assertSame('admin', $session_data['role_name']);
        $this->assertTrue($session_data['logged_in']);
    }

    // QA ni para password-change state; session payload should preserve required/deferred flags correctly.
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

    // QA ni para invalid login; wrong password should fail bisan naa ang username.
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

        $service = new Auth_service(array('user_model' => $user_model));

        $this->assertFalse($service->authenticate('admin', 'wrong-password'));
    }

    // QA ni para role session data; role ID/name should survive authentication payload building.
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
