<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/application/hooks/Session_version_guard.php';

class SessionVersionGuardTest extends TestCase {
    public function testOnlyCurrentPositiveSessionVersionIsAccepted() {
        $this->assertTrue(Session_version_guard::versions_match(3, 3));
        $this->assertFalse(Session_version_guard::versions_match(2, 3));
        $this->assertFalse(Session_version_guard::versions_match(NULL, 3));
        $this->assertFalse(Session_version_guard::versions_match(0, 0));
        $this->assertFalse(Session_version_guard::versions_match(3, NULL));
    }
}
