USE inventory_management_db;

SET @database_name = DATABASE();

SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @database_name
       AND TABLE_NAME = 'users'
       AND COLUMN_NAME = 'first_name') = 0,
    'ALTER TABLE users ADD COLUMN first_name VARCHAR(100) NULL AFTER id',
    'SELECT 1'
);
PREPARE statement FROM @sql;
EXECUTE statement;
DEALLOCATE PREPARE statement;

SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @database_name
       AND TABLE_NAME = 'users'
       AND COLUMN_NAME = 'middle_name') = 0,
    'ALTER TABLE users ADD COLUMN middle_name VARCHAR(100) NULL AFTER first_name',
    'SELECT 1'
);
PREPARE statement FROM @sql;
EXECUTE statement;
DEALLOCATE PREPARE statement;

SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @database_name
       AND TABLE_NAME = 'users'
       AND COLUMN_NAME = 'last_name') = 0,
    'ALTER TABLE users ADD COLUMN last_name VARCHAR(100) NULL AFTER middle_name',
    'SELECT 1'
);
PREPARE statement FROM @sql;
EXECUTE statement;
DEALLOCATE PREPARE statement;

UPDATE users
SET first_name = CASE
        WHEN first_name IS NULL OR TRIM(first_name) = '' THEN username
        ELSE first_name
    END,
    last_name = CASE
        WHEN last_name IS NULL OR TRIM(last_name) = '' THEN 'User'
        ELSE last_name
    END;

ALTER TABLE users
    MODIFY first_name VARCHAR(100) NOT NULL,
    MODIFY middle_name VARCHAR(100) NULL,
    MODIFY last_name VARCHAR(100) NOT NULL;
