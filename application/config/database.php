<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$active_group = 'default';
$query_builder = TRUE;

$db_hostname = getenv('INVENTORY_DB_HOST');
$db_username = getenv('INVENTORY_DB_USER');
$db_password = getenv('INVENTORY_DB_PASSWORD');
$db_database = getenv('INVENTORY_DB_NAME');
$db_port = getenv('INVENTORY_DB_PORT');

$db['default'] = array(
    'dsn'      => '',
    'hostname' => ($db_hostname !== FALSE && $db_hostname !== '') ? $db_hostname : 'localhost',
    'username' => ($db_username !== FALSE && $db_username !== '') ? $db_username : 'root',
    'password' => ($db_password !== FALSE) ? $db_password : '',
    'database' => ($db_database !== FALSE && $db_database !== '') ? $db_database : 'inventory_management_db',
    'dbdriver' => 'mysqli',
    'dbprefix' => '',
    'pconnect' => FALSE,
    'db_debug' => (ENVIRONMENT !== 'production'),
    'cache_on' => FALSE,
    'cachedir' => '',
    'char_set' => 'utf8mb4',
    'dbcollat' => 'utf8mb4_unicode_ci',
    'swap_precedence' => 'auto',
    'encrypt' => FALSE,
    'compress' => FALSE,
    'stricton' => TRUE,
    'failover' => array(),
    'save_queries' => (ENVIRONMENT !== 'production'),
    'port' => ($db_port !== FALSE && $db_port !== '') ? (int) $db_port : 3306
);
