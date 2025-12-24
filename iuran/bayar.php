<?php
session_start();
require_once __DIR__ . '/config/functions.php';

// Proteksi: hanya warga
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'warga') {
  redirect('login.php');
}

$id_warga = (int)($_SESSION['id'] ?? 0);
$nama     = $_SESSION['nama'] ?? 'Warga';

$err = '';
$ok  = '';

// Ambil iuran yang belum lunas (buat dropdown)
$resBelum = q(
  "SELECT id, bulan, nominal, status 
   FROM iuran 
   WHERE warga_id=? AND status='BELUM' 
   ORDER BY bulan ASC",
  'i',
  $id_warga
)->get_result();

$opsiBelum = [];
while ($r = $resBelum->fetch_assoc()) {
  $opsiBelum[] = $r;
}

// Handle submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $id_iuran = (int)($_POST['id_iuran'] ?? 0);

  // validasi: pastiin iuran itu punya warga & status BELUM
  $cek = q(
    "SELECT id, bulan, nominal, status 
     FROM iuran 
     WHERE id=? AND warga_id=? AND status='BELUM' 
     LIMIT 1",
    'ii',
    $id_iuran,
    $id_warga
  )->get_result();

  $iuran = $cek->fetch_assoc();

  if (!$iuran) {
    $err = "Iuran yang dipilih tidak valid / sudah diproses.";
  } else {

    // ===== FOLDER UPLOAD (KEBAL WINDOWS) =====
    $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;

    if (!is_dir($uploadDir)) {
      if (!mkdir($uploadDir, 0777, true)) {
        $err = "Gagal bikin folder uploads. Coba jalankan XAMPP as Administrator. Path: " . $uploadDir;
      }
    }

    if (!$err && !is_writable($uploadDir)) {
      $err = "Folder uploads tidak bisa ditulis (permission). Path: " . $uploadDir;
    }

    // ===== VALIDASI FILE =====
    if (!$err) {
      if (!isset($_FILES['bukti']) || $_FILES['bukti']['error'] !== UPLOAD_ERR_OK) {
        $err = "Bukti transfer wajib diupload.";
      } else {

        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
          $err = "Format bukti harus JPG/JPEG/PNG/WEBP.";
        } elseif ((int)$_FILES['bukti']['size'] > 2 * 1024 * 1024) {
          $err = "Ukuran file maksimal 2MB.";
        } else {

          $newName = time() . '_' . rand(1000, 9999) . '.' . $ext;
          $target  = $uploadDir . $newName;

          if (!move_uploaded_file($_FILES['bukti']['tmp_name'], $target)) {
            $err = "Gagal memindahkan file. Target: " . $target;
          } else {

            // PATH WEB untuk DB
            $buktiPath = 'assets/uploads/' . $newName;

            // ✅ UPDATE DB: pakai kolom yang bener (bukti_transfer)
            q(
              "UPDATE iuran SET status='MENUNGGU', bukti_transfer=? WHERE id=? AND warga_id=?",
              'sii',
              $buktiPath,
              $id_iuran,
              $id_warga
            );

            $ok = "Upload berhasil! Bukti kamu sudah terkirim dan menunggu verifikasi RT.";

            // refresh dropdown
            $resBelum = q(
              "SELECT id, bulan, nominal, status 
               FROM iuran 
               WHERE warga_id=? AND status='BELUM' 
               ORDER BY bulan ASC",
              'i',
              $id_warga
            )->get_result();

            $opsiBelum = [];
            while ($r = $resBelum->fetch_assoc()) {
              $opsiBelum[] = $r;
            }
          }
        }
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bayar Iuran - Kas RT</title>
<link rel="stylesheet" href="assets/style.css">
<style>
/* NAVBAR SAMA PERSIS KAYAK DASHBOARD */
.nav {
  background-color: #1f3b73;
  padding: 14px 25px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 4px 20px rgba(0,0,0,0.1);
  color: #fff;
  position: sticky; top: 0; z-index: 100;
}
.nav .menu a {
  color: #cbd5e1;
  text-decoration: none;
  padding: 8px 20px;
  border-radius: 50px;
  font-weight: 600;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  border: 2px solid transparent;
}
.nav .menu a:hover,
.nav .menu a.active {
  background-color: #facc15;
  color: #1f3b73;
  box-shadow: 0 0 15px rgba(250, 204, 21, 0.5);
  transform: translateY(-3px);
}
.nav .menu a.btn-bayar {
  border: 2px solid #facc15;
  color: #facc15;
}
.nav .menu a.btn-bayar:hover {
  background-color: #facc15;
  color: #1f3b73;
  box-shadow: 0 0 20px rgba(250, 204, 21, 0.7);
}
.form-row { display:flex; gap:12px; flex-wrap:wrap; }
.form-row > div { flex:1; min-width: 220px; }
.input, select.input {
  width:100%;
  padding:12px 14px;
  border:1px solid #e2e8f0;
  border-radius: 12px;
  outline: none;
}
.btn {
  padding: 12px 16px;
  border: none;
  border-radius: 12px;
  font-weight: 700;
  cursor: pointer;
}
.btn-primary { background:#1f3b73; color:#fff; }
.btn-primary:hover { filter: brightness(1.05); }
.note { font-size: 0.9rem; color:#64748b; }
.alert {
  padding: 12px 14px;
  border-radius: 12px;
  margin-bottom: 14px;
  font-weight: 600;
}
.alert.err { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
.alert.ok  { background:#dcfce7; color:#166534; border:1px solid #bbf7d0; }
</style>
</head>
<body>

<div class="nav">
  <div class="brand">✨ Kas Warga</div>
  <div class="menu">
    <a href="warga_dashboard.php">Dashboard</a>
    <a class="btn-bayar active" href="bayar.php">💸 Bayar Iuran</a>
    <a href="logout.php">Logout</a>
  </div>
  <div class="burger">☰</div>
</div>

<div class="container">

  <div class="hero">
    <div class="hero-title">Form Pembayaran Iuran</div>
    <div class="hero-sub">Halo, <?=h($nama)?> 👋 Upload bukti transfer biar RT bisa verifikasi.</div>
  </div>

  <div class="card mt-3" style="max-width: 760px; margin: 0 auto;">
    <?php if($err): ?><div class="alert err"><?= h($err) ?></div><?php endif; ?>
    <?php if($ok):  ?><div class="alert ok"><?= h($ok) ?></div><?php endif; ?>

    <div class="card" style="background: linear-gradient(180deg,#1f3b73,#224a92); color:#fff; border:none;">
      <div style="text-align:center; font-weight:700; opacity:.9;">Transfer ke Bank BCA</div>
      <div style="text-align:center; font-size: 28px; font-weight: 800; margin-top:8px;">123 456 7890</div>
      <div style="text-align:center; margin-top:6px; opacity:.9;">a.n. Kas Warga RT 05</div>
      <div style="text-align:center; margin-top:12px;">
        <button type="button" class="btn" onclick="navigator.clipboard.writeText('1234567890')">Salin Nomor Rekening</button>
      </div>
    </div>

    <div style="height:12px;"></div>

    <?php if(count($opsiBelum) === 0): ?>
      <div class="empty">Kamu tidak punya iuran yang berstatus <b>BELUM</b> 🎉</div>
      <div class="note" style="margin-top:10px;">Kalau kamu sudah upload tapi belum lunas, kemungkinan statusnya sudah <b>MENUNGGU</b> (menunggu verifikasi RT).</div>
    <?php else: ?>
      <form method="POST" enctype="multipart/form-data">
        <div class="form-row">
          <div>
            <label class="small">Pilih Bulan Iuran (Status: BELUM)</label>
            <select name="id_iuran" class="input" required>
              <option value="">-- pilih --</option>
              <?php foreach($opsiBelum as $b): ?>
                <option value="<?= (int)$b['id'] ?>">
                  <?= h($b['bulan']) ?> • <?= rupiah((int)$b['nominal']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <div class="note" style="margin-top:6px;">Pilih yang mau dibayar dulu ya.</div>
          </div>

          <div>
            <label class="small">Upload Bukti Transfer</label>
            <input type="file" name="bukti" class="input" accept="image/*" required>
            <div class="note" style="margin-top:6px;">JPG/PNG/WEBP • max 2MB.</div>
          </div>
        </div>

        <div style="display:flex; gap:10px; margin-top:14px;">
          <button class="btn btn-primary" type="submit">Kirim Bukti</button>
          <a class="btn" href="warga_dashboard.php" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">Kembali</a>
        </div>
      </form>
    <?php endif; ?>
  </div>

</div>

<?php require_once __DIR__.'/partials/footer.php'; ?>
</body>
</html>
