<?php
/**
 * Тестовый HTTP-сервис: выдача списка сотрудников в JSON (только чтение).
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

$config = require dirname(__DIR__) . '/config.php';

function json_response(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function get_authorization_header(): string
{
    if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        return trim((string) $_SERVER['HTTP_AUTHORIZATION']);
    }
    if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        return trim((string) $_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
    }
    // CGI/FastCGI: иногда Authorization не попадает в $_SERVER
    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        foreach ($headers as $name => $value) {
            if (strcasecmp($name, 'Authorization') === 0) {
                return trim((string) $value);
            }
        }
    }
    return '';
}

function is_authorized(array $auth): bool
{
    $header = get_authorization_header();

    if ($header !== '' && preg_match('/^Bearer\s+(\S+)$/i', $header, $m)) {
        return hash_equals($auth['token'], $m[1]);
    }

    $user = $_SERVER['PHP_AUTH_USER'] ?? null;
    $pass = $_SERVER['PHP_AUTH_PW'] ?? null;

    if ($user === null && $header !== '' && preg_match('/^Basic\s+(\S+)$/i', $header, $m)) {
        $decoded = base64_decode($m[1], true);
        if ($decoded !== false && str_contains($decoded, ':')) {
            [$user, $pass] = explode(':', $decoded, 2);
        }
    }

    if ($user === null || $pass === null) {
        return false;
    }

    return hash_equals($auth['username'], (string) $user)
        && hash_equals($auth['password'], (string) $pass);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = rtrim($path, '/') ?: '/';

// Health без авторизации — удобно проверять, что сервис жив
if ($method === 'GET' && ($path === '/health' || $path === '/public/health')) {
    json_response(200, [
        'status' => 'ok',
        'service' => 'employees-api',
        'mode' => 'read-only',
    ]);
}

$employeesPaths = ['/employees', '/api/employees', '/public/employees', '/public/api/employees'];
$isEmployees = in_array($path, $employeesPaths, true) || $path === '/';

if (!$isEmployees) {
    json_response(404, [
        'error' => 'not_found',
        'message' => 'Эндпоинт не найден. Используйте GET /employees',
    ]);
}

// Изменение данных запрещено всегда (даже с верными учётными данными)
if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
    json_response(405, [
        'error' => 'method_not_allowed',
        'message' => 'Сервис только для чтения. Изменение данных запрещено.',
        'allowed' => ['GET', 'HEAD'],
    ]);
}

if ($method !== 'GET' && $method !== 'HEAD') {
    json_response(405, [
        'error' => 'method_not_allowed',
        'message' => 'Поддерживается только GET.',
        'allowed' => ['GET', 'HEAD'],
    ]);
}

if (!is_authorized($config['auth'])) {
    header('WWW-Authenticate: Basic realm="Employees API (read-only)"');
    json_response(401, [
        'error' => 'unauthorized',
        'message' => 'Требуется авторизация: Basic Auth (логин/пароль) или Bearer-токен.',
    ]);
}

$payload = [
    'count' => count($config['employees']),
    'employees' => $config['employees'],
];

if ($method === 'HEAD') {
    http_response_code(200);
    exit;
}

json_response(200, $payload);
