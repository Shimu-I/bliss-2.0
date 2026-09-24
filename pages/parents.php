<?php $rows = q("SELECT u.name,u.email,u.contact_info,(SELECT COUNT(*) FROM child c WHERE c.user_id=u.user_id) n FROM users u WHERE u.role='Parent' ORDER BY u.name")->fetchAll(); ?>
<h2>Parents</h2>
<div class="sc"><table><thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Children</th></tr></thead><tbody>
<?php foreach ($rows as $p): ?><tr><td><?= h($p['name']) ?></td><td><?= h($p['email']) ?></td><td><?= h($p['contact_info']) ?></td><td><?= (int)$p['n'] ?></td></tr><?php endforeach ?>
<?php if (!$rows): ?><tr><td colspan="4" class="mu">No parents yet.</td></tr><?php endif ?>
</tbody></table></div>
