<?php
/**
 * application/views/errors/cli/error_404.php | 2026-09-22
 * CLI 404 error output.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

echo "\nERROR: " . strip_tags((string) $heading) . "\n";
echo strip_tags((string) $message) . "\n\n";
