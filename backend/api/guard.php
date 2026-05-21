<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/helper/responses.php';

session_start();

$path = isset($_GET['path']) ? trim((string)$_GET['path'], '/') : '';

if ($path === 'frontend/modules/login/view.html') {
    serveView($path);
    exit;
}

if (empty($_SESSION['is_login']) || empty($_SESSION['user']['id'])) {
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';

    if (strpos($accept, 'text/html') !== false) {
        header('Location: /frontend/modules/login/view.html');
        exit;
    }

    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    json_error('UNAUTHENTICATED', 401);
}

serveView($path);

function serveView(string $path): void
{
    $root = realpath(__DIR__ . '/../..');
    $file = realpath($root . '/' . $path);

    if (!$root || !$file || strpos($file, $root . DIRECTORY_SEPARATOR) !== 0 || !is_file($file)) {
        http_response_code(404);
        exit;
    }

    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store');

    readfile($file);
}