<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/cors.php';
require_once __DIR__ . '/../../../lib/response.php';
require_once __DIR__ . '/../../../lib/db.php';

// Accettiamo solo GET
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
  json_error('METHOD_NOT_ALLOWED', 405, ['allow' => 'GET']);
}

//Leggo e valida id da query string
$idParam = $_GET['id'] ?? null;
if ($idParam === null || !is_numeric($idParam)) {
  json_error('BAD_ID', 400);
}
$id = (int)$idParam;
if ($id <= 0) {
  json_error('BAD_ID', 400);
}


// Connessione
$pdo = pdo();

// query post singolo
$sqlPost = "
   SELECT id, title, `date`, description, mood,
         positives, negatives,
         physical_effort, economic_effort, cost_eur,
         place_name, lat, lng,
         created_at, updated_at
  FROM posts
  WHERE id = ?
";
$stmt = $pdo->prepare($sqlPost);
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row) {
  json_error('NOT_FOUND', 404);
}

// Query tags del post
$sqlTags = "
  SELECT t.name
  FROM post_tags pt
  JOIN tags t ON t.id = pt.tag_id
  WHERE pt.post_id = ?
  ORDER BY t.name
";
$stmt = $pdo->prepare($sqlTags);
$stmt->execute([$id]);
$tags = [];
while ($r = $stmt->fetch()) {
  $tags[] = $r['name'];
}

// Query media del post
$sqlMedia = "
  SELECT type, source, url
  FROM media
  WHERE post_id = ?
  ORDER BY id
";
$stmt = $pdo->prepare($sqlMedia);
$stmt->execute([$id]);
$mediaRows = $stmt->fetchAll();
$media = [];
foreach ($mediaRows as $m) {
  $media[] = [
    'type'   => $m['type'],
    'source' => $m['source'],
    'url'    => $m['url'],
  ];
}

$media = [];
$base  = rtrim(BASE_URL, '/');
$path  = '/src/images/';

foreach ($mediaRows as $m) {
  $filename = trim($m['url'] ?? '');
  $url = (stripos($filename, 'http') === 0)
    ? $filename
    : $base . $path . rawurlencode($filename); // http://localhost:8000/src/images/example.jpg

  $media[] = [
    'type'   => $m['type'],
    'source' => $m['source'],
    'url'    => $url,                      // <-- sempre assoluto
  ];
}



// Mappa DB
$post = [
  'id'              => (int)$row['id'],
  'title'           => $row['title'],
  'date'            => $row['date'],          // YYYY-MM-DD
  'description'     => $row['description'],
  'mood'            => $row['mood'],
  'positives'       => $row['positives'],
  'negatives'       => $row['negatives'],
  'physicalEffort'  => (int)$row['physical_effort'],
  'economicEffort'  => (int)$row['economic_effort'],
  'costEUR'         => (float)$row['cost_eur'],
  'placeName'       => $row['place_name'],
  'lat'             => $row['lat'] !== null ? (float)$row['lat'] : null,
  'lng'             => $row['lng'] !== null ? (float)$row['lng'] : null,
  'createdAt'       => $row['created_at'],
  'updatedAt'       => $row['updated_at'],
];

//Risposta
json_ok(['post' => $post, 'tags' => $tags, 'media' => $media]);
