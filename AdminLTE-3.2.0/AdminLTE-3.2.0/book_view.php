<?php
require 'db.php';

$id = intval($_GET['id'] ?? 0);
$book = $conn->query("SELECT books.*, categories.category_name FROM books JOIN categories ON categories.category_id = books.category_id WHERE book_id = $id")->fetch_assoc();
if (!$book) { go('books.php', 'Book not found.', 'danger'); }

$on_loan = $conn->query("SELECT COUNT(*) AS n FROM borrowings WHERE book_id = $id AND return_date IS NULL")->fetch_assoc()['n'];
$available = $book['total_copies'] - $on_loan;

// Earliest due date among loans not returned = estimated date a copy comes back
$soonest = $conn->query("SELECT MIN(due_date) AS d FROM borrowings WHERE book_id = $id AND return_date IS NULL")->fetch_assoc()['d'];

$history = $conn->query("
  SELECT members.full_name, borrowings.borrow_date, borrowings.due_date, borrowings.return_date,
    CASE WHEN borrowings.return_date IS NOT NULL THEN 'Returned'
         WHEN borrowings.due_date < CURDATE() THEN 'Overdue'
         ELSE 'On loan' END AS loan_status
  FROM borrowings JOIN members ON members.member_id = borrowings.member_id
  WHERE borrowings.book_id = $id ORDER BY borrowings.borrow_date DESC");

$title = 'Book Details';
$page = 'books.php';
include 'header.php';
?>

<div class="card">
  <div class="card-header">
    <h3 class="card-title"><?= h($book['title']) ?></h3>
    <div class="card-tools">
      <?php if ($book['status'] != 'Retired') { ?><a href="book_form.php?id=<?= $id ?>" class="btn btn-sm btn-primary">Edit</a><?php } ?>
      <a href="books.php" class="btn btn-sm btn-default">Back</a>
    </div>
  </div>
  <div class="card-body">
    <p><b>Author:</b> <?= h($book['author']) ?></p>
    <p><b>Edition:</b> <?= h($book['edition']) ?></p>
    <p><b>Category:</b> <?= h($book['category_name']) ?></p>
    <p><b>Total copies:</b> <?= $book['total_copies'] ?></p>
    <p><b>Available copies:</b> <?= $available ?>
      <?php if ($available <= 0 && $soonest) { ?>
        <small class="text-muted">(a copy is expected back around <?= $soonest ?> - an estimate, not a guarantee)</small>
      <?php } ?>
    </p>
    <p><b>Status:</b> <?= badge($book['status']) ?></p>
  </div>
</div>

<div class="card">
  <div class="card-header"><h3 class="card-title">Borrowing history</h3></div>
  <div class="card-body table-responsive p-0">
    <table class="table table-hover">
      <thead><tr><th>Member</th><th>Borrowed</th><th>Due</th><th>Returned</th><th>Status</th></tr></thead>
      <tbody>
      <?php if ($history->num_rows == 0) { ?><tr><td colspan="5" class="text-center text-muted">Never borrowed.</td></tr><?php } ?>
      <?php while ($r = $history->fetch_assoc()) { ?>
        <tr>
          <td><?= h($r['full_name']) ?></td><td><?= $r['borrow_date'] ?></td><td><?= $r['due_date'] ?></td>
          <td><?= h($r['return_date']) ?></td><td><?= badge($r['loan_status']) ?></td>
        </tr>
      <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'footer.php'; ?>
