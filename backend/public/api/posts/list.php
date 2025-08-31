<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/cors.php';
require_once __DIR__ . '/../../../lib/response.php';
require_once __DIR__ . '/../../../lib/db.php';

// 1) Metodo: solo GET
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
  json_error('METHOD_NOT_ALLOWED', 405, ['allow' => 'GET']);
}

// 1) Leggi i parametri dalla query string 
$q        = isset($_GET['q'])        ? trim((string)$_GET['q'])        : '';
$mood     = isset($_GET['mood'])     ? trim((string)$_GET['mood'])     : '';
$dateFrom = isset($_GET['dateFrom']) ? trim((string)$_GET['dateFrom']) : '';
$dateTo   = isset($_GET['dateTo'])   ? trim((string)$_GET['dateTo'])   : '';
$minCost  = isset($_GET['minCost'])  ? (string)$_GET['minCost']        : '';
$maxCost  = isset($_GET['maxCost'])  ? (string)$_GET['maxCost']        : '';
$tagsStr  = isset($_GET['tags'])     ? (string)$_GET['tags']           : ''; // es: "mare, estate , spiaggia"

// Ordinamento (consentiamo SOLO questi valori)
$sortBy  = isset($_GET['sortBy']) ? strtolower((string)$_GET['sortBy']) : 'date'; // 'date' | 'cost'
$order   = isset($_GET['order'])  ? strtolower((string)$_GET['order'])  : 'desc'; // 'asc'  | 'desc'

// Paginazione 
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
if ($limit < 1)  $limit = 1;
if ($limit > 50) $limit = 50;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;


$whereParts = [];
$params = [];
$pdo = pdo();

// q: cerca in title/description/place_name
if ($q !== '') {
  $whereParts[] = '(p.title LIKE ? OR p.description LIKE ? OR p.place_name LIKE ?)';
  $like = '%' . $q . '%';
  $params[] = $like;
  $params[] = $like;
  $params[] = $like;
}

// mood: confronto esatto
if ($mood !== '') {
  $whereParts[] = 'p.mood = ?';
  $params[] = $mood;
}

// dateFrom/dateTo: controllo base formato YYYY-MM-DD 
if ($dateFrom !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
  $whereParts[] = 'p.`date` >= ?';
  $params[] = $dateFrom;
}
if ($dateTo !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
  $whereParts[] = 'p.`date` <= ?';
  $params[] = $dateTo;
}

// costi: solo se numerici
if ($minCost !== '' && is_numeric($minCost)) {
  $whereParts[] = 'p.cost_eur >= ?';
  $params[] = (float)$minCost;
}
if ($maxCost !== '' && is_numeric($maxCost)) {
  $whereParts[] = 'p.cost_eur <= ?';
  $params[] = (float)$maxCost;
}

// tags ANY match: "mare,estate" → EXISTS con IN (?, ?, ...)
$tags = [];
if ($tagsStr !== '') {
  foreach (explode(',', $tagsStr) as $t) {
    $s = trim($t);
    if ($s !== '') $tags[] = $s;
  }
  if (!empty($tags)) {
    // creo la lista di ? in base a quanti tag ho
    $placeholders = implode(',', array_fill(0, count($tags), '?'));
    $whereParts[] =
      "EXISTS (
         SELECT 1
         FROM post_tags pt
         JOIN tags t ON t.id = pt.tag_id
         WHERE pt.post_id = p.id
           AND t.name IN ($placeholders)
       )";
    // aggiungo i tag ai params nell'ordine
    foreach ($tags as $name) {
      $params[] = $name;
    }
  }
}

// 3) ORDER BY: mappo valori consentiti su colonne vere
$sortCol = 'p.`date`';
if ($sortBy === 'cost') {
  $sortCol = 'p.cost_eur';
}
$orderSql = ($order === 'asc') ? 'ASC' : 'DESC';

// 4) PAGINAZIONE: per semplicità inietto i numeri direttamente nella SQL

$sql =
  "SELECT p.id, p.title, p.`date`, p.mood, p.cost_eur
   FROM posts p";

if (!empty($whereParts)) {
  $sql .= ' WHERE ' . implode(' AND ', $whereParts);
}

$sql .= " ORDER BY $sortCol $orderSql LIMIT $limit OFFSET $offset";

// 5-6) Preparo, eseguo e prendo i risultati
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

// 6-bis) Mappo snake_case DB -> camelCase JSON
$items = [];
foreach ($rows as $r) {
  $items[] = [
    'id'      => (int)$r['id'],
    'title'   => $r['title'],
    'date'    => $r['date'],               // YYYY-MM-DD
    'mood'    => $r['mood'],
    'costEUR' => (float)$r['cost_eur'],
  ];
}

// 8) Risposta
json_ok([
  'items' => $items,
  'page'  => $page,
  'limit' => $limit,
]);
