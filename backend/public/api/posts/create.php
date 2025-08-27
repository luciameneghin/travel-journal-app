<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/cors.php';
require_once __DIR__ . '/../../../lib/response.php';
require_once __DIR__ . '/../../../lib/db.php';

// Accettiamo solo POST
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  json_error('METHOD_NOT_ALLOWED', 405, ['allow' => 'POST']);
}

// === Lettura input JSON ===
$raw = file_get_contents('php://input');
if ($raw === false || $raw === '') json_error('EMPTY_BODY', 400);
$input = json_decode($raw, true);
if (!is_array($input)) json_error('INVALID_JSON', 400);

// === Validazione obbligatori (semplice) ===
$errors = [];
$moods  = ['felice', 'stressato', 'emozionato', 'rilassato', 'altro'];

// title
if (!isset($input['title']) || !is_string($input['title']) || trim($input['title']) === '') {
  $errors['title'][] = 'REQUIRED';
} else if (strlen(trim($input['title'])) > 150) {
  $errors['title'][] = 'MAX_150';
}

// date
if (!isset($input['date']) || !is_string($input['date'])) {
  $errors['date'][] = 'REQUIRED';
} else {
  $date = $input['date'];
  if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $errors['date'][] = 'FORMAT_YYYY_MM_DD';
  } else {
    [$y, $m, $d] = array_map('intval', explode('-', $date));
    if (!checkdate($m, $d, $y)) $errors['date'][] = 'INVALID_DATE';
  }
}

// description
if (!isset($input['description']) || !is_string($input['description']) || trim($input['description']) === '') {
  $errors['description'][] = 'REQUIRED';
}

// mood
if (!isset($input['mood']) || !is_string($input['mood']) || !in_array($input['mood'], $moods, true)) {
  $errors['mood'][] = 'INVALID';
}

// physicalEffort
if (!isset($input['physicalEffort']) || !is_numeric($input['physicalEffort'])) {
  $errors['physicalEffort'][] = 'REQUIRED_INT_1_5';
} else {
  $n = (int)$input['physicalEffort'];
  if ($n < 1 || $n > 5) $errors['physicalEffort'][] = 'RANGE_1_5';
}

// economicEffort
if (!isset($input['economicEffort']) || !is_numeric($input['economicEffort'])) {
  $errors['economicEffort'][] = 'REQUIRED_INT_1_5';
} else {
  $n = (int)$input['economicEffort'];
  if ($n < 1 || $n > 5) $errors['economicEffort'][] = 'RANGE_1_5';
}

// costEUR
if (!isset($input['costEUR']) || !is_numeric($input['costEUR'])) {
  $errors['costEUR'][] = 'REQUIRED_NUMBER';
} else if ((float)$input['costEUR'] < 0) {
  $errors['costEUR'][] = 'MIN_0';
}

// === Opzionali ===
$positives = null;
if (array_key_exists('positives', $input)) {
  if (!is_string($input['positives'])) $errors['positives'][] = 'MUST_BE_STRING';
  else $positives = trim($input['positives']);
}

$negatives = null;
if (array_key_exists('negatives', $input)) {
  if (!is_string($input['negatives'])) $errors['negatives'][] = 'MUST_BE_STRING';
  else $negatives = trim($input['negatives']);
}

$placeName = null;
if (array_key_exists('placeName', $input)) {
  if (!is_string($input['placeName'])) $errors['placeName'][] = 'MUST_BE_STRING';
  else $placeName = trim($input['placeName']);
}

$lat = null;
if (array_key_exists('lat', $input)) {
  if (!is_numeric($input['lat'])) $errors['lat'][] = 'MUST_BE_NUMBER';
  else {
    $latVal = (float)$input['lat'];
    if ($latVal < -90 || $latVal > 90) $errors['lat'][] = 'RANGE_-90_90';
    else $lat = $latVal;
  }
}

$lng = null;
if (array_key_exists('lng', $input)) {
  if (!is_numeric($input['lng'])) $errors['lng'][] = 'MUST_BE_NUMBER';
  else {
    $lngVal = (float)$input['lng'];
    if ($lngVal < -180 || $lngVal > 180) $errors['lng'][] = 'RANGE_-180_180';
    else $lng = $lngVal;
  }
}

