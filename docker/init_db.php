<?php
// Imports database/*.sql into an empty database. Safe to run on every start.
$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    getenv('DB_HOST') ?: 'localhost', getenv('DB_PORT') ?: '3306', getenv('DB_NAME') ?: 'pawpaws');
$opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if (getenv('DB_SSL')) {
    $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    $opts[PDO::MYSQL_ATTR_SSL_CA] = '/etc/ssl/certs/ca-certificates.crt';
}
$pdo = new PDO($dsn, getenv('DB_USER') ?: 'root', getenv('DB_PASS') ?: '', $opts);

if ($pdo->query("SHOW TABLES LIKE 'users'")->fetch()) { echo "DB already initialised\n"; exit; }

foreach (['database.sql', 'add_pets_20.sql'] as $f) {
    $sql = file_get_contents(__DIR__ . '/database/' . $f);
    // Managed databases don't allow dropping/creating schemas; use the provided one.
    $sql = preg_replace('/^\s*(DROP DATABASE|CREATE DATABASE|USE)\b[^;]*;/mi', '', $sql);
    $pdo->exec($sql);
    echo "Imported $f\n";
}
