<?php

use PHPUnit\Framework\TestCase;

class ArchitectureRulesTest extends TestCase {

    // QA rule ni para service files; diri ma-catch if nawala ang domain service nga expected sa proper layer separation.
    public function testDomainServicesExist() {
        $root = dirname(__DIR__);
        $services = array(
            'Authorization_service.php',
            'Category_service.php',
            'Product_service.php',
            'Report_service.php',
            'Role_service.php',
            'Setup_service.php',
            'Stock_service.php',
            'Supplier_service.php',
            'User_service.php'
        );

        foreach ($services as $service) {
            $this->assertFileExists(
                $root . '/application/libraries/' . $service,
                $service . ' should remain in the service layer.'
            );
        }
    }

    // QA rule ni para controllers; controllers dapat HTTP/form/view orchestration ra, dili password hashing, direct writes, or DB transactions.
    public function testControllersDoNotOwnPersistenceOrSecurityBusinessLogic() {
        $source = $this->readPhpDirectory(dirname(__DIR__) . '/application/controllers');

        $this->assertDoesNotMatchRegularExpression('/\$this->db->(?:insert|update|delete|trans_begin|trans_commit|trans_rollback)\s*\(/', $source);
        $this->assertStringNotContainsString('password_hash(', $source);
        $this->assertStringNotContainsString('password_verify(', $source);
        $this->assertStringNotContainsString('User_model->has_permission(', $source);
        $this->assertStringNotContainsString('User_model->has_any_permission(', $source);
        $this->assertStringNotContainsString('Stock_model->create_transaction(', $source);
        $this->assertStringNotContainsString('Stock_model->create_adjustment(', $source);
        $this->assertStringNotContainsString('Role_model->save_with_permissions(', $source);
        $this->assertStringNotContainsString('Role_model->sync_permissions(', $source);
    }

    // QA rule ni para models; write models query/persistence ra and dili na mo-own cross-entity domain orchestration.
    public function testModelsDoNotContainMovedBusinessMethods() {
        $root = dirname(__DIR__) . '/application/models/';
        $product = file_get_contents($root . 'Product_model.php');
        $role = file_get_contents($root . 'Role_model.php');
        $stock = file_get_contents($root . 'Stock_model.php');
        $user = file_get_contents($root . 'User_model.php');

        $this->assertStringNotContainsString('function relationships_are_valid', $product);
        $this->assertStringNotContainsString('function save_with_permissions', $role);
        $this->assertStringNotContainsString('function sync_permissions', $role);
        $this->assertStringNotContainsString('function create_transaction', $stock);
        $this->assertStringNotContainsString('function create_adjustment', $stock);
        $this->assertStringNotContainsString('function has_permission', $user);
        $this->assertStringNotContainsString('function has_any_permission', $user);
        $this->assertStringNotContainsString('function verify_password', $user);
    }

    // QA rule ni para controller delegation; main write flows dapat klaro nga service ang tawagon, dili direct model business method.
    public function testMainWriteFlowsDelegateToServices() {
        $root = dirname(__DIR__) . '/application/controllers/';
        $expectations = array(
            'Categories.php' => 'category_service->save(',
            'Products.php' => 'product_service->save(',
            'Roles.php' => 'role_service->save(',
            'Setup.php' => 'setup_service->create_initial_admin(',
            'Stock.php' => 'stock_service->create_transaction(',
            'Suppliers.php' => 'supplier_service->save(',
            'Users.php' => 'user_service->save('
        );

        foreach ($expectations as $file => $needle) {
            $source = file_get_contents($root . $file);
            $this->assertStringContainsString($needle, $source, $file . ' should delegate its write flow to a service.');
        }
    }

    // Internal QA helper ni para read PHP directory; tawagon ra sulod tests/ArchitectureRulesTest.php para source-level architecture checks.
    private function readPhpDirectory($directory) {
        $source = '';

        foreach (glob(rtrim($directory, '/') . '/*.php') as $file) {
            $source .= "\n" . file_get_contents($file);
        }

        return $source;
    }
}
