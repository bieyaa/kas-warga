<?php
require_once __DIR__.'/../config/functions.php';

$id = (int)($_GET['id'] ?? 0);
$st = q("SELECT * FROM warga WHERE id=?",'i',$id);
$r  = $st->get_result()->fetch_assoc();

if(!$r){
  http_response_code(404);
  echo "Warga tidak ditemukan";
  exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $nama    = trim($_POST['nama'] ?? '');
  $alamat  = trim($_POST['alamat'] ?? '');
  $no_hp   = trim($_POST['no_hp'] ?? '');
  $username= trim($_POST['username'] ?? '');
  $password= trim($_POST['password'] ?? '');
  $status  = $_POST['status'] ?? 'pending';

  // validasi sederhana
  if($nama === '' || $alamat === '' || $username === '' || $password === ''){
    $err = "Nama, Alamat, Username & Password wajib diisi.";
  }

  if(!isset($err)){
    // update data warga (termasuk akun login)
    q(
      "UPDATE warga SET nama=?, alamat=?, no_hp=?, username=?, password=?, status=? WHERE id=?",
      'ssssssi',
      $nama, $alamat, $no_hp, $username, $password, $status, $id
    );
    redirect('index.php');
  } else {
    // kalau error, form tetap isi dari input terbaru
    $r['nama']     = $nama;
    $r['alamat']   = $alamat;
    $r['no_hp']    = $no_hp;
    $r['username'] = $username;
    $r['password'] = $password;
    $r['status']   = $status;
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Warga</title>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="nav">
  <div class="brand">Kas Warga</div>
  <div class="menu">
    <a href="../index.php">Dashboard</a>
    <a href="index.php">Warga</a>
  </div>
  <div class="burger">☰</div>
</div>

<div class="container">
  <div class="card">
    <h3>Edit Warga</h3>

    <?php if(isset($err)): ?>
      <div class="badge bad" style="display:inline-block"><?=h($err)?></div>
    <?php endif; ?>

    <form method="post" class="mt-2">
      <label>Nama</label>
      <input name="nama" value="<?=h($r['nama'])?>" required>

      <label>Alamat</label>
      <input name="alamat" value="<?=h($r['alamat'])?>" required>

      <label>No. HP</label>
      <input name="no_hp" value="<?=h($r['no_hp'])?>">

      <label>Username</label>
      <input name="username" value="<?=h($r['username'] ?? '')?>" required>

      <label>Password</label>
      <input name="password" value="<?=h($r['password'] ?? '')?>" required>

      <label>Status Akun</label>
      <select name="status">
        <option value="pending" <?= (isset($r['status']) && $r['status']==='pending') ? 'selected' : '' ?>>Pending</option>
        <option value="aktif"   <?= (isset($r['status']) && $r['status']==='aktif')   ? 'selected' : '' ?>>Aktif</option>
      </select>

      <div class="mt-2" style="display:flex;gap:10px">
        <button class="btn" type="submit">Simpan</button>
        <a class="btn ghost" href="index.php">Batal</a>
      </div>
    </form>
  </div>
</div>
</body>
</html>
