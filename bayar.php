<?php
session_start();
require_once __DIR__.'/config/functions.php';

// Proteksi: hanya warga
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'warga') {
  redirect('login.php');
}

$id_warga = $_SESSION['id'];
$nama     = $_SESSION['nama'];
$err      = '';
$success  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bulan_pilih = $_POST['bulan'] ?? ''; 
    $nominal     = (int) ($_POST['nominal'] ?? 0);
    $keterangan  = trim($_POST['keterangan'] ?? '');
    
    // Validasi File Upload
    $file_name   = $_FILES['bukti']['name'] ?? '';
    $file_tmp    = $_FILES['bukti']['tmp_name'] ?? '';
    $file_size   = $_FILES['bukti']['size'] ?? 0;
    $file_ext    = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    if ($bulan_pilih === '' || $nominal <= 0) {
        $err = "Bulan dan Nominal wajib diisi.";
    } elseif ($file_name === '') {
        $err = "Bukti transfer wajib diupload.";
    } elseif (!in_array($file_ext, ['jpg', 'jpeg', 'png'])) {
        $err = "Format file harus JPG, JPEG, atau PNG.";
    } elseif ($file_size > 2000000) { 
        $err = "Ukuran file maksimal 2MB.";
    } else {
        $tgl_bulan = $bulan_pilih . '-01'; 

        // Cek apakah bulan ini sudah pernah dibayar?
        $cek = q("SELECT id FROM iuran WHERE warga_id=? AND bulan=?", 'is', $id_warga, $tgl_bulan);
        if ($cek->get_result()->num_rows > 0) {
            $err = "Iuran untuk bulan tersebut sudah pernah diajukan/dibayar.";
        } else {
            // Rename file biar unik
            $new_name = time() . '_' . $id_warga . '.' . $file_ext;
            $destination = __DIR__ . '/assets/uploads/' . $new_name;

            if (move_uploaded_file($file_tmp, $destination)) {
                // Simpan ke database dengan status MENUNGGU
                q("INSERT INTO iuran (warga_id, bulan, nominal, keterangan, status, bukti_transfer) 
                   VALUES (?, ?, ?, ?, 'MENUNGGU', ?)",
                   'isiss',
                   $id_warga, $tgl_bulan, $nominal, $keterangan, $new_name
                );
                $success = "Bukti berhasil dikirim! Menunggu konfirmasi Admin.";
            } else {
                $err = "Gagal mengupload gambar. Pastikan folder assets/uploads ada.";
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
<title>Bayar Iuran - Kas Warga</title>
<link rel="stylesheet" href="assets/style.css">
<style>
    /* --- NAVBAR STYLE --- */
    .nav {
        background-color: #1f3b73; padding: 14px 25px; display: flex; justify-content: space-between; align-items: center;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1); color: #fff; position: sticky; top: 0; z-index: 100;
    }
    
    /* Style Tombol Menu Biasa */
    .nav .menu a {
        color: #cbd5e1; text-decoration: none; padding: 8px 20px; border-radius: 50px; font-weight: 600; font-size: 0.95rem;
        transition: all 0.3s ease; border: 2px solid transparent;
    }
    .nav .menu a:hover {
        background-color: #facc15; color: #1f3b73; box-shadow: 0 0 15px rgba(250, 204, 21, 0.5); transform: translateY(-3px);
    }

    /* --- FIX STYLE TOMBOL BAYAR --- */
    /* Kondisi Diam (Outline Kuning, Teks Kuning) */
    .nav .menu a.btn-bayar { 
        border: 2px solid #facc15; 
        color: #facc15; 
    }

    /* Kondisi Hover ATAU Active (Background Kuning, TEKS HARUS NAVY) */
    .nav .menu a.btn-bayar:hover,
    .nav .menu a.btn-bayar.active { 
        background-color: #facc15; 
        color: #1f3b73 !important; /* Paksa jadi Navy biar kebaca */
        box-shadow: 0 0 20px rgba(250, 204, 21, 0.7); 
        font-weight: 800;
    }

    /* --- FORM STYLE --- */
    .payment-card {
        background: #fff; border-radius: 16px; padding: 30px; max-width: 600px; margin: 20px auto;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .bank-info {
        background: linear-gradient(135deg, #1f3b73, #2563eb); color: #fff; padding: 20px;
        border-radius: 12px; margin-bottom: 25px; text-align: center;
    }
    .bank-info h4 { margin-bottom: 5px; font-weight: 400; opacity: 0.9; }
    .bank-info .norek { font-size: 1.5rem; font-weight: 800; letter-spacing: 1px; margin-bottom: 10px; }
    .bank-info .copy-btn {
        background: rgba(255,255,255,0.2); border: none; color: #fff; padding: 5px 12px;
        border-radius: 20px; cursor: pointer; font-size: 0.85rem; transition: .2s;
    }
    .bank-info .copy-btn:hover { background: #fff; color: #1f3b73; }

    label { display: block; font-weight: 600; color: #334155; margin-bottom: 8px; margin-top: 15px; }
    input[type="text"], input[type="number"], input[type="month"], textarea {
        width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 1rem; transition: .2s;
    }
    input:focus, textarea:focus { border-color: #3b82f6; outline: none; }
    
    .file-upload-wrapper {
        border: 2px dashed #cbd5e1; border-radius: 10px; padding: 20px; text-align: center; cursor: pointer; transition: .2s;
        background: #f8fafc;
    }
    .file-upload-wrapper:hover { border-color: #3b82f6; background: #eff6ff; }
    
    .btn-submit {
        width: 100%; background: #facc15; color: #1f3b73; font-weight: 800; border: none; padding: 15px;
        border-radius: 12px; font-size: 1.1rem; margin-top: 25px; cursor: pointer; transition: .2s;
    }
    .btn-submit:hover { background: #eab308; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(234, 179, 8, 0.3); }
    
    .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; }
    .alert-err { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
    .alert-ok { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
</style>
</head>
<body>

<div class="nav">
  <div class="brand">✨ Kas Warga</div>
  <div class="menu">
    <a href="warga_dashboard.php">Dashboard</a>
    <a class="active btn-bayar" href="bayar.php">💸 Bayar Iuran</a>
    <a href="logout.php">Logout</a>
  </div>
  <div class="burger">☰</div>
</div>

<div class="container">
    
    <h2 style="text-align:center; color:#1f3b73; margin-top:20px; margin-bottom:10px;">Form Pembayaran Iuran</h2>
    <p style="text-align:center; color:#64748b; margin-bottom:30px;">Silakan transfer lalu upload buktinya di sini.</p>

    <div class="payment-card">
        
        <?php if ($err): ?>
            <div class="alert alert-err"><?= $err ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-ok">
                <?= $success ?>
                <br><a href="warga_dashboard.php" style="font-size:0.9rem; text-decoration:none; color:#15803d; font-weight:800;">&laquo; Kembali ke Dashboard</a>
            </div>
        <?php endif; ?>

        <div class="bank-info">
            <h4>Transfer ke Bank BCA</h4>
            <div class="norek" id="textNorek">123 456 7890</div>
            <div>a.n. Kas Warga RT 05</div>
            <button class="copy-btn mt-2" onclick="copyNorek()">Salin Nomor Rekening</button>
        </div>

        <form method="post" enctype="multipart/form-data">
            
            <label>Bayar untuk Bulan</label>
            <input type="month" name="bulan" required value="<?= date('Y-m') ?>">
            <small style="color:#64748b;">Pilih bulan dan tahun iuran.</small>

            <label>Nominal Transfer (Rp)</label>
            <input type="number" name="nominal" placeholder="Contoh: 50000" required>

            <label>Bukti Transfer (Foto/Screenshot)</label>
            <div class="file-upload-wrapper" onclick="document.getElementById('fileInput').click()">
                <span id="fileName">Klik untuk pilih gambar (Max 2MB)</span>
                <input type="file" name="bukti" id="fileInput" style="display:none" accept="image/*" onchange="updateFileName(this)">
            </div>

            <label>Catatan Tambahan (Opsional)</label>
            <textarea name="keterangan" rows="2" placeholder="Contoh: Bayar sekalian buat bulan depan..."></textarea>

            <button type="submit" class="btn-submit">Kirim Bukti Pembayaran 🚀</button>
        </form>
    </div>

</div>

<script>
    function copyNorek() {
        navigator.clipboard.writeText("1234567890");
        alert("Nomor rekening berhasil disalin!");
    }

    function updateFileName(input) {
        if (input.files && input.files[0]) {
            document.getElementById('fileName').innerText = "✅ " + input.files[0].name;
            document.getElementById('fileName').style.color = "#15803d";
            document.getElementById('fileName').style.fontWeight = "bold";
        }
    }
</script>

</body>
</html>