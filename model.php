<?php

declare(strict_types=1);

function get_users(PDO $conn): array
{
    $statement = $conn->query(
        'SELECT u.id, u.name
         FROM users AS u
         WHERE EXISTS (
             SELECT 1
             FROM user_accounts AS a
             JOIN transactions AS t ON t.account_from = a.id OR t.account_to = a.id
             WHERE a.user_id = u.id
         )
         ORDER BY u.name, u.id'
    );

    return array_column($statement->fetchAll(PDO::FETCH_ASSOC), 'name', 'id');
}

function get_transaction_years(PDO $conn): array
{
    $years = $conn->query(
        "SELECT DISTINCT strftime('%Y', trdate) AS year
         FROM transactions
         WHERE strftime('%Y', trdate) BETWEEN '0001' AND '9998'
         ORDER BY year DESC"
    )->fetchAll(PDO::FETCH_COLUMN);

    return array_map('intval', $years);
}

function get_user_transactions_balances(int $user_id, PDO $conn, ?int $year = null): array
{
    if ($user_id <= 0) {
        return [];
    }

    $year = $year ?? (get_transaction_years($conn)[0] ?? (int) date('Y'));

    if ($year < 1 || $year > 9998) {
        throw new InvalidArgumentException('Invalid year.');
    }

    $statement = $conn->prepare(
        "SELECT strftime('%m', t.trdate) AS month,
                SUM(
                    (CASE WHEN dst.user_id = :credit_user THEN 1 ELSE 0 END
                     - CASE WHEN src.user_id = :debit_user THEN 1 ELSE 0 END)
                    * CAST(ROUND(t.amount * 100) AS INTEGER)
                ) AS amount_cents,
                COUNT(*) AS count
         FROM transactions AS t
         JOIN user_accounts AS src ON src.id = t.account_from
         JOIN user_accounts AS dst ON dst.id = t.account_to
         WHERE (src.user_id = :source_user OR dst.user_id = :target_user)
           AND t.trdate >= :start_date
           AND t.trdate < :end_date
         GROUP BY month
         ORDER BY month"
    );

    foreach ([':credit_user', ':debit_user', ':source_user', ':target_user'] as $parameter) {
        $statement->bindValue($parameter, $user_id, PDO::PARAM_INT);
    }

    $statement->bindValue(':start_date', sprintf('%04d-01-01', $year));
    $statement->bindValue(':end_date', sprintf('%04d-01-01', $year + 1));
    $statement->execute();

    return array_map(static function (array $row): array {
        return [
            'month' => $row['month'],
            'amount' => (float) ($row['amount_cents'] / 100),
            'count' => (int) $row['count'],
        ];
    }, $statement->fetchAll(PDO::FETCH_ASSOC));
}
