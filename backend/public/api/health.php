<?php
require __DIR__ . '/../../config/cors.php';
require __DIR__ . '/../../lib/response.php';

// Data/ora corrente in formato ISO 8601
$now = date('c');

// Risposta JSON
json_ok([
  'service' => 'travel-journal-app-api',
  'time' => $now
]);
