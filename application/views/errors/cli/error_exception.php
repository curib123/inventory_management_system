<?php
/**
 * application/views/errors/cli/error_exception.php | 2026-09-22
 * CLI exception output ni; clean ra para dali makita ang actual issue.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

echo "\nEXCEPTION: " . strip_tags(isset($heading) ? (string) $heading : 'Application Error') . "\n";
echo strip_tags(isset($message) ? (string) $message : 'An unexpected error occurred.') . "\n\n";
