<?php
/**
 * application/views/errors/cli/error_php.php | 2026-09-22
 * PHP diagnostic output ni sa CLI; diri makita dayon ang file ug line sa issue.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

echo "\nPHP ERROR: " . strip_tags((string) $message) . "\n";
echo (string) $filepath . ':' . (int) $line . "\n\n";
