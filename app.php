<?php
require __DIR__ . '/inc/bootstrap.php';
$u = need();
$allowed = tabs()[$u['role']];
$p = (string)($_GET['p'] ?? 'home');
if (!isset($allowed[$p])) $p = 'home';
top($allowed[$p], $p);
include __DIR__ . '/pages/' . $p . '.php';
bottom();
