<?php require __DIR__ . '/inc/bootstrap.php'; top('Welcome'); ?>
<section class="hero"><h1>Gentle care,<br>clearly managed.</h1>
<p class="mu lead">Bliss brings parents, caregivers and staff together in one calm place: attendance, activities, meals, learning and billing.</p>
<div class="row"><?php if (user()): ?><a class="btn" href="app.php">Open dashboard</a><?php else: ?><a class="btn" href="register.php">Register as a parent</a><a class="btn g" href="login.php">Sign in</a><?php endif ?></div></section>
<div class="grid">
<?php foreach ([['🧸 Daily care','Trained caregivers matched to every child.'],['📚 Learning','Progress notes shared after each session.'],['🍎 Meals','Menus with allergy tracking.'],['💛 Special care','Tailored support for medical and special needs.']] as $x): ?>
<div class="card"><b><?= h($x[0]) ?></b><p class="mu"><?= h($x[1]) ?></p></div>
<?php endforeach ?></div>
<?php bottom();
