<?php
/**
 * application/views/errors/html/error_exception.php | 2026-09-22
 * Safe HTML exception page.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$script_name = isset($_SERVER['SCRIPT_NAME']) ? (string) $_SERVER['SCRIPT_NAME'] : '/index.php';
$base_path = str_replace('\\', '/', dirname($script_name));
$base_path = rtrim($base_path, '/');
$base_path = $base_path === '/' || $base_path === '.' ? '' : $base_path;
$scheme = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^A-Za-z0-9.\-:\[\]]/', '', (string) $_SERVER['HTTP_HOST']) : 'localhost';
$home_url = $scheme . '://' . $host . $base_path . '/dashboard';
$safe_home_url = htmlspecialchars((string) $home_url, ENT_QUOTES, 'UTF-8');

$safe_heading = htmlspecialchars(strip_tags(isset($heading) ? (string) $heading : 'Application Error'), ENT_QUOTES, 'UTF-8');
$safe_message = htmlspecialchars(strip_tags(isset($message) ? (string) $message : 'An unexpected error occurred.'), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpokY6ctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title><?= $safe_heading; ?> </title>
</head>
<body class="min-vh-100 d-flex align-items-center justify-content-center bg-body-tertiary p-4">
    <main class="container text-center bg-white border rounded-4 shadow-sm p-5">
        <div class="badge text-bg-danger fs-4 p-2 mb-3" aria-hidden="true">C</div>
        <p class="text-uppercase small fw-bold text-danger mb-2">Exception</p>
        <h1 class="h2 mb-3"><?= $safe_heading; ?></h1>
        <p class="text-secondary"><?= $safe_message; ?></p>
        <a class="btn btn-danger mt-3" href="<?= $safe_home_url; ?>">Return to the application</a>
    </main>
</body>
</html>
