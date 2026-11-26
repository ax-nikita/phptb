<?php

declare(strict_types=1);

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../model.php';

try {
    $conn = get_connect();
    $users = get_users($conn);
    $years = get_transaction_years($conn);
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    exit('Unable to load transaction data.');
}

$month_names = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User transactions information</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h1>User transactions information</h1>

<form action="data.php" method="get" id="form">
    <label for="user">Select user:</label>
    <select name="user" id="user" required>
        <?php foreach ($users as $id => $name): ?>
            <option value="<?= (int) $id ?>"><?= htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>

    <label for="year">Year:</label>
    <select name="year" id="year" required>
        <?php foreach ($years as $year): ?>
            <option value="<?= $year ?>"><?= $year ?></option>
        <?php endforeach; ?>
    </select>

    <input id="submit" type="submit" value="Show" <?= !$users || !$years ? 'disabled' : '' ?>>
    <p id="status" role="status" aria-live="polite"><?= !$users || !$years ? 'No transactions available.' : '' ?></p>
</form>

<div id="data" hidden>
    <h2>Transactions of <span id="user-name"></span>, <span id="report-year"></span></h2>
    <table>
        <thead>
            <tr><th scope="col">Month</th><th scope="col">Amount</th><th scope="col">Count</th></tr>
        </thead>
        <tbody>
            <?php foreach ($month_names as $index => $month): ?>
                <tr data-id="<?= sprintf('%02d', $index + 1) ?>">
                    <th scope="row"><?= $month ?></th>
                    <td data-field="amount"></td>
                    <td data-field="count"></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script src="script.js" defer></script>
</body>
</html>
