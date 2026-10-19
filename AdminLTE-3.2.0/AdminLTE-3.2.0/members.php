<?php
require 'db.php';

// ---- When Activate / Deactivate is pressed ----
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['member_id']);
    $member = $conn->query("SELECT status FROM members WHERE member_id = $id")->fetch_assoc();

    if ($member['status'] == 'Active') {
        // A member who still holds books cannot be deactivated
        $out = $conn->query("SELECT COUNT(*) AS n FROM borrowings WHERE member_id = $id AND return_date IS NULL")->fetch_assoc()['n'];
        if ($out > 0) { go('members.php', "This member still has $out unreturned book(s).", 'danger'); }
        $conn->query("UPDATE members SET status = 'Inactive' WHERE member_id = $id");
        go('members.php', 'Member deactivated.');
    } else {
        $conn->query("UPDATE members SET status = 'Active' WHERE member_id = $id");
        go('members.php', 'Member activated.');
    }
}

// ---- Search ----
$q = $conn->real_escape_string(trim($_GET['q'] ?? ''));
$members = $conn->query("
  SELECT members.*,
    (SELECT COUNT(*) FROM borrowings WHERE borrowings.member_id = members.member_id AND return_date IS NULL) AS on_loan
  FROM members
  WHERE full_name LIKE '%$q%' OR registration_number LIKE '%$q%'
  ORDER BY full_name");

$title = 'Members';
$page = 'members.php';
include 'header.php';
?>

<div class="card">
  <div class="card-body">
    <form method="get" class="form-row">
      <div class="col-md-8 mb-2"><input name="q" class="form-control" placeholder="Search by name or registration number" value="<?= h($_GET['q'] ?? '') ?>"></div>
      <div class="col-md-4 mb-2"><button class="btn btn-primary"><i class="fas fa-search"></i> Search</button> <a href="members.php" class="btn btn-default">Reset</a></div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">Member list</h3>
    <div class="card-tools"><a href="member_form.php" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> Add member</a></div>
  </div>
  <div class="card-body table-responsive p-0">
    <table class="table table-hover">
      <thead><tr><th>Name</th><th>Reg. no.</th><th>Email</th><th>Phone</th><th>Books out</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php if ($members->num_rows == 0) { ?><tr><td colspan="7" class="text-center text-muted">No members found.</td></tr><?php } ?>
      <?php while ($m = $members->fetch_assoc()) { ?>
        <tr>
          <td><?= h($m['full_name']) ?></td>
          <td><?= h($m['registration_number']) ?></td>
          <td><?= h($m['email']) ?></td>
          <td><?= h($m['phone']) ?></td>
          <td><?= $m['on_loan'] ?></td>
          <td><?= badge($m['status']) ?></td>
          <td>
            <a href="member_view.php?id=<?= $m['member_id'] ?>" class="btn btn-info btn-sm">View</a>
            <a href="member_form.php?id=<?= $m['member_id'] ?>" class="btn btn-primary btn-sm">Edit</a>
            <form method="post" class="d-inline" onsubmit="return confirm('Change this member status?');">
              <input type="hidden" name="member_id" value="<?= $m['member_id'] ?>">
              <?php if ($m['status'] == 'Active') { ?>
                <button class="btn btn-danger btn-sm">Deactivate</button>
              <?php } else { ?>
                <button class="btn btn-success btn-sm">Activate</button>
              <?php } ?>
            </form>
          </td>
        </tr>
      <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'footer.php'; ?>
