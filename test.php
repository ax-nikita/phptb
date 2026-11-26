<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/model.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

function expect_same($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($message . ': ' . var_export($actual, true));
    }
}

$conn = get_connect(':memory:');
init_db($conn);

expect_same(
    [
        ['month' => '01', 'amount' => 300.0, 'count' => 5],
        ['month' => '02', 'amount' => 300.0, 'count' => 5],
        ['month' => '03', 'amount' => 300.0, 'count' => 5],
    ],
    get_user_transactions_balances(10, $conn, 2024),
    'Alice monthly totals'
);

expect_same([], get_user_transactions_balances(999, $conn, 2024), 'Unknown user');
expect_same([], get_user_transactions_balances(0, $conn, 2024), 'Invalid user');
expect_same([], get_user_transactions_balances(10, $conn, 2023), 'Empty year');
expect_same([2024], get_transaction_years($conn), 'Available years');

$conn->exec("INSERT INTO transactions (account_from, account_to, amount, trdate) VALUES
    (10, 10, 12.34, '2025-01-01 00:00:00'),
    (10, 11, 25.50, '2025-01-01 01:00:00'),
    (10, 12, 0.10, '2025-01-01 02:00:00'),
    (12, 11, 0.30, '2025-01-01 03:00:00'),
    (12, 10, 0.20, '2025-01-01 04:00:00'),
    (10, 12, 10.25, '2025-12-31 23:59:59'),
    (12, 10, 99.00, '2026-01-01 00:00:00')");

expect_same(
    [
        ['month' => '01', 'amount' => 0.4, 'count' => 5],
        ['month' => '12', 'amount' => -10.25, 'count' => 1],
    ],
    get_user_transactions_balances(10, $conn, 2025),
    'Internal transfers, decimal amounts and year boundaries'
);

expect_same(
    [['month' => '01', 'amount' => 99.0, 'count' => 1]],
    get_user_transactions_balances(10, $conn),
    'Latest year by default'
);

expect_same([2026, 2025, 2024], get_transaction_years($conn), 'Year order');
expect_same(true, isset(get_users($conn)[10]), 'User with transactions');
expect_same(false, isset(get_users($conn)[15]), 'User without transactions');

try {
    get_user_transactions_balances(10, $conn, 9999);
    throw new RuntimeException('Invalid year accepted');
} catch (InvalidArgumentException $exception) {
}

echo "All tests passed.\n";
