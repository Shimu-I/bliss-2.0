<?php
ob_start();
ini_set('display_errors', '0');
$cfg = require __DIR__ . '/../config.php';
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'; form-action 'self'; frame-ancestors 'none'");
session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS'])]);
session_start();
try {
  $db = new PDO("mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['name']};charset=utf8mb4", $cfg['user'], $cfg['pass'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]);
} catch (PDOException $e) {
  error_log($e->getMessage());
  http_response_code(500);
  exit('Database connection failed. Check config.php.');
}

date_default_timezone_set($cfg['tz']);
$db->exec("SET time_zone = '" . (new DateTime('now', new DateTimeZone($cfg['tz'])))->format('P') . "'");

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function q($sql, $p = []) { global $db; $s = $db->prepare($sql); $s->execute($p); return $s; }
function redirect($to) { header('Location: ' . $to); exit; }
function money($v) { global $cfg; return $cfg['currency'] . number_format((float)$v, 2); }
function age($d) { return (new DateTime($d))->diff(new DateTime())->y; }
function vdate($s) { $d = DateTime::createFromFormat('Y-m-d', (string)$s); return $d && $d->format('Y-m-d') === $s; }
function csrf() { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16)); return $_SESSION['csrf']; }
function field() { return '<input type="hidden" name="csrf" value="' . csrf() . '">'; }
function post() {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') return false;
  if (!hash_equals(csrf(), (string)($_POST['csrf'] ?? ''))) { http_response_code(400); exit('Invalid request. Go back and reload the page.'); }
  return true;
}
function flash($m = null) {
  if ($m !== null) { $_SESSION['flash'] = $m; return ''; }
  $f = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']); return $f;
}
function user() {
  static $u = false;
  if ($u === false) $u = isset($_SESSION['uid']) ? (q('SELECT * FROM users WHERE user_id=?', [$_SESSION['uid']])->fetch() ?: null) : null;
  return $u;
}
function need() {
  $u = user();
  if (!$u) redirect('login.php');
  $roles = func_get_args();
  if ($roles && !in_array($u['role'], $roles, true)) { http_response_code(403); exit('Forbidden'); }
  return $u;
}
function notify($uid, $cid, $msg, $type = 'Update') {
  q('INSERT INTO notification (user_id,child_id,message,notification_type) VALUES (?,?,?,?)', [$uid, $cid, mb_substr($msg, 0, 500), $type]);
}
function kids() {
  $u = user();
  $sql = 'SELECT c.*, g.name cg, p.name parent, (SELECT status FROM attendance a WHERE a.child_id=c.child_id AND a.`date`=CURDATE()) today
          FROM child c JOIN users p ON p.user_id=c.user_id LEFT JOIN users g ON g.user_id=c.caregiver_id';
  if ($u['role'] === 'Parent') return q($sql . ' WHERE c.user_id=? ORDER BY c.name', [$u['user_id']])->fetchAll();
  if ($u['role'] === 'Caregiver') return q($sql . ' WHERE c.caregiver_id=? ORDER BY c.name', [$u['user_id']])->fetchAll();
  return q($sql . ' ORDER BY c.name')->fetchAll();
}
function tabs() {
  return [
    'Parent'    => ['home' => 'Overview', 'children' => 'Children', 'records' => 'Activity', 'bills' => 'Bills', 'inbox' => 'Inbox'],
    'Caregiver' => ['home' => 'My children', 'records' => 'Records'],
    'Admin'     => ['home' => 'Overview', 'children' => 'Children', 'staff' => 'Caregivers', 'parents' => 'Parents', 'bills' => 'Payments'],
  ];
}
function top($title, $cur = '') {
  $u = user();
  echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"><title>' . h($title) . ' · Bliss Day Care</title><link rel="stylesheet" href="assets/style.css"></head><body>';
  echo '<header><a class="brand" href="index.php">Bliss Day Care</a><nav>';
  if ($u) {
    $unread = (int)q("SELECT COUNT(*) FROM notification WHERE user_id=? AND status='Unread'", [$u['user_id']])->fetchColumn();
    foreach (tabs()[$u['role']] as $k => $l)
      echo '<a href="app.php?p=' . $k . '"' . ($k === $cur ? ' class="on"' : '') . '>' . h($l) . ($k === 'inbox' && $unread ? ' <i class="dot">' . $unread . '</i>' : '') . '</a>';
    echo '<form method="post" action="logout.php" class="inl">' . field() . '<button class="lnk">Sign out</button></form>';
  } else echo '<a href="login.php">Sign in</a><a href="register.php">Register</a>';
  echo '</nav></header><main>';
  if ($m = flash()) echo '<p class="note">' . h($m) . '</p>';
}
function bottom() { echo '</main><footer>Bliss Day Care</footer><script src="assets/app.js"></script></body></html>'; }
