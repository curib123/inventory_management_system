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

class AuthActivityLogModelStub {
    public function insert_activity_log($data) {
    }
}

class AuthLoginAttemptModelStub {
    public function is_locked($username, $ip_address) {
        return FALSE;
    }

    public function record_failure($username, $ip_address) {
        return FALSE;
    }

    public function clear_attempts($username, $ip_address) {
    }

    public function lockout_status($username, $ip_address) {
        return array('locked_until' => NULL, 'remaining_seconds' => 0);
    }
}

class AuthServiceTest extends TestCase {

    // QA ni para valid login; sakto nga credentials should return complete session data from Auth_service.
    public function testAuthenticateReturnsSessionDataForValidCredentials() {
        $user = (object) array(
            'id' => 7,
            'username' => 'admin',
            'profile_image' => '0123456789abcdef0123456789abcdef.png',
            'role_id' => 1,
            'role_name' => 'admin',
            'auth_version' => 4,
            'password' => password_hash('StrongPass123!', PASSWORD_DEFAULT),
            'must_change_password' => 0
        );
        $user_model = $this->createMock(AuthUserModelStub::class);
        $user_model->expects($this->once())
            ->method('find_active_by_username')
            ->with('admin')
            ->willReturn($user);

        $activity_log_model = $this->createMock(
            AuthActivityLogModelStub::class
        );
        $activity_log_model->expects($this->once())
            ->method('insert_activity_log')
            ->with($this->callback(function ($data) {
                return $data['user_id'] === 7
                    && $data['action'] === 'login_created'
                    && $data['description'] === 'Login Session';
            }));
        $login_attempt_model = $this->createMock(AuthLoginAttemptModelStub::class);
        $login_attempt_model->expects($this->once())
            ->method('is_locked')
            ->with('admin', '127.0.0.1')
            ->willReturn(FALSE);
        $login_attempt_model->expects($this->once())
            ->method('clear_attempts')
            ->with('admin', '127.0.0.1');
        $login_attempt_model->expects($this->never())
            ->method('record_failure');

        $service = new Auth_service(array(
            'user_model' => $user_model,
            'activity_log_model' => $activity_log_model,
            'login_attempt_model' => $login_attempt_model,
            'ip_address' => '127.0.0.1'
        ));
        $session_data = $service->authenticate('admin', 'StrongPass123!');

        $this->assertSame($user->id, $session_data['user_id']);
        $this->assertSame('admin', $session_data['username']);
        $this->assertSame('0123456789abcdef0123456789abcdef.png', $session_data['profile_image']);
        $this->assertSame('admin', $session_data['role_name']);
        $this->assertSame(4, $session_data['auth_version']);
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

        $login_attempt_model = $this->createMock(AuthLoginAttemptModelStub::class);
        $login_attempt_model->expects($this->once())
            ->method('is_locked')
            ->with('admin', '127.0.0.1')
            ->willReturn(FALSE);
        $login_attempt_model->expects($this->once())
            ->method('record_failure')
            ->with('admin', '127.0.0.1')
            ->willReturn(FALSE);
        $login_attempt_model->expects($this->never())
            ->method('clear_attempts');

        $service = new Auth_service(array(
            'user_model' => $user_model,
            'login_attempt_model' => $login_attempt_model,
            'ip_address' => '127.0.0.1'
        ));

        $this->assertFalse($service->authenticate('admin', 'wrong-password'));
    }

    public function testLockedLoginIsRejectedBeforeUserLookup() {
        $user_model = $this->createMock(AuthUserModelStub::class);
        $user_model->expects($this->never())
            ->method('find_active_by_username');

        $login_attempt_model = $this->createMock(AuthLoginAttemptModelStub::class);
        $login_attempt_model->expects($this->once())
            ->method('is_locked')
            ->with('admin', '192.0.2.10')
            ->willReturn(TRUE);
        $login_attempt_model->expects($this->never())
            ->method('record_failure');

        $service = new Auth_service(array(
            'user_model' => $user_model,
            'login_attempt_model' => $login_attempt_model,
            'ip_address' => '192.0.2.10'
        ));

        $this->assertFalse($service->authenticate(' Admin ', 'not-logged'));
    }

    public function testLoginLockoutStatusUsesNormalizedUsernameAndClientIp() {
        $login_attempt_model = $this->createMock(AuthLoginAttemptModelStub::class);
        $login_attempt_model->expects($this->once())
            ->method('lockout_status')
            ->with('admin', '192.0.2.10')
            ->willReturn(array(
                'locked_until' => '2026-10-01 12:15:00',
                'remaining_seconds' => 900
            ));

        $service = new Auth_service(array(
            'login_attempt_model' => $login_attempt_model,
            'ip_address' => '192.0.2.10'
        ));

        $this->assertSame(array(
            'locked_until' => '2026-10-01 12:15:00',
            'remaining_seconds' => 900
        ), $service->login_lockout_status(' Admin '));
    }

    public function testLogoutWritesOneAuditRecordForAuthenticatedUser() {
        $activity_log_model = $this->createMock(AuthActivityLogModelStub::class);
        $activity_log_model->expects($this->once())
            ->method('insert_activity_log')
            ->with(array(
                'user_id' => 7,
                'action' => 'user_logout',
                'description' => 'Logout Session',
                'ip_address' => '127.0.0.1'
            ))
            ->willReturn(TRUE);

        $service = new Auth_service(array(
            'activity_log_model' => $activity_log_model,
            'ip_address' => '127.0.0.1'
        ));

        $this->assertTrue($service->logout(7));
    }

    public function testLogoutDoesNotWriteAuditRecordForAnonymousUser() {
        $activity_log_model = $this->createMock(AuthActivityLogModelStub::class);
        $activity_log_model->expects($this->never())
            ->method('insert_activity_log');

        $service = new Auth_service(array(
            'activity_log_model' => $activity_log_model,
            'ip_address' => '127.0.0.1'
        ));

        $this->assertFalse($service->logout(0));
    }

    // QA ni para role session data; role ID/name should survive authentication payload building.
    public function testSessionDataContainsRoleInformation() {
        $user = (object) array(
            'id' => 12,
            'first_name' => 'John Paul',
            'last_name' => 'Curib',
            'username' => 'staff-user',
            'profile_image' => '',
            'role_id' => 2,
            'role_name' => 'staff',
            'auth_version' => 2
        );

        $session_data = (new Auth_service())->session_data($user);

        $this->assertSame(array(
            'user_id' => 12,
            'first_name' => 'John Paul',
            'last_name' => 'Curib',
            'username' => 'staff-user',
            'profile_image' => '',
            'role_id' => 2,
            'role_name' => 'staff',
            'auth_version' => 2,
            'must_change_password' => FALSE,
            'password_change_deferred' => FALSE,
            'logged_in' => TRUE
        ), $session_data);
    }
}
