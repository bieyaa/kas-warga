<?php
require_once __DIR__.'/../config/functions.php';
$dari=trim($_GET['dari']??''); $sampai=trim($_GET['sampai']??''); $status=trim($_GET['status']??'');
$sql="SELECT i.*, w.nama FROM iuran i JOIN warga w ON w.id=i.warga_id WHERE 1=1"; $types=''; $params=[];
if($dari!==''){ $sql.=" AND i.bulan >= ?"; $types.='s'; $params[]=$dari; }
if($sampai!==''){ $sql.=" AND i.bulan <= ?"; $types.='s'; $params[]=$sampai; }
if($status!==''){ $sql.=" AND i.status = ?"; $types.='s'; $params[]=$status; }
$sql.=" ORDER BY i.bulan DESC, w.nama ASC"; $res=q($sql,$types,...$params)->get_result();
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=laporan_kas.csv');
$out=fopen('php://output','w'); fputcsv($out,['Warga','Bulan','Nominal','Status','Keterangan','Dibuat']);
while($r=$res->fetch_assoc()){ fputcsv($out,[$r['nama'],$r['bulan'],$r['nominal'],$r['status'],$r['keterangan'],$r['created_at']]); }
fclose($out); exit;
