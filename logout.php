<?php
require __DIR__ . '/inc/bootstrap.php';
if (post()) { $_SESSION = []; session_destroy(); }
redirect('index.php');
