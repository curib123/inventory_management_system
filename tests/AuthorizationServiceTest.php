<?php

use PHPUnit\Framework\TestCase;

class AuthorizationUserModelStub {

    private $permissions;

    // Test stub helper ni para permission keys; Authorization_service mao ang tested business layer, dili database.
    public function __construct($permissions) {
        $this->permissions = (array) $permissions;
    }

    // Test stub query ni para permission keys; tawagon ra sa Authorization_service during unit tests.
    public function get_user_permission_keys($user_id) {
        return (int) $user_id > 0 ? $this->permissions : array();
    }
}

class AuthorizationServiceTest extends TestCase {

    // QA ni para exact permission rule; one matching permission should grant access and unknown permission should fail.
    public function testHasPermissionUsesPermissionKeysFromRepository() {
        $service = new Authorization_service(array(
            'user_model' => new AuthorizationUserModelStub(array('products.view', 'stock.history'))
        ));

        $this->assertTrue($service->has_permission(7, 'products.view'));
        $this->assertFalse($service->has_permission(7, 'roles.delete'));
    }

    // QA ni para any-permission rule; basta usa sa requested permissions naa sa user, valid ang access.
    public function testHasAnyPermissionAcceptsAnyMatchingPermission() {
        $service = new Authorization_service(array(
            'user_model' => new AuthorizationUserModelStub(array('roles.permissions'))
        ));

        $this->assertTrue($service->has_any_permission(4, array('roles.edit', 'roles.permissions')));
        $this->assertFalse($service->has_any_permission(4, array('users.edit', 'users.delete')));
    }

    // QA ni para post-login route priority; first authorized route should follow one centralized service order.
    public function testFirstAuthorizedRouteReturnsFirstPermittedDestination() {
        $service = new Authorization_service(array(
            'user_model' => new AuthorizationUserModelStub(array('reports.view', 'users.view'))
        ));

        $this->assertSame('reports', $service->first_authorized_route(3));
    }

    // QA ni para account with no page permission; service should return null so controller can show no-access page.
    public function testFirstAuthorizedRouteReturnsNullWithoutViewPermission() {
        $service = new Authorization_service(array(
            'user_model' => new AuthorizationUserModelStub(array('stock.adjust'))
        ));

        $this->assertNull($service->first_authorized_route(3));
    }
}
