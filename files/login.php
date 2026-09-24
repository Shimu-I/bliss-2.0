<?php
require __DIR__ . '/inc/bootstrap.php';
if (user()) redirect('app.php');
$err = '';
if (post()) {
  if (($_SESSION['lock'] ?? 0) > time()) {
    $err = 'Too many attempts. Please wait a minute and try again.';
  } else {
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $pw = (string)($_POST['password'] ?? '');
    $u = q('SELECT * FROM users WHERE email=?', [$email])->fetch();
    if (!$u) password_hash($pw, PASSWORD_DEFAULT); // keeps timing similar for unknown emails
    if ($u && password_verify($pw, $u['password'])) {
      session_regenerate_id(true);
      $_SESSION['uid'] = (int)$u['user_id'];
      unset($_SESSION['fails'], $_SESSION['lock']);
      redirect('app.php');
    }
    $_SESSION['fails'] = ($_SESSION['fails'] ?? 0) + 1;
    if ($_SESSION['fails'] >= 5) { $_SESSION['lock'] = time() + 60; $_SESSION['fails'] = 0; }
    $err = 'Incorrect email or password.';
  }
}
top('Sign in'); ?>
<div class="form"><h2>Sign in</h2>
<?php if ($err) echo '<p class="note err">' . h($err) . '</p>'; ?>
<form method="post"><?= field() ?>
<label>Email<input name="email" type="email" required autocomplete="username"></label>
<label>Password<input name="password" type="password" required autocomplete="current-password"></label>
<button>Sign in</button></form>
<p class="mu gap">New parent? <a href="register.php">Register</a></p></div>
<?php bottom();
