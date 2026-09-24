<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$app_error_heading = isset($heading) ? (string) $heading : 'Page or Record Not Found';
$app_error_message = isset($message) ? (string) $message : 'The requested page or record could not be found.';
$app_error_code = 404;
$app_error_causes = array(
    'The record may have been deleted or moved.',
    'The link may be outdated or incomplete.',
    'The requested route may no longer exist.'
);
$app_error_steps = array(
    'Return to the previous list and refresh the data.',
    'Open the item again from the current page instead of an old bookmark.'
);

require __DIR__ . '/_app_error.php';
