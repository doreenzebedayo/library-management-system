<?php
require 'db.php';

// Which list to show: all, active, overdue or returned
$show = $_GET['show'] ?? 'all';
if ($show == 'active') { $where = "borrowings.return_date IS NULL"; }
elseif ($show == 'overdue') { $where = "borrowings.return_date IS NULL AND borrowings.due_date < CURDATE()"; }
elseif ($show == 'returned') { $where = "borrowings.return_date IS NOT NULL"; }
else { $show = 'all'; $where = "1 = 1"; }

// ---- When the Return button is pressed ----
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['borrowing_id']);
    // Set return date to today (only if it is still empty)
    $conn->query("UPDATE borrowings SET return_date = CURDATE() WHERE borrowing_id = $id AND return_date IS NULL");
    go("loans.php?show=$show", 'Book marked as returned.');
}

$loans = $conn->query("
  SELECT borrowings.*, members.full_name, books.title,
    CASE WHEN borrowings.return_date IS NOT NULL THEN 'Returned'
         WHEN borrowings.due_date < CURDATE() THEN 'Overdue'
         ELSE 'On loan' END AS loan_status
  FROM borrowings
  JOIN members ON members.member_id = borrowings.member_id
  JOIN books ON books.book_id = borrowings.book_id
  WHERE $where
  ORDER BY borrowings.due_date");

$title = 'Loans';
$page = 'loans.php';
include 'header.php';
?>

<ul class="nav nav-pills mb-3">
  <li class="nav-item"><a class="nav-link <?php if ($show == 'all') echo 'active'; ?>" href="loans.php?show=all">All</a></li>
  <li class="nav-item"><a class="nav-link <?php if ($show == 'active') echo 'active'; ?>" href="loans.php?show=active">Active</a></li>
  <li class="nav-item"><a class="nav-link <?php if ($show == 'overdue') echo 'active'; ?>" href="loans.php?show=overdue">Overdue</a></li>
  <li class="nav-item"><a class="nav-link <?php if ($show == 'returned') echo 'active'; ?>" href="loans.php?show=returned">Returned</a></li>
  <li class="nav-item ml-auto"><a class="btn btn-success" href="borrow.php"><i class="fas fa-plus"></i> New borrowing</a></li>
</ul>

<div class="card">
  <div class="card-body table-responsive p-0">
    <table class="table table-hover">
      <thead><tr><th>Member</th><th>Book</th><th>Borrowed</th><th>Due</th><th>Returned</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
      <?php if ($loans->num_rows == 0) { ?><tr><td colspan="7" class="text-center text-muted">No loans in this list.</td></tr><?php } ?>
      <?php while ($l = $loans->fetch_assoc()) { ?>
        <tr>
          <td><?= h($l['full_name']) ?></td>
          <td><?= h($l['title']) ?></td>
          <td><?= $l['borrow_date'] ?></td>
          <td><?= $l['due_date'] ?></td>
          <td><?= h($l['return_date']) ?></td>
          <td><?= badge($l['loan_status']) ?></td>
          <td>
            <?php if ($l['return_date'] == null) { ?>
              <form method="post" action="loans.php?show=<?= $show ?>" class="d-inline" onsubmit="return confirm('Mark as returned?');">
                <input type="hidden" name="borrowing_id" value="<?= $l['borrowing_id'] ?>">
                <button class="btn btn-success btn-sm">Return</button>
              </form>
            <?php } ?>
          </td>
        </tr>
      <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'footer.php'; ?>
