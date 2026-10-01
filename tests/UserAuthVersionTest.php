<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/system/core/Model.php';
require_once dirname(__DIR__) . '/application/models/User_model.php';

class Issue012UserDatabaseStub {
    public $sets = array();
    public $wheres = array();
    public $updates = array();

    public function set($field, $value, $escape = NULL) {
        $this->sets[] = array($field, $value, $escape);
        return $this;
    }

    public function where($field, $value = NULL) {
        $this->wheres[] = array($field, $value);
        return $this;
    }

    public function update($table, $data = NULL, $where = NULL) {
        $this->updates[] = array($table, $data, $where);
        return TRUE;
    }
}

class Issue012TestUserModel extends User_model {
    public $fake_db;

    public function __get($key) {
        if ($key === 'db') {
            return $this->fake_db;
        }

        return parent::__get($key);
    }
}

class UserAuthVersionTest extends TestCase {
    private function createModel(Issue012UserDatabaseStub $database) {
        $model = (new ReflectionClass(Issue012TestUserModel::class))->newInstanceWithoutConstructor();
        $model->fake_db = $database;

        return $model;
    }

    public function testAdminPasswordResetIncrementsAuthVersionAtomically() {
        $database = new Issue012UserDatabaseStub();
        $model = $this->createModel($database);

        $this->assertTrue($model->save(array('password' => 'new-hash'), 19));

        $this->assertSame(array('auth_version', 'auth_version + 1', FALSE), $database->sets[0]);
        $this->assertSame(array('id', 19), $database->wheres[0]);
        $this->assertSame('users', $database->updates[0][0]);
    }

    public function testProfileOnlyUpdateDoesNotIncrementAuthVersion() {
        $database = new Issue012UserDatabaseStub();
        $model = $this->createModel($database);

        $this->assertTrue($model->save(array('first_name' => 'Updated'), 19));

        $this->assertSame(array(), $database->sets);
    }

    public function testSelfServicePasswordChangeAlsoIncrementsAuthVersion() {
        $database = new Issue012UserDatabaseStub();
        $model = $this->createModel($database);

        $this->assertTrue($model->update_password_hash(19, 'changed-hash', FALSE));

        $this->assertSame(array('auth_version', 'auth_version + 1', FALSE), $database->sets[0]);
        $this->assertSame(array('password' => 'changed-hash', 'must_change_password' => 0), $database->updates[0][1]);
    }
}
