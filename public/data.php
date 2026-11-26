<?php

declare(strict_types=1);

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../model.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    echo '[]';
    exit;
}

$user_id = filter_var($_GET['user'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
$year = isset($_GET['year']) ? filter_var($_GET['year'], FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 9998],
]) : null;

if ($user_id === false || $year === false) {
    http_response_code(400);
    echo '[]';
    exit;
}

try {
    echo json_encode(
        get_user_transactions_balances($user_id, get_connect(), $year),
        JSON_THROW_ON_ERROR
    );
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    echo '{"error":"Unable to load transactions."}';
}
