<?php
require __DIR__ . '/inc/bootstrap.php';
if (user()) redirect('app.php');
$err = '';
if (post()) {
  $name = trim((string)($_POST['name'] ?? ''));
  $email = strtolower(trim((string)($_POST['email'] ?? '')));
  $phone = trim((string)($_POST['phone'] ?? ''));
  $pw = (string)($_POST['password'] ?? '');
  if ($name === '' || mb_strlen($name) > 100) $err = 'Please enter your name.';
  elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) $err = 'Please enter a valid email.';
  elseif (!preg_match('/^[0-9+\-\s]{6,20}$/', $phone)) $err = 'Please enter a valid phone number.';
  elseif (strlen($pw) < 8) $err = 'Password must be at least 8 characters.';
  elseif (q('SELECT 1 FROM users WHERE email=?', [$email])->fetch()) $err = 'That email is already registered.';
  else {
    q("INSERT INTO users (name,email,role,password,contact_info) VALUES (?,?,'Parent',?,?)", [$name, $email, password_hash($pw, PASSWORD_DEFAULT), $phone]);
    session_regenerate_id(true);
    $_SESSION['uid'] = (int)$db->lastInsertId();
    flash('Welcome to Bliss. Start by registering your child.');
    redirect('app.php?p=children');
  }
}
top('Register'); ?>
<div class="form"><h2>Register as a parent</h2>
<?php if ($err) echo '<p class="note err">' . h($err) . '</p>'; ?>
<form method="post"><?= field() ?>
<label>Full name<input name="name" required maxlength="100" value="<?= h($_POST['name'] ?? '') ?>"></label>
<label>Email<input name="email" type="email" required maxlength="100" value="<?= h($_POST['email'] ?? '') ?>"></label>
<label>Phone<input name="phone" type="tel" required maxlength="20" value="<?= h($_POST['phone'] ?? '') ?>"></label>
<label>Password (8+ characters)<input name="password" type="password" minlength="8" required autocomplete="new-password"></label>
<button>Create account</button></form></div>
<?php bottom();
