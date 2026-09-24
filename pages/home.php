<?php
if ($u['role'] === 'Admin') {
  $stats = [
    ['Children', 'SELECT COUNT(*) FROM child'],
    ['Caregivers', "SELECT COUNT(*) FROM users WHERE role='Caregiver'"],
    ['Parents', "SELECT COUNT(*) FROM users WHERE role='Parent'"],
    ['Present today', "SELECT COUNT(*) FROM attendance WHERE `date`=CURDATE() AND status='Present'"],
    ['Outstanding', "SELECT COALESCE(SUM(total_amount),0) FROM bill_payment WHERE payment_status='Pending'"],
  ];
  echo '<h2>Overview</h2><div class="grid">';
  foreach ($stats as $s) { $v = q($s[1])->fetchColumn(); if ($s[0] === 'Outstanding') $v = money($v); echo '<div class="card mu">' . h($s[0]) . '<div class="big">' . h($v) . '</div></div>'; }
  echo '</div>';
  return;
}
$unread = (int)q("SELECT COUNT(*) FROM notification WHERE user_id=? AND status='Unread'", [$u['user_id']])->fetchColumn();
?>
<h2>Hello, <?= h(explode(' ', $u['name'])[0]) ?></h2>
<?php if ($u['role'] === 'Parent' && $unread): ?><p><a href="app.php?p=inbox">You have <?= $unread ?> unread notification<?= $unread > 1 ? 's' : '' ?> &rarr;</a></p><?php endif ?>
<div class="grid">
<?php foreach (kids() as $c): ?>
<div class="card"><b><?= h($c['name']) ?></b><?= $c['special_needs'] === 'Yes' ? '<span class="tag">Special care</span>' : '' ?>
<p class="mu"><?= age($c['date_of_birth']) ?> yrs &middot; <?= h($c['gender']) ?><br>Caregiver: <?= h($c['cg'] ?: 'Unassigned') ?></p>
<p>Today: <?= $c['today'] ? '<span class="st ' . h($c['today']) . '">' . h($c['today']) . '</span>' : '<span class="mu">Not marked</span>' ?></p>
<?php if ($u['role'] === 'Caregiver'): ?>
<form method="post" action="app.php?p=records" class="inl"><?= field() ?><input type="hidden" name="act" value="att"><input type="hidden" name="child" value="<?= (int)$c['child_id'] ?>">
<div class="row"><button name="status" value="Present">Present</button><button name="status" value="Absent" class="g">Absent</button></div></form>
<?php endif ?></div>
<?php endforeach ?>
<?php if (!kids()): ?><p class="mu">No children yet.</p><?php endif ?>
</div>
