<?php require_once __DIR__.'/../config/functions.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $nama=trim($_POST['nama']??''); $alamat=trim($_POST['alamat']??''); $no_hp=trim($_POST['no_hp']??'');
  if($nama===''||$alamat===''){ $err="Nama & Alamat wajib diisi."; }
  if(!isset($err)){
    q("INSERT INTO warga(nama,alamat,no_hp) VALUES(?,?,?)",'sss',$nama,$alamat,$no_hp);
    redirect('index.php');
  }
}
?>
<!DOCTYPE html><html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Warga</title><link rel="stylesheet" href="../assets/style.css"></head><body>
<div class="nav"><div class="brand">Kas Warga</div><div class="menu">
<a href="../index.php">Dashboard</a><a href="index.php">Warga</a></div><div class="burger">☰</div></div>
<div class="container"><div class="card"><h3>Tambah Warga</h3>
<?php if(isset($err)): ?><div class="badge bad" style="display:inline-block"><?=h($err)?></div><?php endif; ?>
<form method="post" class="mt-2">
<label>Nama</label><input name="nama" required>
<label>Alamat</label><input name="alamat" required>
<label>No. HP</label><input name="no_hp" placeholder="08xxxxxxxxxx">
<div class="mt-2" style="display:flex;gap:10px">
<button class="btn" type="submit">Simpan</button><a class="btn ghost" href="index.php">Batal</a></div>
</form></div></div></body></html>
