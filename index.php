<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2019, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to use the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the
 * following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 */

if (version_compare(PHP_VERSION, '5.6.0', '<')) {
    exit('PHP 5.6 or newer is required.');
}

if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'development');
}

if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

date_default_timezone_set('Asia/Dhaka');

$root_path = __DIR__;
$system_path = $root_path . DIRECTORY_SEPARATOR . 'system';
$application_folder = $root_path . DIRECTORY_SEPARATOR . 'application';

if (defined('STDIN')) {
    chdir(dirname(__FILE__));
}

if (is_dir($application_folder) === FALSE) {
    exit('Your application folder path does not appear to be correct. Please open the following file and correct this: ' . __FILE__);
}

if (is_dir($system_path) === FALSE) {
    exit('The CodeIgniter system folder is missing. Restore the system folder to: ' . $system_path);
}

define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
define('BASEPATH', str_replace('\\', '/', realpath($system_path)) . '/');
define('FCPATH', str_replace('\\', '/', __FILE__));
define('SYSDIR', basename(BASEPATH));

if (is_dir($application_folder) === TRUE) {
    define('APPPATH', str_replace('\\', '/', realpath($application_folder)) . '/');
    define('VIEWPATH', APPPATH . 'views/');
}

require_once BASEPATH . 'core/CodeIgniter.php';
