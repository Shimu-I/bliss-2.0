<?php
if (post()) {
  q("UPDATE notification SET status='Read' WHERE user_id=?", [$u['user_id']]);
  redirect('app.php?p=inbox');
}
$rows = q('SELECT * FROM notification WHERE user_id=? ORDER BY notification_id DESC LIMIT 100', [$u['user_id']])->fetchAll();
$hasNew = false; foreach ($rows as $n) if ($n['status'] === 'Unread') $hasNew = true;
?>
<div class="bar"><h2>Inbox</h2><?php if ($hasNew): ?><form method="post" class="inl"><?= field() ?><button class="g">Mark all read</button></form><?php endif ?></div>
<div class="sc"><table><thead><tr><th>Date</th><th>Message</th></tr></thead><tbody>
<?php foreach ($rows as $n): ?><tr><td><?= h(substr($n['created_at'], 0, 10)) ?></td><td><?= $n['status'] === 'Unread' ? '<b>' . h($n['message']) . '</b>' : '<span class="mu">' . h($n['message']) . '</span>' ?></td></tr><?php endforeach ?>
<?php if (!$rows): ?><tr><td colspan="2" class="mu">No notifications.</td></tr><?php endif ?>
</tbody></table></div>
