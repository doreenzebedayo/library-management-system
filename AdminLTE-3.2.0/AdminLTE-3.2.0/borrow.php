<?php
require 'db.php';

// ---- When the Save button is pressed ----
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $member_id = intval($_POST['member_id']);
    $book_id = intval($_POST['book_id']);
    $borrow_date = $conn->real_escape_string($_POST['borrow_date']);
    $due_date = $conn->real_escape_string($_POST['due_date']);

    // Check 1: everything filled in?
    if ($member_id == 0 || $book_id == 0 || $borrow_date == '' || $due_date == '') {
        go('borrow.php', 'Please choose a member, a book and both dates.', 'danger');
    }
    // Check 2: due date must not be before the borrow date
    if ($due_date < $borrow_date) {
        go('borrow.php', 'Due date cannot be earlier than the borrow date.', 'danger');
    }
    // Check 3: member must be active
    $member = $conn->query("SELECT status FROM members WHERE member_id = $member_id")->fetch_assoc();
    if (!$member || $member['status'] != 'Active') {
        go('borrow.php', 'This member is not active.', 'danger');
    }
    // Check 4: book must be Active (not Pending Retirement or Retired)
    $book = $conn->query("SELECT total_copies, status FROM books WHERE book_id = $book_id")->fetch_assoc();
    if (!$book || $book['status'] != 'Active') {
        go('borrow.php', 'This book cannot be borrowed.', 'danger');
    }
    // Check 5: is there a copy left? available = total copies - loans not returned
    $out = $conn->query("SELECT COUNT(*) AS n FROM borrowings WHERE book_id = $book_id AND return_date IS NULL")->fetch_assoc()['n'];
    if ($book['total_copies'] - $out <= 0) {
        go('borrow.php', 'No copies available. This borrowing was rejected.', 'danger');
    }

    // All checks passed: save the loan. return_date is left empty (NULL).
    $conn->query("INSERT INTO borrowings (book_id, member_id, borrow_date, due_date) VALUES ($book_id, $member_id, '$borrow_date', '$due_date')");
    go('loans.php?show=active', 'Borrowing recorded successfully. It now appears in the list below.');
}

$members = $conn->query("SELECT * FROM members WHERE status = 'Active' ORDER BY full_name");
$books = $conn->query("
  SELECT books.book_id, books.title, books.total_copies,
    (SELECT COUNT(*) FROM borrowings WHERE borrowings.book_id = books.book_id AND return_date IS NULL) AS on_loan
  FROM books WHERE status = 'Active' ORDER BY title");

$today = date('Y-m-d');
$in_14_days = date('Y-m-d', strtotime('+14 days'));

$title = 'Borrowing';
$page = 'borrow.php';
include 'header.php';
?>

<div class="card card-primary">
  <div class="card-header"><h3 class="card-title">Record a borrowing</h3></div>
  <form method="post">
    <div class="card-body">
      <div class="row">
        <div class="col-md-6 form-group">
          <label>Member</label>
          <select name="member_id" class="form-control" required>
            <option value="">-- Select member --</option>
            <?php while ($m = $members->fetch_assoc()) { ?>
              <option value="<?= $m['member_id'] ?>"><?= h($m['full_name']) ?> (<?= h($m['registration_number']) ?>)</option>
            <?php } ?>
          </select>
        </div>
        <div class="col-md-6 form-group">
          <label>Book</label>
          <select name="book_id" class="form-control" required>
            <option value="">-- Select book --</option>
            <?php while ($b = $books->fetch_assoc()) { ?>
              <?php $available = $b['total_copies'] - $b['on_loan']; ?>
              <option value="<?= $b['book_id'] ?>" <?php if ($available <= 0) echo 'disabled'; ?>><?= h($b['title']) ?> (<?= $available ?> available)</option>
            <?php } ?>
          </select>
          <small class="text-muted">Books with 0 available copies cannot be selected.</small>
        </div>
        <div class="col-md-6 form-group"><label>Borrow date</label><input type="date" name="borrow_date" class="form-control" value="<?= $today ?>" required></div>
        <div class="col-md-6 form-group"><label>Expected return date</label><input type="date" name="due_date" class="form-control" value="<?= $in_14_days ?>" required></div>
      </div>
    </div>
    <div class="card-footer">
      <button class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
      <a href="dashboard.php" class="btn btn-default">Cancel</a>
      <a href="loans.php?show=active" class="btn btn-success float-right">Process returns</a>
    </div>
  </form>
</div>

<?php include 'footer.php'; ?>
