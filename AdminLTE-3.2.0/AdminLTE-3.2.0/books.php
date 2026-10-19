<?php
require 'db.php';

// ---- When a Retire or Confirm button is pressed ----
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['book_id']);
    $action = $_POST['action'];

    $book = $conn->query("SELECT status FROM books WHERE book_id = $id")->fetch_assoc();
    // How many copies of this book are still out?
    $out = $conn->query("SELECT COUNT(*) AS n FROM borrowings WHERE book_id = $id AND return_date IS NULL")->fetch_assoc()['n'];

    if ($action == 'retire' && $book['status'] == 'Active') {
        if ($out > 0) {
            $new_status = 'Pending Retirement';   // copies are still out, so wait
            $message = "$out copies are still out. Book is now Pending Retirement.";
        } else {
            $new_status = 'Retired';
            $message = 'Book retired.';
        }
    } elseif ($action == 'confirm' && $book['status'] == 'Pending Retirement' && $out == 0) {
        $new_status = 'Retired';
        $message = 'Retirement confirmed.';
    } else {
        go('books.php', 'That action is not allowed for this book.', 'danger');
    }

    $conn->query("UPDATE books SET status = '$new_status' WHERE book_id = $id");
    go('books.php', $message, 'success');
}

// ---- Search and filters ----
$q = $conn->real_escape_string(trim($_GET['q'] ?? ''));
$category = intval($_GET['category'] ?? 0);
$status = $conn->real_escape_string($_GET['status'] ?? '');

$where = "(books.title LIKE '%$q%' OR books.author LIKE '%$q%')";
if ($category > 0) { $where = $where . " AND books.category_id = $category"; }
if ($status != '') { $where = $where . " AND books.status = '$status'"; }

// on_loan = number of this book's loans that are not returned yet
$books = $conn->query("
  SELECT books.*, categories.category_name,
    (SELECT COUNT(*) FROM borrowings WHERE borrowings.book_id = books.book_id AND return_date IS NULL) AS on_loan
  FROM books
  JOIN categories ON categories.category_id = books.category_id
  WHERE $where
  ORDER BY books.title");

$categories = $conn->query("SELECT * FROM categories ORDER BY category_name");

$title = 'Books';
$page = 'books.php';
include 'header.php';
?>

<div class="card">
  <div class="card-body">
    <form method="get" class="form-row">
      <div class="col-md-5 mb-2"><input type="text" name="q" class="form-control" placeholder="Search by title or author" value="<?= h($_GET['q'] ?? '') ?>"></div>
      <div class="col-md-3 mb-2">
        <select name="category" class="form-control">
          <option value="0">All categories</option>
          <?php while ($c = $categories->fetch_assoc()) { ?>
            <option value="<?= $c['category_id'] ?>" <?php if ($category == $c['category_id']) echo 'selected'; ?>><?= h($c['category_name']) ?></option>
          <?php } ?>
        </select>
      </div>
      <div class="col-md-2 mb-2">
        <select name="status" class="form-control">
          <option value="">All statuses</option>
          <option <?php if ($status == 'Active') echo 'selected'; ?>>Active</option>
          <option <?php if ($status == 'Pending Retirement') echo 'selected'; ?>>Pending Retirement</option>
          <option <?php if ($status == 'Retired') echo 'selected'; ?>>Retired</option>
        </select>
      </div>
      <div class="col-md-2 mb-2">
        <button class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
        <a href="books.php" class="btn btn-default">Reset</a>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">Book list</h3>
    <div class="card-tools"><a href="book_form.php" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> Add book</a></div>
  </div>
  <div class="card-body table-responsive p-0">
    <table class="table table-hover">
      <thead><tr><th>Title</th><th>Author</th><th>Edition</th><th>Category</th><th>Total</th><th>Available</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php if ($books->num_rows == 0) { ?>
        <tr><td colspan="8" class="text-center text-muted">No books found.</td></tr>
      <?php } ?>
      <?php while ($b = $books->fetch_assoc()) { ?>
        <?php $available = $b['total_copies'] - $b['on_loan']; ?>
        <tr>
          <td><?= h($b['title']) ?></td>
          <td><?= h($b['author']) ?></td>
          <td><?= h($b['edition']) ?></td>
          <td><?= h($b['category_name']) ?></td>
          <td><?= $b['total_copies'] ?></td>
          <td><?= $available ?></td>
          <td><?= badge($b['status']) ?></td>
          <td>
            <a href="book_view.php?id=<?= $b['book_id'] ?>" class="btn btn-info btn-sm">View</a>
            <?php if ($b['status'] != 'Retired') { ?>
              <a href="book_form.php?id=<?= $b['book_id'] ?>" class="btn btn-primary btn-sm">Edit</a>
            <?php } ?>
            <?php if ($b['status'] == 'Active') { ?>
              <form method="post" class="d-inline" onsubmit="return confirm('Retire this book?');">
                <input type="hidden" name="book_id" value="<?= $b['book_id'] ?>">
                <input type="hidden" name="action" value="retire">
                <button class="btn btn-danger btn-sm">Retire</button>
              </form>
            <?php } ?>
            <?php if ($b['status'] == 'Pending Retirement') { ?>
              <form method="post" class="d-inline">
                <input type="hidden" name="book_id" value="<?= $b['book_id'] ?>">
                <input type="hidden" name="action" value="confirm">
                <button class="btn btn-warning btn-sm" <?php if ($b['on_loan'] > 0) echo 'disabled'; ?>>Confirm retirement</button>
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
