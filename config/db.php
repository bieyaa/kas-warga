<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'kas_warga');

$mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_errno) {
  http_response_code(500);
  echo "<h3>Gagal konek database.</h3>";
  echo "<p>Detail: " . htmlspecialchars($mysqli->connect_error) . "</p>";
  exit;
}
$mysqli->set_charset('utf8mb4');
