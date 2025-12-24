<?php require_once __DIR__.'/../config/functions.php';
$id=(int)($_GET['id']??0);
q("DELETE FROM warga WHERE id=?",'i',$id);
redirect('index.php');
