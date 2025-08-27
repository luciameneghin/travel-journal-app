<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../lib/response.php';
require_once __DIR__ . '/../../lib/db.php';

try {
  $pdo = pdo(); //uso funzione in lib/db.php

  $ver = $pdo->query('SELECT VERSION() AS v')->fetchColumn();
  $pdo->query('SELECT 1')->fetch();

  json_ok(['db' => 'ok', 'version' => $ver]);
} catch (Throwable $e) {
  json_error('DB_CONNECTION_FAILED', 500, ['detail' => $e->getMessage()]);
}
