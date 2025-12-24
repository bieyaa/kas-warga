<?php
// Wajib set $base & $active sebelum include file ini.
// $title optional.
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title ?? 'Admin - Kas Warga') ?></title>
  <link rel="stylesheet" href="<?= $base ?>/assets/style.css">
</head>

<body class="has-sidebar">
<div class="layout">

<?php include __DIR__ . '/sidebar_admin.php'; ?>

<main class="main">
