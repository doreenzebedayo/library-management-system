<?php
require 'db.php';

// If the address has ?id=5 we are EDITING book 5. If not, we are ADDING a new book.
$id = intval($_GET['id'] ?? 0);

// Start with empty values (for adding)
$title_value = '';
$author = '';
$edition = '';
$category_id = 0;
$total_copies = 1;
$on_loan = 0;
$error = '';

// When editing, load the book from the database
if ($id > 0) {
    $book = $conn->query("SELECT * FROM books WHERE book_id = $id")->fetch_assoc();
    if (!$book) { go('books.php', 'Book not found.', 'danger'); }
    if ($book['status'] == 'Retired') { go('books.php', 'Retired books cannot be edited.', 'warning'); }
    $title_value = $book['title'];
    $author = $book['author'];
    $edition = $book['edition'];
    $category_id = $book['category_id'];
    $total_copies = $book['total_copies'];
    $on_loan = $conn->query("SELECT COUNT(*) AS n FROM borrowings WHERE book_id = $id AND return_date IS NULL")->fetch_assoc()['n'];
}

// When the Save button is pressed
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title_value = trim($_POST['title']);
    $author = trim($_POST['author']);
    $edition = trim($_POST['edition']);
    $category_id = intval($_POST['category_id']);
    $total_copies = intval($_POST['total_copies']);

    // Checks (validation)
    if ($title_value == '' || $author == '' || $category_id == 0) {
        $error = 'Title, author and category are required.';
    } elseif ($total_copies < 0) {
        $error = 'Total copies cannot be negative.';
    } elseif ($id > 0 && $total_copies < $on_loan) {
        $error = "Total copies cannot be less than the $on_loan copies that are on loan.";
    } else {
        $t = $conn->real_escape_string($title_value);
        $a = $conn->real_escape_string($author);
        // An empty edition is saved as NULL (no value)
        if ($edition == '') { $e = 'NULL'; } else { $e = "'" . $conn->real_escape_string($edition) . "'"; }

        if ($id > 0) {
            $conn->query("UPDATE books SET category_id = $category_id, title = '$t', author = '$a', edition = $e, total_copies = $total_copies WHERE book_id = $id");
            go('books.php', 'Book updated.');
        } else {
            $conn->query("INSERT INTO books (category_id, title, author, edition, total_copies) VALUES ($category_id, '$t', '$a', $e, $total_copies)");
            go('books.php', 'Book added.');
        }
    }
}

$categories = $conn->query("SELECT * FROM categories ORDER BY category_name");

if ($id > 0) { $title = 'Edit Book'; } else { $title = 'Add Book'; }
$page = 'books.php';
include 'header.php';
?>

<div class="card card-primary">
  <form method="post">
    <div class="card-body">
      <div class="row">
        <div class="col-md-6 form-group"><label>Title</label><input name="title" class="form-control" value="<?= h($title_value) ?>" required></div>
        <div class="col-md-6 form-group"><label>Author</label><input name="author" class="form-control" value="<?= h($author) ?>" required></div>
      </div>
      <div class="row">
        <div class="col-md-4 form-group">
          <label>Category</label>
          <select name="category_id" class="form-control" required>
            <option value="">-- Select --</option>
            <?php while ($c = $categories->fetch_assoc()) { ?>
              <option value="<?= $c['category_id'] ?>" <?php if ($category_id == $c['category_id']) echo 'selected'; ?>><?= h($c['category_name']) ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="col-md-4 form-group"><label>Edition (optional)</label><input name="edition" class="form-control" value="<?= h($edition) ?>"></div>
        <div class="col-md-4 form-group"><label>Total copies</label><input type="number" min="0" name="total_copies" class="form-control" value="<?= $total_copies ?>" required></div>
      </div>
    </div>
    <div class="card-footer">
      <button class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
      <a href="books.php" class="btn btn-default">Cancel</a>
    </div>
  </form>
</div>

<?php include 'footer.php'; ?>
