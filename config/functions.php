<?php
require_once __DIR__ . '/db.php';
function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function rupiah($n){ return 'Rp ' . number_format((float)$n,0,',','.'); }
function q($sql, $types='', ...$params){
  global $mysqli; $stmt = $mysqli->prepare($sql);
  if(!$stmt){ throw new Exception($mysqli->error); }
  if($types!==''){ $stmt->bind_param($types, ...$params); }
  $stmt->execute(); return $stmt;
}
function get_scalar($sql, $types='', ...$params){
  $st=q($sql,$types,...$params); $res=$st->get_result(); if(!$res) return null;
  $row=$res->fetch_row(); return $row ? $row[0] : null;
}
function month_name($ym){
  [$y,$m]=explode('-', $ym);
  $names=['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
  return $names[(int)$m-1]." ".$y;
}
function redirect($to){ header("Location: $to"); exit; }
