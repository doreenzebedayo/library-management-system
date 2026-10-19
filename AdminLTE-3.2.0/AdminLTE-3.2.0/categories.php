<?php
require 'db.php';

$error = '';
$name = '';
$description = '';

// ?id=3 means we are EDITING category 3. No id means ADDING a new one.
$id = intval($_GET['id'] ?? 0);
if ($id > 0) {
    $cat = $conn->query("SELECT * FROM categories WHERE category_id = $id")->fetch_assoc();
    if (!$cat) { go('categories.php', 'Category not found.', 'danger'); }
    $name = $cat['category_name'];
    $description = $cat['description'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // ---- Delete button ----
    if (isset($_POST['delete_id'])) {
        $del = intval($_POST['delete_id']);
        // A category that still has books cannot be deleted
        $used = $conn->query("SELECT COUNT(*) AS n FROM books WHERE category_id = $del")->fetch_assoc()['n'];
        if ($used > 0) {
            go('categories.php', "Cannot delete: $used book(s) use this category.", 'danger');
        }
        $conn->query("DELETE FROM categories WHERE category_id = $del");
        go('categories.php', 'Category deleted.');
    }

    // ---- Save button (add or edit) ----
    $name = trim($_POST['category_name']);
    $description = trim($_POST['description']);

    if ($name == '') {
        $error = 'Category name is required.';
    } else {
        $n = $conn->real_escape_string($name);
        // An empty description is saved as NULL (no value)
        if ($description == '') { $d = 'NULL'; } else { $d = "'" . $conn->real_escape_string($description) . "'"; }

        if ($id > 0) {
            $ok = $conn->query("UPDATE categories SET category_name = '$n', description = $d WHERE category_id = $id");
        } else {
            $ok = $conn->query("INSERT INTO categories (category_name, description) VALUES ('$n', $d)");
        }

        if ($ok) {
            go('categories.php', 'Category saved.');
        } elseif ($conn->errno == 1062) {
            $error = 'A category with this name already exists.';
        } else {
            $error = 'Could not save: ' . $conn->error;
        }
    }
}

// List all categories with the number of books in each
$categories = $conn->query("
  SELECT categories.*,
    (SELECT COUNT(*) FROM books WHERE books.category_id = categories.category_id) AS book_count
  FROM categories ORDER BY category_name");

$title = 'Categories';
$page = 'categories.php';
include 'header.php';
?>

<div class="card card-primary">
  <div class="card-header"><h3 class="card-title"><?php if ($id > 0) { echo 'Edit category'; } else { echo 'Add category'; } ?></h3></div>
  <form method="post">
    <div class="card-body">
      <div class="row">
        <div class="col-md-5 form-group"><label>Category name</label><input name="category_name" class="form-control" value="<?= h($name) ?>" required></div>
        <div class="col-md-7 form-group"><label>Description (optional)</label><input name="description" class="form-control" value="<?= h($description) ?>"></div>
      </div>
    </div>
    <div class="card-footer">
      <button class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
      <?php if ($id > 0) { ?><a href="categories.php" class="btn btn-default">Cancel</a><?php } ?>
    </div>
  </form>
</div>

<div class="card">
  <div class="card-header"><h3 class="card-title">Category list</h3></div>
  <div class="card-body table-responsive p-0">
    <table class="table table-hover">
      <thead><tr><th>Name</th><th>Description</th><th>Books</th><th>Actions</th></tr></thead>
      <tbody>
      <?php if ($categories->num_rows == 0) { ?><tr><td colspan="4" class="text-center text-muted">No categories yet.</td></tr><?php } ?>
      <?php while ($c = $categories->fetch_assoc()) { ?>
        <tr>
          <td><?= h($c['category_name']) ?></td>
          <td><?= h($c['description']) ?></td>
          <td><?= $c['book_count'] ?></td>
          <td>
            <a href="categories.php?id=<?= $c['category_id'] ?>" class="btn btn-primary btn-sm">Edit</a>
            <form method="post" class="d-inline" onsubmit="return confirm('Delete this category?');">
              <input type="hidden" name="delete_id" value="<?= $c['category_id'] ?>">
              <button class="btn btn-danger btn-sm" <?php if ($c['book_count'] > 0) echo 'disabled'; ?>>Delete</button>
            </form>
          </td>
        </tr>
      <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'footer.php'; ?>
