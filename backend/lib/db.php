<?php

declare(strict_types=1);
require __DIR__ . '/../config/config.php';

function pdo(): PDO
{
  static $pdo = null;
  if ($pdo instanceof PDO) {
    return $pdo;
  }
  // 1) DSN per MySQL con host, porta, nome DB e charset
  $dsn = sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
    DB_HOST,
    DB_PORT,
    DB_NAME
  );

  // 2) Opzioni PDO 
  $options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
  ];

  // 3) Crea e memorizza la connessione
  $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

  return $pdo;
}
