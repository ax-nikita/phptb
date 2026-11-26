# User transactions information

A small PHP and SQLite application that shows monthly transaction totals for a selected user.

## Run locally

PHP 7.4 or later with PDO SQLite enabled.

```sh
php -S 127.0.0.1:8000 -t public
```

Open http://127.0.0.1:8000. The repository includes a sample SQLite database. Keep the document root set to `public/` so the database and PHP support files are not served directly.

## API

`GET /data.php?user=10&year=2024`

```json
[{"month":"01","amount":300,"count":5},{"month":"02","amount":300,"count":5},{"month":"03","amount":300,"count":5}]
```

Incoming transfers increase the total and outgoing transfers decrease it. Transfers between accounts belonging to the same user have a net amount of zero and count as one transaction. The report assumes the sample database's ISO-formatted timestamps and amounts with two decimal places. Amounts are converted to integer cents before summation.

The year defaults to the latest year in the database. Invalid parameters return HTTP 400 and an empty array; users without transactions return an empty array with HTTP 200.

## Tests

```sh
php test.php
```

Tests use a separate in-memory SQLite database.

This is a demo with sample data, not a banking ledger or an authenticated reporting service. Do not expose it with real customer data.
