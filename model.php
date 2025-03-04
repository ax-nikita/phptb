<?php

/**
 * Return list of users.
 */
function get_users(PDO $conn) : array
{
    $data = $conn->query("
        SELECT 
            users.id,
            users.name
        FROM users
        WHERE EXISTS(SELECT 1
                     FROM user_accounts as ua
                              INNER JOIN transactions AS t
                                         ON
                                             t.account_from = ua.id OR t.account_to = ua.id
             WHERE ua.user_id = users.id)")->fetchAll();

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
    SELECT month, SUM(amount) as amount, COUNT(1) as count
    FROM (SELECT t.id                     as tid,
                 strftime('%m', t.trdate) AS month,
                 CASE
                     WHEN (SELECT COUNT(1)
                           FROM user_accounts AS ua_2
                           WHERE ua_2.id IN (t.account_to, t.account_from)
                           GROUP BY ua_2.user_id
                           LIMIT 1) = 2 THEN 0
                     WHEN t.account_to = ua.id THEN t.amount
                     ELSE -t.amount END
                                          AS `amount`
          FROM user_accounts AS ua
                   INNER JOIN transactions AS t
                              ON
                                  t.account_from = ua.id OR t.account_to = ua.id
          WHERE ua.user_id = $user_id
          GROUP BY tid)
    GROUP BY month")->fetchAll(PDO::FETCH_ASSOC);

    return $data;
}
