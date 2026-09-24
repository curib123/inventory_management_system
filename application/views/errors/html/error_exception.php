<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$app_error_heading = 'Unexpected Server Error';
$app_error_message = (defined('ENVIRONMENT') && ENVIRONMENT === 'development' && isset($message))
    ? (string) $message
    : 'The server encountered an unexpected problem while processing the request.';
$app_error_code = http_response_code() ?: 500;
$app_error_causes = array(
    'An unexpected application exception may have occurred.',
    'A required service, file, or dependency may be unavailable.',
    'The operation may have encountered invalid or inconsistent data.'
);
$app_error_steps = array(
    'Retry the action after refreshing the page.',
    'If it continues, check the server logs for the matching request and underlying exception.'
);

require __DIR__ . '/_app_error.php';
