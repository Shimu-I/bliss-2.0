<?php
require __DIR__ . '/inc/bootstrap.php';
$done = false;
try { $done = (int)q('SELECT COUNT(*) FROM users')->fetchColumn() > 0; } catch (PDOException $e) { $done = false; }
if ($done) { http_response_code(403); exit('Already installed. Delete install.php from the server.'); }
$err = '';
if (post()) {
  $name = trim((string)($_POST['name'] ?? ''));
  $email = strtolower(trim((string)($_POST['email'] ?? '')));
  $pw = (string)($_POST['password'] ?? '');
  if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $err = 'Enter the admin name and a valid email.';
  elseif (strlen($pw) < 10) $err = 'Use an admin password of 10+ characters.';
  else {
    foreach (array_filter(array_map('trim', explode(';', file_get_contents(__DIR__ . '/schema.sql')))) as $stmt) $db->exec($stmt);
    q("INSERT INTO users (name,email,role,password) VALUES (?,?,'Admin',?)", [$name, $email, password_hash($pw, PASSWORD_DEFAULT)]);
    if (!empty($_POST['demo'])) {
      $dpw = password_hash('password123', PASSWORD_DEFAULT);
      $add = function ($n, $e, $r) use ($dpw, $db) { q('INSERT INTO users (name,email,role,password,contact_info) VALUES (?,?,?,?,?)', [$n, $e, $r, $dpw, '01700000000']); return (int)$db->lastInsertId(); };
      $john = $add('John Doe', 'john.doe@example.com', 'Parent');
      $jane = $add('Jane Smith', 'jane.smith@example.com', 'Parent');
      $emma = $add('Emma Green', 'emma.green@example.com', 'Caregiver');
      $oliv = $add('Olivia Brown', 'olivia.brown@example.com', 'Caregiver');
      q('INSERT INTO caregivers (user_id,qualification,experience,special_training) VALUES (?,?,?,?),(?,?,?,?)', [$emma, 'Nursing', 5, 'Pediatric Care', $oliv, 'Psychology', 3, 'Child Therapy']);
      $kid = function ($n, $dob, $g, $m, $sp, $p, $cg) { q('INSERT INTO child (name,date_of_birth,gender,medical_history,special_needs,user_id,caregiver_id) VALUES (?,?,?,?,?,?,?)', [$n, $dob, $g, $m, $sp, $p, $cg]); global $db; return (int)$db->lastInsertId(); };
      $a = $kid('Alice Doe', '2021-01-01', 'female', 'None', 'No', $john, $emma);
      $c = $kid('Charlie Doe', '2022-02-15', 'male', 'Asthma', 'Yes', $john, $emma);
      $s = $kid('Sophia Smith', '2021-04-20', 'female', 'None', 'No', $jane, $oliv);
      $l = $kid('Liam Smith', '2020-06-30', 'male', 'Allergies', 'Yes', $jane, $oliv);
      q('INSERT INTO attendance (`date`,status,child_id) VALUES (CURDATE(),?,?),(CURDATE(),?,?),(DATE_SUB(CURDATE(),INTERVAL 1 DAY),?,?)', ['Present', $a, 'Absent', $c, 'Present', $s]);
      q('INSERT INTO activity (activity_type,description,`date`,child_id) VALUES (?,?,CURDATE(),?)', ['Art Class', 'Painting and drawing', $a]);
      q('INSERT INTO learning (learning_type,progress_notes,`date`,child_id) VALUES (?,?,CURDATE(),?)', ['Reading', 'Improved comprehension', $s]);
      q('INSERT INTO meal (meal_name,allergies,`date`,child_id) VALUES (?,?,CURDATE(),?)', ['Rice and Chicken', 'None', $l]);
      q('INSERT INTO bill_payment (user_id,section_type,section_description,total_amount,due_date,payment_status,paid_at) VALUES (?,?,?,?,CURDATE(),?,NOW())', [$john, 'Tuition', 'Monthly tuition', 5000, 'Paid']);
      q('INSERT INTO bill_payment (user_id,section_type,section_description,total_amount,due_date) VALUES (?,?,?,?,DATE_ADD(CURDATE(),INTERVAL 7 DAY))', [$jane, 'Meal', 'Meals for the month', 3000]);
      notify($john, $a, 'Alice completed Art Class today.', 'Activity');
    }
    flash('Installed. Now delete install.php from your server, then sign in.');
    redirect('login.php');
  }
}
top('Install'); ?>
<div class="form"><h2>Install Bliss</h2>
<p class="mu">Creates the tables and your admin account. Runs only once.</p>
<?php if ($err) echo '<p class="note err">' . h($err) . '</p>'; ?>
<form method="post"><?= field() ?>
<label>Admin name<input name="name" required maxlength="100"></label>
<label>Admin email<input name="email" type="email" required maxlength="100"></label>
<label>Admin password (10+ characters)<input name="password" type="password" minlength="10" required autocomplete="new-password"></label>
<label><input type="checkbox" name="demo" value="1" class="chk"> Load demo data (accounts use password <code>password123</code>; delete them before going live)</label>
<button>Install</button></form></div>
<?php bottom();