// tags
$tags = [];
if (array_key_exists('tags', $input)) {
  if (!is_array($input['tags'])) $errors['tags'][] = 'MUST_BE_ARRAY';
  else {
    foreach ($input['tags'] as $t) {
      if (is_string($t)) {
        $s = trim($t);
        if ($s !== '') $tags[] = $s;
      }
    }
    $tags = array_values(array_unique($tags));
  }
}

// images
$images = [];
if (array_key_exists('images', $input)) {
  if (!is_array($input['images'])) $errors['images'][] = 'MUST_BE_ARRAY';
  else foreach ($input['images'] as $u) {
    if (is_string($u)) {
      $s = trim($u);
      if ($s !== '' && preg_match('/^https?:\/\//i', $s)) $images[] = $s;
    }
  }
}

// videos
$videos = [];
if (array_key_exists('videos', $input)) {
  if (!is_array($input['videos'])) $errors['videos'][] = 'MUST_BE_ARRAY';
  else foreach ($input['videos'] as $u) {
    if (is_string($u)) {
      $s = trim($u);
      if ($s !== '' && preg_match('/^https?:\/\//i', $s)) $videos[] = $s;
    }
  }
}

// Se ci sono errori → 400
if (!empty($errors)) {
  json_error('VALIDATION_FAILED', 400, ['details' => $errors]);
}

// === Normalizzazione ===
$title          = trim($input['title']);
$date           = $input['date']; // YYYY-MM-DD
$description    = trim($input['description']);
$mood           = $input['mood'];
$physicalEffort = (int)$input['physicalEffort'];
$economicEffort = (int)$input['economicEffort'];
$costEUR        = (float)$input['costEUR'];

// === Helpers “locali” per tag/media ===
function find_tag_id_by_name_or_null(PDO $pdo, string $name): ?int
{
  $stmt = $pdo->prepare("SELECT id FROM tags WHERE name = ?");
  $stmt->execute([$name]);
  $row = $stmt->fetch();
  return $row ? (int)$row['id'] : null;
}
function insert_tag_and_return_id(PDO $pdo, string $name): int
{
  $stmt = $pdo->prepare("INSERT INTO tags (name) VALUES (?)");
  $stmt->execute([$name]);
  return (int)$pdo->lastInsertId();
}
function link_post_tag(PDO $pdo, int $postId, int $tagId): void
{
  // Evita errore se già presente
  $stmt = $pdo->prepare("INSERT IGNORE INTO post_tags (post_id, tag_id) VALUES (?, ?)");
  $stmt->execute([$postId, $tagId]);
}
function insert_media(PDO $pdo, int $postId, string $type, string $source, string $url): void
{
  $stmt = $pdo->prepare("INSERT INTO media (post_id, type, source, url, created_at) VALUES (?, ?, ?, ?, NOW())");
  $stmt->execute([$postId, $type, $source, $url]);
}
function safe_message(Throwable $e): string
{
  return $e->getMessage(); // in prod potresti renderlo generico
}

// === Transazione + INSERT ===
$pdo = pdo();
$pdo->beginTransaction();

try {
  // 4) Inserire in posts (placeholders ? per evitare SQL injection)
  $sql = "INSERT INTO posts (
            title, `date`, description, mood, positives, negatives,
            physical_effort, economic_effort, cost_eur, place_name, lat, lng,
            created_at, updated_at
          ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    $title,
    $date,
    $description,
    $mood,
    $positives,
    $negatives,
    $physicalEffort,
    $economicEffort,
    $costEUR,
    $placeName,
    $lat,
    $lng
  ]);

  $postId = (int)$pdo->lastInsertId();

  // 5) Gestire i tag
  foreach ($tags as $name) {
    $tagId = find_tag_id_by_name_or_null($pdo, $name);
    if ($tagId === null) {
      $tagId = insert_tag_and_return_id($pdo, $name);
    }
    link_post_tag($pdo, $postId, $tagId);
  }

  // 6) Gestire i media (URL)
  foreach ($images as $url) {
    insert_media($pdo, $postId, "image", "url", $url);
  }
  foreach ($videos as $url) {
    insert_media($pdo, $postId, "video", "url", $url);
  }

  // 7) Commit e risposta
  $pdo->commit();
  json_ok(['id' => $postId]);
} catch (Throwable $e) {
  $pdo->rollBack();
  json_error('DB_ERROR', 500, ['detail' => safe_message($e)]);
}
