<?php require_once __DIR__.'/../config/functions.php';
$id = (int)($_GET['id']??0);
$r = q("SELECT * FROM pengeluaran WHERE id=?",'i',$id)->get_result()->fetch_assoc();
if(!$r){ http_response_code(404); echo "Data tidak ditemukan"; exit; }

if($_SERVER['REQUEST_METHOD']==='POST'){
  $tanggal = $_POST['tanggal']??'';
  $kategori = trim($_POST['kategori']??'');
  $nominal = (int)($_POST['nominal']??0);
  $ket = trim($_POST['keterangan']??'');
  if($tanggal===''||$kategori===''||$nominal<=0){ $err="Tanggal, kategori, dan nominal wajib diisi."; }
  if(!isset($err)){
    q("UPDATE pengeluaran SET tanggal=?, kategori=?, nominal=?, keterangan=? WHERE id=?",
      'ssisi', $tanggal,$kategori,$nominal,$ket,$id);
    redirect('index.php');
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Pengeluaran</title>
  <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="nav"><div class="brand">Kas Warga</div>
  <div class="menu">
    <a href="../index.php">Dashboard</a><a href="index.php">Pengeluaran</a>
  </div><div class="burger">☰</div>
</div>
<div class="container">
  <div class="card">
    <h3>Edit Pengeluaran</h3>
    <?php if(isset($err)): ?><div class="badge bad" style="display:inline-block"><?=h($err)?></div><?php endif; ?>
    <form method="post" class="mt-2">
      <label>Tanggal</label><input type="date" name="tanggal" value="<?=h($r['tanggal'])?>" required>
      <label>Kategori</label><input name="kategori" value="<?=h($r['kategori'])?>" required>
      <label>Nominal</label><input type="number" name="nominal" value="<?=h($r['nominal'])?>" min="1" required>
      <label>Keterangan</label><input name="keterangan" value="<?=h($r['keterangan'])?>">
      <div class="mt-2" style="display:flex;gap:10px">
        <button class="btn" type="submit">Simpan</button>
        <a class="btn ghost" href="index.php">Batal</a>
      </div>
    </form>
  </div>
</div>
<?php require_once __DIR__.'/../partials/footer.php'; ?>
</body>
</html>
