<?php
require dirname(__DIR__) . '/vendor/autoload.php';
$env = Dotenv\Dotenv::parse(file_get_contents(dirname(__DIR__) . '/.env'));
if (($env['APP_ENV'] ?? '') !== 'testing' || ($env['DB_DATABASE'] ?? '') !== 'logigate_testing' || ! in_array($env['DB_HOST'] ?? '', ['127.0.0.1', 'localhost'], true)) { throw new RuntimeException('Unsafe source database.'); }
$name = 'logigate_testing_v1_' . bin2hex(random_bytes(5));
$pdo = new PDO('mysql:host=' . $env['DB_HOST'] . ';port=' . ($env['DB_PORT'] ?? 3306) . ';dbname=logigate_testing;charset=utf8mb4', $env['DB_USERNAME'], $env['DB_PASSWORD'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
if ($pdo->query('SELECT DATABASE()')->fetchColumn() !== 'logigate_testing') { throw new RuntimeException('Source mismatch.'); }
$tables = $pdo->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE' AND ENGINE = 'InnoDB'")->fetchAll(PDO::FETCH_COLUMN);
$pdo->exec('CREATE DATABASE `' . $name . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$pdo->exec('USE `' . $name . '`');
if ($pdo->query('SELECT DATABASE()')->fetchColumn() !== $name) { throw new RuntimeException('Target mismatch.'); }
$pdo->exec('SET FOREIGN_KEY_CHECKS=0');
foreach ($tables as $table) {
    $quoted = str_replace('`', '``', $table);
    $ddl = $pdo->query('SHOW CREATE TABLE `logigate_testing`.`' . $quoted . '`')->fetch(PDO::FETCH_NUM)[1];
    $pdo->exec($ddl);
    $pdo->exec('INSERT INTO `' . $quoted . '` SELECT * FROM `logigate_testing`.`' . $quoted . '`');
}
$pdo->exec('SET FOREIGN_KEY_CHECKS=1');
file_put_contents(__DIR__ . '/.v1-sandbox.json', json_encode(['database' => $name, 'source' => 'logigate_testing']));
echo 'Created isolated sandbox: ' . $name . PHP_EOL;
