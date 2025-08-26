<?php

declare(strict_types=1);

//l'operazione va a buon fine
function json_ok(array $data = []): void
{
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['ok' => true] + $data, JSON_UNESCAPED_UNICODE);
  exit;
}

//l'operazione NON va a buon fine
function json_error(string $code, int $status = 400, array $extra = []): void
{
  http_response_code($status);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['ok' => false, 'error' => $code] + $extra, JSON_UNESCAPED_UNICODE);
  exit;
}
