<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/system/core/Model.php';
require_once dirname(__DIR__) . '/application/models/Login_attempt_model.php';

class LoginAttemptDatabaseStub {
    public $queries = array();

    public function query($sql, $bindings = array()) {
        $this->queries[] = array('sql' => $sql, 'bindings' => $bindings);

        return new class {
            public function num_rows() {
                return 0;
            }
        };
    }
}

class LoginAttemptModelTest extends TestCase {
    private function createModel(LoginAttemptDatabaseStub $database) {
        $model = (new ReflectionClass(Login_attempt_model::class))->newInstanceWithoutConstructor();
        $model->db = $database;

        return $model;
    }

    public function testLockCheckUsesSeparateHashedUsernameAndIpKeys() {
        $database = new LoginAttemptDatabaseStub();
        $model = $this->createModel($database);

        $this->assertFalse($model->is_locked(' Admin ', '192.0.2.10'));
        $query = $database->queries[0];

        $this->assertStringContainsString('locked_until > NOW()', $query['sql']);
        $this->assertSame(array(
            'username',
            hash('sha256', 'admin'),
            'ip',
            hash('sha256', '192.0.2.10')
        ), $query['bindings']);
        $this->assertStringNotContainsString('Admin', $query['sql'] . implode('', $query['bindings']));
    }

    public function testDifferentUsernamesHaveIndependentAccountBuckets() {
        $database = new LoginAttemptDatabaseStub();
        $model = $this->createModel($database);

        $model->is_locked('alice', '192.0.2.10');
        $model->is_locked('bob', '192.0.2.10');

        $this->assertNotSame(
            $database->queries[0]['bindings'][1],
            $database->queries[1]['bindings'][1]
        );
        $this->assertSame(
            $database->queries[0]['bindings'][3],
            $database->queries[1]['bindings'][3]
        );
    }

    public function testFailureUsesAtomicWindowAndCooldownUpsertsForBothKeys() {
        $database = new LoginAttemptDatabaseStub();
        $model = $this->createModel($database);

        $this->assertFalse($model->record_failure('admin', '192.0.2.10'));

        $username_upsert = $database->queries[0];
        $ip_upsert = $database->queries[1];

        $this->assertStringContainsString('ON DUPLICATE KEY UPDATE', $username_upsert['sql']);
        $this->assertStringContainsString('attempt_count >= 4', $username_upsert['sql']);
        $this->assertStringContainsString('INTERVAL 10 MINUTE', $username_upsert['sql']);
        $this->assertStringContainsString('INTERVAL 15 MINUTE', $username_upsert['sql']);
        $this->assertSame('username', $username_upsert['bindings'][0]);
        $this->assertSame('ip', $ip_upsert['bindings'][0]);
        $this->assertStringStartsWith(
            'SELECT 1 FROM login_attempts WHERE locked_until > NOW()',
            $database->queries[3]['sql']
        );
    }

    public function testSuccessfulLoginClearsBothIdentityBuckets() {
        $database = new LoginAttemptDatabaseStub();
        $model = $this->createModel($database);

        $model->clear_attempts('admin', '192.0.2.10');

        $this->assertStringContainsString('DELETE FROM login_attempts WHERE', $database->queries[0]['sql']);
        $this->assertSame(array(
            'username',
            hash('sha256', 'admin'),
            'ip',
            hash('sha256', '192.0.2.10')
        ), $database->queries[0]['bindings']);
    }
}
