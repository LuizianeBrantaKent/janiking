<?php
// includes/staff_header.php
if (!isset($pageTitle)) $pageTitle = 'JaniKing';
if (!isset($activeNav)) $activeNav = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?> – JaniKing</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">

  <?php
    // Link your styles.css safely (no filemtime warnings if missing)
    $cssRel = 'assets/css/styles.css';   //  <-- make sure the filename is exactly styles.css
    $cssFs  = asset_path($cssRel);
    $v      = file_exists($cssFs) ? '?v=' . filemtime($cssFs) : '';
  ?>
  <link rel="stylesheet" href="<?= e(asset_url($cssRel . $v)) ?>">
</head>
<body>
<div class="app">
  <aside class="sidebar">
    <div class="logo">
      <img src="../assets/images/logo1.png" alt="JaniKing logo" />
      <strong class="ms-2">JaniKing</strong>
    </div>
    <nav class="nav">
      <a href="<?= e(asset_url('staff/staff_dashboard.php')) ?>"        class="<?= $activeNav==='dashboard'?'active':'' ?>"><i class="fa fa-gauge"></i><span>Dashboard</span></a>
      <a href="<?= e(asset_url('staff/staff_communication.php')) ?>"    class="<?= $activeNav==='communication'?'active':'' ?>"><i class="fa fa-comments"></i><span>Communication</span></a>
      <a href="<?= e(asset_url('staff/staff_reports.php')) ?>"          class="<?= $activeNav==='reports'?'active':'' ?>"><i class="fa fa-chart-line"></i><span>Reports</span></a>
      <a href="<?= e(asset_url('staff/staff_manage_documents.php')) ?>" class="<?= $activeNav==='documents'?'active':'' ?>"><i class="fa fa-folder-open"></i><span>Documents</span></a>
      <a href="<?= e(asset_url('staff/staff_upload_files.php')) ?>"     class="<?= $activeNav==='upload_files'?'active':'' ?>"><i class="fa fa-upload"></i><span>Upload Files</span></a>
      <a href="<?= e(asset_url('staff/staff_manage_training.php')) ?>"  class="<?= $activeNav==='training'?'active':'' ?>"><i class="fa fa-graduation-cap"></i><span>Training</span></a>
      <a href="<?= e(asset_url('staff/staff_profile.php')) ?>"          class="<?= $activeNav==='profile'?'active':'' ?>"><i class="fa fa-user-gear"></i><span>Profile / Settings</span></a>
    </nav>
    <div class="spacer"></div>
    <div class="logout">
      <a href="#" class="d-flex align-items-center" style="gap:10px; padding:10px 12px; color:#334155; text-decoration:none">
        <i class="fa fa-arrow-right-from-bracket"></i><span>Logout</span>
      </a>
    </div>
  </aside>

  <header class="header">
    <div class="user">
      <div class="text-end me-2">
        <strong><?= e($userName) ?></strong>
        <small class="d-block" style="color:var(--muted)"><?= e($userRole) ?></small>
      </div>
      <div class="avatar"><img src="<?= e($avatarPath) ?>" alt="Avatar"></div>
    </div>
  </header>

  <main class="content">
