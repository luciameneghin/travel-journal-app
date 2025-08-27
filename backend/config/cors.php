<?php
require_once __DIR__ . '/config.php';

// 1) Recupera l'origin della richiesta
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// 2) Se è un origin permesso, aggiungi il relativo header
if (in_array($origin, ALLOWED_ORIGINS, true)) {
  header("Access-Control-Allow-Origin: $origin");
}

// 3) Header più comuni per CORS
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

// 4) Se è una preflight request (OPTIONS), rispondi subito e non proseguire
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204);
  exit;
}
