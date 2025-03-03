<?php

/**
 * Return list of users.
 */
function get_users(PDO $conn) : array
{
    $data = $conn->query("
        SELECT 
            users.id, users.name 
        FROM users 
        INNER JOIN user_accounts AS ua 
            ON ua.user_id = users.id 
        INNER JOIN transactions AS t 
            ON t.account_from = ua.id OR t.account_to = ua.id 
        GROUP BY users.id")->fetchAll();

    return array_column($data, 'name', 'id');
}

/**
 * Return transactions balances of given user.
 */
function get_user_transactions_balances($user_id, PDO $conn) : array
{
    if (!is_integer($user_id) && $user_id < 0) { // Базовая проверка, она есть и в data.php, но лучше перестраховатся если будем использовать функцию в будущем
        return [];
    }

    $data = $conn->query(
        "
    SELECT
        strftime('%m',t.trdate) AS month,
        SUM(
            CASE WHEN t.account_to = t.account_from THEN 0 WHEN t.account_to = ua.id THEN t.amount ELSE -t.amount END
        ) AS `amount`,
        COUNT(amount) as `count`
    FROM
        users
    INNER JOIN user_accounts AS ua
        ON
            ua.user_id = users.id
    INNER JOIN transactions AS t
        ON
            t.account_from = ua.id OR t.account_to = ua.id
    WHERE
        users.id = $user_id
    GROUP BY
        month")->fetchAll(PDO::FETCH_ASSOC);

    return $data;
}
