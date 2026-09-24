<?php
$err = '';
if (post()) {
  $act = $_POST['act'] ?? '';
  if ($act === 'del') {
    q("DELETE FROM users WHERE user_id=? AND role='Caregiver'", [(int)($_POST['id'] ?? 0)]);
    flash('Caregiver removed. Their children are now unassigned.'); redirect('app.php?p=staff');
  }
  if ($act === 'add') {
    $name = trim((string)($_POST['name'] ?? ''));
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $pw = (string)($_POST['pw'] ?? '');
    $qual = trim((string)($_POST['qual'] ?? ''));
    $exp = (int)($_POST['exp'] ?? 0);
    $train = trim((string)($_POST['train'] ?? ''));
    if ($name === '' || mb_strlen($name) > 100 || $qual === '' || mb_strlen($qual) > 255 || mb_strlen($train) > 255) $err = 'Please complete the fields (name and qualification are required).';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) $err = 'Enter a valid email.';
    elseif (strlen($pw) < 8) $err = 'Password must be at least 8 characters.';
    elseif ($exp < 0 || $exp > 60) $err = 'Enter valid years of experience.';
    elseif (q('SELECT 1 FROM users WHERE email=?', [$email])->fetch()) $err = 'That email is already registered.';
    else {
      $db->beginTransaction();
      q("INSERT INTO users (name,email,role,password) VALUES (?,?,'Caregiver',?)", [$name, $email, password_hash($pw, PASSWORD_DEFAULT)]);
      q('INSERT INTO caregivers (user_id,qualification,experience,special_training) VALUES (?,?,?,?)', [$db->lastInsertId(), $qual, $exp, $train ?: null]);
      $db->commit();
      flash('Caregiver added.'); redirect('app.php?p=staff');
    }
  }
}
$rows = q('SELECT u.user_id,u.name,u.email,c.qualification,c.experience,c.special_training FROM users u LEFT JOIN caregivers c ON c.user_id=u.user_id WHERE u.role=\'Caregiver\' ORDER BY u.name')->fetchAll();
?>
<h2>Caregivers</h2>
<?php if ($err) echo '<p class="note err">' . h($err) . '</p>'; ?>
<form method="post" class="fg"><?= field() ?><input type="hidden" name="act" value="add">
<label>Name<input name="name" required maxlength="100"></label>
<label>Email<input name="email" type="email" required maxlength="100"></label>
<label>Temporary password<input name="pw" type="password" minlength="8" required autocomplete="new-password"></label>
<label>Qualification<input name="qual" required maxlength="255"></label>
<label>Years of experience<input name="exp" type="number" min="0" max="60" required></label>
<label>Special training<input name="train" maxlength="255"></label>
<label>&nbsp;<button>Add caregiver</button></label></form>
<div class="sc"><table><thead><tr><th>Name</th><th>Email</th><th>Qualification</th><th>Experience</th><th>Training</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $g): ?>
<tr><td><?= h($g['name']) ?></td><td><?= h($g['email']) ?></td><td><?= h($g['qualification']) ?></td><td><?= (int)$g['experience'] ?> yrs</td><td><?= h($g['special_training'] ?: '—') ?></td>
<td><form method="post" class="inl" data-confirm="Remove this caregiver? Their children become unassigned."><?= field() ?><input type="hidden" name="act" value="del"><input type="hidden" name="id" value="<?= (int)$g['user_id'] ?>"><button class="x">Remove</button></form></td></tr>
<?php endforeach ?>
<?php if (!$rows): ?><tr><td colspan="6" class="mu">No caregivers yet.</td></tr><?php endif ?>
</tbody></table></div>
