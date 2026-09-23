<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$safe_heading = htmlspecialchars(strip_tags(isset($heading) ? (string) $heading : 'Application Error'), ENT_QUOTES, 'UTF-8');
$safe_message = htmlspecialchars(strip_tags(isset($message) ? (string) $message : 'An application error occurred.'), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title><?php echo $safe_heading; ?></title>
</head>
<body>
    <h1><?php echo $safe_heading; ?></h1>
    <p><?php echo $safe_message; ?></p>
    <p><a href="javascript:history.back()">Go Back</a></p>
</body>
</html>
