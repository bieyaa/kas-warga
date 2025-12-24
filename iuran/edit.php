<?php require_once __DIR__.'/../config/functions.php';
$id=(int)($_GET['id']??0);
$r=q("SELECT * FROM iuran WHERE id=?",'i',$id)->get_result()->fetch_assoc();
if(!$r){ http_response_code(404); echo "Data tidak ditemukan"; exit; }
if($_SERVER['REQUEST_METHOD']==='POST'){
  $bulan=trim($_POST['bulan']??''); $nominal=(int)($_POST['nominal']??0);
  $status=($_POST['status']??'BELUM')==='LUNAS'?'LUNAS':'BELUM'; $ket=trim($_POST['keterangan']??'');
  if($bulan===''||$nominal<=0){ $err="Bulan & nominal wajib benar."; }
  if(!isset($err)){
    try{
      q("UPDATE iuran SET bulan=?, nominal=?, status=?, keterangan=? WHERE id=?",'sissi',$bulan,$nominal,$status,$ket,$id);
      redirect('index.php');
    }catch(Throwable $e){ $err="Gagal update. Cek duplikasi warga+bulan."; }
  }
}
$w=q("SELECT id,nama FROM warga WHERE id=?",'i',$r['warga_id'])->get_result()->fetch_assoc();
?>
<!DOCTYPE html><html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Iuran</title><link rel="stylesheet" href="../assets/style.css"></head><body>
<div class="nav"><div class="brand">Kas Warga</div><div class="menu">
<a href="../index.php">Dashboard</a><a href="index.php">Iuran</a></div><div class="burger">☰</div></div>
<div class="container"><div class="card"><h3>Edit Iuran — <?=h($w['nama']??'')?> </h3>
<?php if(isset($err)): ?><div class="badge bad" style="display:inline-block"><?=h($err)?></div><?php endif; ?>
<form method="post" class="mt-2">
<label>Bulan (YYYY-MM)</label><input name="bulan" value="<?=h($r['bulan'])?>" required>
<label>Nominal</label><input type="number" name="nominal" value="<?=h($r['nominal'])?>" min="1" required>
<label>Status</label><select name="status"><option value="BELUM" <?=$r['status']==='BELUM'?'selected':''?>>BELUM</option>
<option value="LUNAS" <?=$r['status']==='LUNAS'?'selected':''?>>LUNAS</option></select>
<label>Keterangan</label><input name="keterangan" value="<?=h($r['keterangan'])?>">
<div class="mt-2" style="display:flex;gap:10px"><button class="btn" type="submit">Simpan</button><a class="btn ghost" href="index.php">Batal</a></div>
</form></div></div></body></html>
