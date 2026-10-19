<?php
require 'db.php';

// Each number comes from one simple COUNT query
$r = $conn->query("SELECT COUNT(*) AS n FROM books WHERE status = 'Active'");
$total_books = $r->fetch_assoc()['n'];

$r = $conn->query("SELECT COUNT(*) AS n FROM members WHERE status = 'Active'");
$total_members = $r->fetch_assoc()['n'];

// Active loan = not returned yet (return_date is empty)
$r = $conn->query("SELECT COUNT(*) AS n FROM borrowings WHERE return_date IS NULL");
$active_loans = $r->fetch_assoc()['n'];

// Overdue = not returned AND due date is before today
$r = $conn->query("SELECT COUNT(*) AS n FROM borrowings WHERE return_date IS NULL AND due_date < CURDATE()");
$overdue_loans = $r->fetch_assoc()['n'];

// The 5 newest loans
$recent = $conn->query("
  SELECT members.full_name, books.title, borrowings.borrow_date, borrowings.due_date,
    CASE WHEN borrowings.return_date IS NOT NULL THEN 'Returned'
         WHEN borrowings.due_date < CURDATE() THEN 'Overdue'
         ELSE 'On loan' END AS loan_status
  FROM borrowings
  JOIN members ON members.member_id = borrowings.member_id
  JOIN books ON books.book_id = borrowings.book_id
  ORDER BY borrowings.borrow_date DESC, borrowings.borrowing_id DESC
  LIMIT 5");

$title = 'Dashboard';
$page = 'dashboard.php';
include 'header.php';
?>

<div class="row">
  <div class="col-lg-3 col-6">
    <div class="small-box bg-info">
      <div class="inner"><h3><?= $total_books ?></h3><p>Book Titles</p></div>
      <div class="icon"><i class="fas fa-book"></i></div>
      <a href="books.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-success">
      <div class="inner"><h3><?= $total_members ?></h3><p>Members</p></div>
      <div class="icon"><i class="fas fa-users"></i></div>
      <a href="members.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-warning">
      <div class="inner"><h3><?= $active_loans ?></h3><p>Active Loans</p></div>
      <div class="icon"><i class="fas fa-exchange-alt"></i></div>
      <a href="loans.php?show=active" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-danger">
      <div class="inner"><h3><?= $overdue_loans ?></h3><p>Overdue Loans</p></div>
      <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
      <a href="loans.php?show=overdue" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">Recent Borrowing Activity</h3>
    <div class="card-tools"><a href="loans.php" class="btn btn-sm btn-primary">View all</a></div>
  </div>
  <div class="card-body table-responsive p-0">
    <table class="table table-hover">
      <thead><tr><th>Member</th><th>Book</th><th>Borrowed</th><th>Due</th><th>Status</th></tr></thead>
      <tbody>
      <?php while ($row = $recent->fetch_assoc()) { ?>
        <tr>
          <td><?= h($row['full_name']) ?></td>
          <td><?= h($row['title']) ?></td>
          <td><?= $row['borrow_date'] ?></td>
          <td><?= $row['due_date'] ?></td>
          <td><?= badge($row['loan_status']) ?></td>
        </tr>
      <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'footer.php'; ?>
