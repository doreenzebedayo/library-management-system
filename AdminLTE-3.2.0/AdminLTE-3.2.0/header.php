<?php
// Top part of every page: menu, title, breadcrumb and message box.
// Each page sets $title (page name) and $page (file name) before including this file.
$msg = $_GET['msg'] ?? '';
$type = $_GET['type'] ?? 'info';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Library | <?= h($title) ?></title>
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a></li>
    </ul>
  </nav>

  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="dashboard.php" class="brand-link"><span class="brand-text font-weight-light pl-3">Library System</span></a>
    <div class="sidebar">
      <ul class="nav nav-pills nav-sidebar flex-column mt-2">
        <li class="nav-item"><a href="dashboard.php" class="nav-link <?php if ($page == 'dashboard.php') echo 'active'; ?>"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a></li>
        <li class="nav-item"><a href="books.php" class="nav-link <?php if ($page == 'books.php') echo 'active'; ?>"><i class="nav-icon fas fa-book"></i><p>Books</p></a></li>
        <li class="nav-item"><a href="categories.php" class="nav-link <?php if ($page == 'categories.php') echo 'active'; ?>"><i class="nav-icon fas fa-tags"></i><p>Categories</p></a></li>
        <li class="nav-item"><a href="members.php" class="nav-link <?php if ($page == 'members.php') echo 'active'; ?>"><i class="nav-icon fas fa-users"></i><p>Members</p></a></li>
        <li class="nav-item"><a href="loans.php" class="nav-link <?php if ($page == 'loans.php') echo 'active'; ?>"><i class="nav-icon fas fa-list"></i><p>Loans</p></a></li>
        <li class="nav-item"><a href="borrow.php" class="nav-link <?php if ($page == 'borrow.php') echo 'active'; ?>"><i class="nav-icon fas fa-exchange-alt"></i><p>Borrowing</p></a></li>
      </ul>
    </div>
  </aside>

  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6"><h1><?= h($title) ?></h1></div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active"><?= h($title) ?></li>
            </ol>
          </div>
        </div>
      </div>
    </section>
    <section class="content">
      <div class="container-fluid">
        <?php if ($msg != '') { ?>
          <div class="alert alert-<?= h($type) ?> alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button><?= h($msg) ?>
          </div>
        <?php } ?>
        <?php if (!empty($error)) { ?>
          <div class="alert alert-danger"><?= h($error) ?></div>
        <?php } ?>
