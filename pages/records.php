<?php
$kids = kids();
$ids = array_map('intval', array_column($kids, 'child_id'));
$err = '';
if (post() && $u['role'] === 'Caregiver') {
  $cid = (int)($_POST['child'] ?? 0);
  if (!in_array($cid, $ids, true)) { http_response_code(403); exit('Forbidden'); }
  $c = q('SELECT name,user_id FROM child WHERE child_id=?', [$cid])->fetch();
  $act = $_POST['act'] ?? '';
  if ($act === 'att') {
    $st = ($_POST['status'] ?? '') === 'Absent' ? 'Absent' : 'Present';
    q('INSERT INTO attendance (`date`,status,child_id) VALUES (CURDATE(),?,?) ON DUPLICATE KEY UPDATE status=VALUES(status)', [$st, $cid]);
    notify($c['user_id'], $cid, $c['name'] . ' was marked ' . strtolower($st) . ' today.', 'Attendance');
    flash($c['name'] . ' marked ' . strtolower($st) . '.');
    redirect('app.php');
  }
  $t = $_POST['type'] ?? '';
  $title = trim((string)($_POST['title'] ?? ''));
  $note = trim((string)($_POST['note'] ?? ''));
  if (!in_array($t, ['activity', 'learning', 'meal', 'pickup', 'dropoff'], true)) $err = 'Choose a record type.';
  elseif (in_array($t, ['activity', 'learning', 'meal'], true) && ($title === '' || mb_strlen($title) > 100)) $err = 'Please add a title (100 characters max).';
  elseif (mb_strlen($note) > 1000) $err = 'Details are too long.';
  else {
    if ($t === 'activity') q('INSERT INTO activity (activity_type,description,`date`,child_id) VALUES (?,?,CURDATE(),?)', [$title, $note, $cid]);
    elseif ($t === 'learning') q('INSERT INTO learning (learning_type,progress_notes,`date`,child_id) VALUES (?,?,CURDATE(),?)', [$title, $note, $cid]);
    elseif ($t === 'meal') q('INSERT INTO meal (meal_name,allergies,`date`,child_id) VALUES (?,?,CURDATE(),?)', [$title, $note, $cid]);
    else q('INSERT INTO pickdrop (status,`time`,`date`,child_id) VALUES (?,CURTIME(),CURDATE(),?)', [$t === 'pickup' ? 'Picked' : 'Dropped', $cid]);
    $label = ['activity' => 'Activity', 'learning' => 'Learning', 'meal' => 'Meal', 'pickup' => 'Picked up', 'dropoff' => 'Dropped off'][$t];
    notify($c['user_id'], $cid, $c['name'] . ': ' . $label . ($title !== '' ? ' - ' . $title : ''), $label);
    flash('Record added.');
    redirect('app.php?p=records');
  }
}
?>
<h2><?= $u['role'] === 'Parent' ? 'Activity' : 'Records' ?></h2>
<?php if ($err) echo '<p class="note err">' . h($err) . '</p>'; ?>
<?php if ($u['role'] === 'Caregiver' && $kids): ?>
<form method="post" class="fg"><?= field() ?><input type="hidden" name="act" value="log">
<label>Child<select name="child"><?php foreach ($kids as $c) echo '<option value="' . (int)$c['child_id'] . '">' . h($c['name']) . '</option>'; ?></select></label>
<label>Type<select name="type"><option value="activity">Activity</option><option value="learning">Learning note</option><option value="meal">Meal</option><option value="pickup">Pick-up</option><option value="dropoff">Drop-off</option></select></label>
<label>Title<input name="title" maxlength="100"></label>
<label>Details<input name="note" maxlength="1000"></label>
<label>&nbsp;<button>Add record</button></label></form>
<?php endif ?>
<?php
$rows = [];
if ($ids) {
  $in = implode(',', array_fill(0, count($ids), '?'));
  $rows = q("SELECT r.d, r.t, r.title, r.note, c.name FROM (
      SELECT `date` d,'Attendance' t,status title,'' note,child_id FROM attendance
      UNION ALL SELECT `date`,'Activity',activity_type,description,child_id FROM activity
      UNION ALL SELECT `date`,'Learning',learning_type,progress_notes,child_id FROM learning
      UNION ALL SELECT `date`,'Meal',meal_name,allergies,child_id FROM meal
      UNION ALL SELECT `date`,'Transport',status,'',child_id FROM pickdrop) r
    JOIN child c ON c.child_id=r.child_id WHERE r.child_id IN ($in) ORDER BY r.d DESC, r.child_id LIMIT 200", $ids)->fetchAll();
}
?>
<div class="sc"><table><thead><tr><th>Date</th><th>Child</th><th>Type</th><th>Details</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?>
<tr><td><?= h($r['d']) ?></td><td><?= h($r['name']) ?></td><td><?= h($r['t']) ?></td>
<td><?= $r['t'] === 'Attendance' ? '<span class="st ' . h($r['title']) . '">' . h($r['title']) . '</span>' : '<b>' . h($r['title']) . '</b>' . ($r['note'] ? '<br><span class="mu">' . h($r['note']) . '</span>' : '') ?></td></tr>
<?php endforeach ?>
<?php if (!$rows): ?><tr><td colspan="4" class="mu">Nothing here yet.</td></tr><?php endif ?>
</tbody></table></div>
