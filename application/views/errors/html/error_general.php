<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$app_error_heading = isset($heading) ? (string) $heading : 'Application Error';
$app_error_message = isset($message) ? (string) $message : 'The request could not be completed.';
$app_error_code = http_response_code();
$app_error_causes = array(
    'The request may contain invalid or outdated data.',
    'Your session or permissions may no longer match the requested action.',
    'A server-side operation may have failed unexpectedly.'
);
$app_error_steps = array(
    'Review the message above, then retry the action.',
    'Refresh the page if the screen has been open for a long time.',
    'If the problem continues, contact the administrator with the action you were performing.'
);

require __DIR__ . '/_app_error.php';
