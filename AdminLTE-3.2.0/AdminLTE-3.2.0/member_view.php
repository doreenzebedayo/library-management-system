<?php
require 'db.php';

$id = intval($_GET['id'] ?? 0);
$member = $conn->query("SELECT * FROM members WHERE member_id = $id")->fetch_assoc();
if (!$member) { go('members.php', 'Member not found.', 'danger'); }

$history = $conn->query("
  SELECT books.title, borrowings.borrow_date, borrowings.due_date, borrowings.return_date,
    CASE WHEN borrowings.return_date IS NOT NULL THEN 'Returned'
         WHEN borrowings.due_date < CURDATE() THEN 'Overdue'
         ELSE 'On loan' END AS loan_status
  FROM borrowings JOIN books ON books.book_id = borrowings.book_id
  WHERE borrowings.member_id = $id ORDER BY borrowings.borrow_date DESC");

$title = 'Member Details';
$page = 'members.php';
include 'header.php';
?>

<div class="card">
  <div class="card-header">
    <h3 class="card-title"><?= h($member['full_name']) ?></h3>
    <div class="card-tools">
      <a href="member_form.php?id=<?= $id ?>" class="btn btn-sm btn-primary">Edit</a>
      <a href="members.php" class="btn btn-sm btn-default">Back</a>
    </div>
  </div>
  <div class="card-body">
    <p><b>Registration number:</b> <?= h($member['registration_number']) ?></p>
    <p><b>Email:</b> <?= h($member['email']) ?></p>
    <p><b>Phone:</b> <?= h($member['phone']) ?></p>
    <p><b>Status:</b> <?= badge($member['status']) ?></p>
  </div>
</div>

<div class="card">
  <div class="card-header"><h3 class="card-title">Borrowing history</h3></div>
  <div class="card-body table-responsive p-0">
    <table class="table table-hover">
      <thead><tr><th>Book</th><th>Borrowed</th><th>Due</th><th>Returned</th><th>Status</th></tr></thead>
      <tbody>
      <?php if ($history->num_rows == 0) { ?><tr><td colspan="5" class="text-center text-muted">No borrowing yet.</td></tr><?php } ?>
      <?php while ($r = $history->fetch_assoc()) { ?>
        <tr>
          <td><?= h($r['title']) ?></td><td><?= $r['borrow_date'] ?></td><td><?= $r['due_date'] ?></td>
          <td><?= h($r['return_date']) ?></td><td><?= badge($r['loan_status']) ?></td>
        </tr>
      <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'footer.php'; ?>
