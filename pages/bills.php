<?php
$isAdmin = $u['role'] === 'Admin';
$err = '';
if (post() && $isAdmin) {
  $act = $_POST['act'] ?? '';
  if ($act === 'paid') {
    $b = q('SELECT user_id,section_type FROM bill_payment WHERE bill_id=?', [(int)($_POST['bill'] ?? 0)])->fetch();
    if ($b) {
      q("UPDATE bill_payment SET payment_status='Paid', paid_at=NOW() WHERE bill_id=?", [(int)$_POST['bill']]);
      notify($b['user_id'], null, 'Payment received for ' . $b['section_type'] . '. Thank you!', 'Payment');
    }
    flash('Marked as paid.'); redirect('app.php?p=bills');
  }
  if ($act === 'add') {
    $pid = (int)($_POST['parent'] ?? 0);
    $type = $_POST['type'] ?? '';
    $desc = trim((string)($_POST['desc'] ?? ''));
    $amt = (string)($_POST['amount'] ?? '');
    $due = (string)($_POST['due'] ?? '');
    if (!q("SELECT 1 FROM users WHERE user_id=? AND role='Parent'", [$pid])->fetch()) $err = 'Choose a parent.';
    elseif (!in_array($type, ['Tuition', 'Meal', 'Special care'], true)) $err = 'Choose a bill type.';
    elseif (!is_numeric($amt) || $amt <= 0 || $amt > 1000000) $err = 'Enter a valid amount.';
    elseif (!vdate($due)) $err = 'Enter a valid due date.';
    elseif (mb_strlen($desc) > 255) $err = 'Description is too long.';
    else {
      q('INSERT INTO bill_payment (user_id,section_type,section_description,total_amount,due_date) VALUES (?,?,?,?,?)', [$pid, $type, $desc, round((float)$amt, 2), $due]);
      notify($pid, null, 'New ' . strtolower($type) . ' bill: ' . money($amt) . ', due ' . $due . '.', 'Bill');
      flash('Bill created.'); redirect('app.php?p=bills');
    }
  }
}
$sql = "SELECT b.*, u.name parent, IF(b.payment_status='Pending' AND b.due_date<CURDATE(),'Overdue',b.payment_status) status FROM bill_payment b JOIN users u ON u.user_id=b.user_id";
$rows = $isAdmin ? q($sql . ' ORDER BY b.due_date DESC')->fetchAll() : q($sql . ' WHERE b.user_id=? ORDER BY b.due_date DESC', [$u['user_id']])->fetchAll();
?>
<h2><?= $isAdmin ? 'Payments' : 'Bills' ?></h2>
<?php if ($err) echo '<p class="note err">' . h($err) . '</p>'; ?>
<?php if ($isAdmin): ?>
<form method="post" class="fg"><?= field() ?><input type="hidden" name="act" value="add">
<label>Parent<select name="parent"><?php foreach (q("SELECT user_id,name FROM users WHERE role='Parent' ORDER BY name")->fetchAll() as $p) echo '<option value="' . (int)$p['user_id'] . '">' . h($p['name']) . '</option>'; ?></select></label>
<label>Type<select name="type"><option>Tuition</option><option>Meal</option><option>Special care</option></select></label>
<label>Amount<input name="amount" type="number" min="0.01" step="0.01" required></label>
<label>Due date<input name="due" type="date" required></label>
<label>Description<input name="desc" maxlength="255"></label>
<label>&nbsp;<button>Create bill</button></label></form>
<?php endif ?>
<div class="sc"><table><thead><tr><?= $isAdmin ? '<th>Parent</th>' : '' ?><th>Type</th><th>Description</th><th>Amount</th><th>Due</th><th>Status</th><?= $isAdmin ? '<th></th>' : '' ?></tr></thead><tbody>
<?php foreach ($rows as $b): ?>
<tr><?= $isAdmin ? '<td>' . h($b['parent']) . '</td>' : '' ?><td><?= h($b['section_type']) ?></td><td><?= h($b['section_description']) ?></td><td><?= h(money($b['total_amount'])) ?></td><td><?= h($b['due_date']) ?></td>
<td><span class="st <?= h($b['status']) ?>"><?= h($b['status']) ?></span></td>
<?php if ($isAdmin): ?><td><?php if ($b['payment_status'] === 'Pending'): ?><form method="post" class="inl" data-confirm="Mark this bill as paid?"><?= field() ?><input type="hidden" name="act" value="paid"><input type="hidden" name="bill" value="<?= (int)$b['bill_id'] ?>"><button class="g">Mark paid</button></form><?php endif ?></td><?php endif ?></tr>
<?php endforeach ?>
<?php if (!$rows): ?><tr><td colspan="7" class="mu">No bills yet.</td></tr><?php endif ?>
</tbody></table></div>
<?php if (!$isAdmin): ?><p class="mu gap">Payments are recorded by the centre once received. Contact the front desk for payment options.</p><?php endif ?>
