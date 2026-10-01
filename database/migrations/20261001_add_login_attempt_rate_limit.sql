CREATE TABLE IF NOT EXISTS login_attempts (
    id INT NOT NULL AUTO_INCREMENT,
    scope_type ENUM('username', 'ip') NOT NULL,
    identifier_hash CHAR(64) NOT NULL,
    attempt_count INT UNSIGNED NOT NULL DEFAULT 0,
    window_started_at DATETIME NOT NULL,
    locked_until DATETIME DEFAULT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_login_attempt_scope (scope_type, identifier_hash),
    KEY idx_login_attempts_locked_until (locked_until),
    KEY idx_login_attempts_updated_at (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
