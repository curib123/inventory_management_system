<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$app_error_heading = 'Database Operation Failed';
$app_error_message = 'The application could not complete the requested database operation.';
$app_error_code = http_response_code() ?: 500;
$app_error_causes = array(
    'The database service may be temporarily unavailable.',
    'A connection, query, or transaction may have failed.',
    'The requested data may conflict with a related record or database constraint.'
);
$app_error_steps = array(
    'Retry the action once after refreshing the page.',
    'If the problem continues, ask the administrator to check the application and database logs.'
);

require __DIR__ . '/_app_error.php';
