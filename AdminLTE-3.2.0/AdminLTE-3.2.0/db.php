<?php
// Connect to the database library_db
mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli("localhost", "root", "", "library_db");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// h() makes text safe to show on a web page
function h($text) { return htmlspecialchars($text ?? ''); }

// go() sends the user to another page and shows a message there
function go($page, $msg, $type = 'success') {
    if (strpos($page, '?') === false) { $join = '?'; } else { $join = '&'; }
    header("Location: " . $page . $join . "type=" . $type . "&msg=" . urlencode($msg));
    exit;
}

// badge() makes a coloured label from a status word
function badge($text) {
    if ($text == 'Active' || $text == 'Returned') { $color = 'success'; }
    elseif ($text == 'Overdue') { $color = 'danger'; }
    elseif ($text == 'On loan' || $text == 'Pending Retirement') { $color = 'warning'; }
    else { $color = 'secondary'; }
    return '<span class="badge badge-' . $color . '">' . h($text) . '</span>';
}
?>
