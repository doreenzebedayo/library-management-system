<?php
require 'db.php';

// ?id=5 means EDIT member 5. No id means ADD a new member.
$id = intval($_GET['id'] ?? 0);
$full_name = '';
$reg_no = '';
$email = '';
$phone = '';
$error = '';

if ($id > 0) {
    $member = $conn->query("SELECT * FROM members WHERE member_id = $id")->fetch_assoc();
    if (!$member) { go('members.php', 'Member not found.', 'danger'); }
    $full_name = $member['full_name'];
    $reg_no = $member['registration_number'];
    $email = $member['email'];
    $phone = $member['phone'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $reg_no = trim($_POST['registration_number']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    if ($full_name == '' || $reg_no == '') {
        $error = 'Full name and registration number are required.';
    } elseif ($email != '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'The email address is not valid.';
    } else {
        $n = $conn->real_escape_string($full_name);
        $r = $conn->real_escape_string($reg_no);
        // Empty email/phone are saved as NULL. This lets many members have no email
        // even though email must be unique.
        if ($email == '') { $e = 'NULL'; } else { $e = "'" . $conn->real_escape_string($email) . "'"; }
        if ($phone == '') { $p = 'NULL'; } else { $p = "'" . $conn->real_escape_string($phone) . "'"; }

        if ($id > 0) {
            $ok = $conn->query("UPDATE members SET full_name = '$n', registration_number = '$r', email = $e, phone = $p WHERE member_id = $id");
        } else {
            $ok = $conn->query("INSERT INTO members (full_name, registration_number, email, phone) VALUES ('$n', '$r', $e, $p)");
        }

        if ($ok) {
            go('members.php', 'Member saved.');
        } elseif ($conn->errno == 1062) {
            $error = 'That registration number or email is already used by another member.';
        } else {
            $error = 'Could not save: ' . $conn->error;
        }
    }
}

if ($id > 0) { $title = 'Edit Member'; } else { $title = 'Add Member'; }
$page = 'members.php';
include 'header.php';
?>

<div class="card card-primary">
  <form method="post">
    <div class="card-body">
      <div class="row">
        <div class="col-md-6 form-group"><label>Full name</label><input name="full_name" class="form-control" value="<?= h($full_name) ?>" required></div>
        <div class="col-md-6 form-group"><label>Registration number</label><input name="registration_number" class="form-control" value="<?= h($reg_no) ?>" required></div>
        <div class="col-md-6 form-group"><label>Email (optional)</label><input name="email" class="form-control" value="<?= h($email) ?>"></div>
        <div class="col-md-6 form-group"><label>Phone (optional)</label><input name="phone" class="form-control" value="<?= h($phone) ?>"></div>
      </div>
    </div>
    <div class="card-footer">
      <button class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
      <a href="members.php" class="btn btn-default">Cancel</a>
    </div>
  </form>
</div>

<?php include 'footer.php'; ?>
