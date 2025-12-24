<?php require_once __DIR__.'/../config/functions.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $warga_id=(int)($_POST['warga_id']??0); $bulan=trim($_POST['bulan']??''); $nominal=(int)($_POST['nominal']??0);
  $status=($_POST['status']??'BELUM')==='LUNAS'?'LUNAS':'BELUM'; $ket=trim($_POST['keterangan']??'');
  if($warga_id<=0||$bulan===''||$nominal<=0){ $err="Semua field wajib diisi dengan benar."; }
  if(!isset($err)){
    try{
      q("INSERT INTO iuran(warga_id,bulan,nominal,status,keterangan) VALUES(?,?,?,?,?)",'isiss',$warga_id,$bulan,$nominal,$status,$ket);
      redirect('index.php');
    }catch(Throwable $e){ $err="Gagal simpan. Pastikan kombinasi warga+bulan belum ada."; }
  }
}
$warga=q("SELECT id,nama FROM warga ORDER BY nama")->get_result();
?>
<!DOCTYPE html><html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Catat Iuran</title><link rel="stylesheet" href="../assets/style.css"></head><body>
<div class="nav"><div class="brand">Kas Warga</div><div class="menu">
<a href="../index.php">Dashboard</a><a href="index.php">Iuran</a></div><div class="burger">☰</div></div>
<div class="container"><div class="card"><h3>Catat Iuran</h3>
<?php if(isset($err)): ?><div class="badge bad" style="display:inline-block"><?=h($err)?></div><?php endif; ?>
<form method="post" class="mt-2">
<label>Warga</label><select name="warga_id" required><option value="">-- pilih --</option>
<?php while($w=$warga->fetch_assoc()): ?><option value="<?=$w['id']?>"><?=h($w['nama'])?></option><?php endwhile; ?></select>
<label>Bulan (YYYY-MM)</label><input name="bulan" placeholder="2025-10" required>
<label>Nominal</label><input type="number" name="nominal" placeholder="100000" min="1" required>
<label>Status</label><select name="status"><option value="BELUM">BELUM</option><option value="LUNAS">LUNAS</option></select>
<label>Keterangan</label><input name="keterangan" placeholder="Tunai / Transfer / Catatan">
<div class="mt-2" style="display:flex;gap:10px"><button class="btn" type="submit">Simpan</button><a class="btn ghost" href="index.php">Batal</a></div>
</form></div></div></body></html>
