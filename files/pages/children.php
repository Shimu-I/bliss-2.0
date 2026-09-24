<?php
$err = '';
$isAdmin = $u['role'] === 'Admin';
if (post()) {
  $act = $_POST['act'] ?? '';
  if ($act === 'del') {
    $id = (int)($_POST['child'] ?? 0);
    if ($isAdmin) q('DELETE FROM child WHERE child_id=?', [$id]);
    else q('DELETE FROM child WHERE child_id=? AND user_id=?', [$id, $u['user_id']]);
    flash('Child removed.'); redirect('app.php?p=children');
  }
  if ($act === 'assign' && $isAdmin) {
    $cg = (int)($_POST['cg'] ?? 0);
    if ($cg && !q("SELECT 1 FROM users WHERE user_id=? AND role='Caregiver'", [$cg])->fetch()) { http_response_code(400); exit('Invalid caregiver'); }
    q('UPDATE child SET caregiver_id=? WHERE child_id=?', [$cg ?: null, (int)($_POST['child'] ?? 0)]);
    flash('Caregiver updated.'); redirect('app.php?p=children');
  }
  if ($act === 'add' && !$isAdmin) {
    $name = trim((string)($_POST['name'] ?? ''));
    $dob = (string)($_POST['dob'] ?? '');
    $g = ($_POST['gender'] ?? '') === 'male' ? 'male' : 'female';
    $sp = ($_POST['special'] ?? '') === 'Yes' ? 'Yes' : 'No';
    $med = trim((string)($_POST['med'] ?? ''));
    if ($name === '' || mb_strlen($name) > 100) $err = 'Please enter the child\'s name.';
    elseif (!vdate($dob) || $dob > date('Y-m-d')) $err = 'Please enter a valid date of birth.';
    elseif (mb_strlen($med) > 1000) $err = 'Medical history is too long.';
    else {
      $cg = q("SELECT u.user_id FROM users u LEFT JOIN child c ON c.caregiver_id=u.user_id WHERE u.role='Caregiver' GROUP BY u.user_id ORDER BY COUNT(c.child_id), u.user_id LIMIT 1")->fetchColumn();
      q('INSERT INTO child (name,date_of_birth,gender,medical_history,special_needs,user_id,caregiver_id) VALUES (?,?,?,?,?,?,?)', [$name, $dob, $g, $med ?: 'None', $sp, $u['user_id'], $cg ?: null]);
      flash('Child registered' . ($cg ? ' and a caregiver was assigned.' : '. A caregiver will be assigned soon.')); redirect('app.php?p=children');
    }
  }
}
$cgs = $isAdmin ? q("SELECT user_id,name FROM users WHERE role='Caregiver' ORDER BY name")->fetchAll() : [];
?>
<h2>Children</h2>
<?php if ($err) echo '<p class="note err">' . h($err) . '</p>'; ?>
<?php if (!$isAdmin): ?>
<form method="post" class="fg"><?= field() ?><input type="hidden" name="act" value="add">
<label>Full name<input name="name" required maxlength="100"></label>
<label>Date of birth<input name="dob" type="date" required max="<?= date('Y-m-d') ?>"></label>
<label>Gender<select name="gender"><option value="female">Female</option><option value="male">Male</option></select></label>
<label>Special needs<select name="special"><option>No</option><option>Yes</option></select></label>
<label>Medical history<input name="med" maxlength="1000"></label>
<label>&nbsp;<button>Register child</button></label></form>
<?php endif ?>
<div class="sc"><table><thead><tr><th>Name</th><th>Age</th><?= $isAdmin ? '<th>Parent</th>' : '<th>Medical</th>' ?><th>Caregiver</th><th></th></tr></thead><tbody>
<?php foreach (kids() as $c): ?>
<tr><td><?= h($c['name']) ?><?= $c['special_needs'] === 'Yes' ? '<span class="tag">Special</span>' : '' ?></td><td><?= age($c['date_of_birth']) ?></td>
<td><?= h($isAdmin ? $c['parent'] : $c['medical_history']) ?></td>
<td><?php if ($isAdmin): ?><form method="post" class="inl"><?= field() ?><input type="hidden" name="act" value="assign"><input type="hidden" name="child" value="<?= (int)$c['child_id'] ?>">
<select name="cg" class="selinl"><option value="">Unassigned</option><?php foreach ($cgs as $g): ?><option value="<?= (int)$g['user_id'] ?>"<?= (int)$g['user_id'] === (int)$c['caregiver_id'] ? ' selected' : '' ?>><?= h($g['name']) ?></option><?php endforeach ?></select> <button class="g">Save</button></form>
<?php else: echo h($c['cg'] ?: 'Unassigned'); endif ?></td>
<td><form method="post" class="inl" data-confirm="Remove this child and all their records?"><?= field() ?><input type="hidden" name="act" value="del"><input type="hidden" name="child" value="<?= (int)$c['child_id'] ?>"><button class="x">Remove</button></form></td></tr>
<?php endforeach ?>
<?php if (!kids()): ?><tr><td colspan="5" class="mu">No children yet.</td></tr><?php endif ?>
</tbody></table></div>
