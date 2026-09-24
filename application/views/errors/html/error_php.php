<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$app_error_heading = 'Application Runtime Error';
$app_error_message = (defined('ENVIRONMENT') && ENVIRONMENT === 'development' && isset($message))
    ? (string) $message
    : 'The application encountered a runtime problem while processing the request.';
$app_error_code = http_response_code() ?: 500;
$app_error_causes = array(
    'A PHP runtime error may have stopped the request.',
    'A required file, configuration value, or dependency may be missing.',
    'Unexpected data may have reached application code that could not process it.'
);
$app_error_steps = array(
    'Retry the action once after refreshing the page.',
    'If the problem continues, review the application log and the affected controller or service.'
);

require __DIR__ . '/_app_error.php';
