<?php
include_once('db.php');
include_once('model.php');

$user_id = isset($_GET['user'])
    ? (int) $_GET['user']
    : null;

if ($user_id) {
    $conn = get_connect();

    // Get transactions balances
    $transactions = get_user_transactions_balances($user_id, $conn);

    echo json_encode($transactions);
} else {
    echo '[]'; //Выводим пустой массив если проверка не пройдена, нужно для стандартизации вывода API
}
