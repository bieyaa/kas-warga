<?php
session_start();
require_once __DIR__.'/config/functions.php';

// Kalau sudah login, arahkan sesuai role
if (isset($_SESSION['role'])) {
  if ($_SESSION['role'] === 'admin') {
    redirect('index.php');
  } elseif ($_SESSION['role'] === 'warga') {
    redirect('warga_dashboard.php');
  }
}

$err  = '';
$info = '';

// Pesan setelah registrasi
if (isset($_GET['registered'])) {
  $info = "Registrasi berhasil. Tunggu validasi admin ya.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');

  if ($username === '' || $password === '') {
    $err = "Username dan password wajib diisi.";
  } else {
    // 1. Cek ADMIN dulu
    $st  = q("SELECT * FROM admin WHERE username=? AND password=?", 'ss', $username, $password);
    $res = $st->get_result();
    if ($row = $res->fetch_assoc()) {
      $_SESSION['role'] = 'admin';
      $_SESSION['nama'] = $row['nama_admin'] ?? 'Admin';
      $_SESSION['id']   = $row['id_admin'] ?? $row['id'] ?? null;
      redirect('index.php');
    }

    // 2. Kalau bukan admin, cek sebagai WARGA
    $st2  = q("SELECT * FROM warga WHERE username=? AND password=?", 'ss', $username, $password);
    $res2 = $st2->get_result();
    if ($row2 = $res2->fetch_assoc()) {
      if ($row2['status'] !== 'aktif') {
        $err = "Akun kamu belum divalidasi oleh admin RT.";
      } else {
        $_SESSION['role'] = 'warga';
        $_SESSION['nama'] = $row2['nama'];
        $_SESSION['id']   = $row2['id'];
        redirect('warga_dashboard.php');
      }
    } else {
      if ($err === '') {
        $err = "Username atau password salah.";
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Login - Kas Warga</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
  /* --- Global Reset --- */
  *{margin:0;padding:0;box-sizing:border-box;font-family:system-ui,-apple-system,"Segoe UI",sans-serif;}

  /* --- Body & Background Poster --- */
  body{
    min-height:100vh;
    background-color: #1f3b73; /* Warna cadangan kalau gambar gagal load */
    display:flex;
    flex-direction:column;
    
    /* GANTI NAMA FILE DISINI JADI bg.jpg BIAR AMAN */
    background-image: url('bg.jpg'); 
    background-size: cover;
    background-position: center;
    background-attachment: fixed; /* Biar gambar diem pas discroll */
    position: relative;
  }
  
  /* Overlay Transparan (DIPERBAIKI) */
  body::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    /* Opacity diturunkan jadi 0.6 dan 0.5 supaya gambar kelihatan */
    background: linear-gradient(135deg, rgba(31, 59, 115, 0.6), rgba(63, 106, 216, 0.5)); 
    z-index: -1; 
  }
  
  /* --- Navbar --- */
  .nav{
    padding:14px 25px; 
    color:#fff;
    display:flex;
    justify-content:space-between;
    align-items:center;
    background-color: rgba(0,0,0,0.3); /* Sedikit lebih gelap */
    backdrop-filter: blur(5px); /* Efek kaca */
    box-shadow: 0 2px 10px rgba(0,0,0,.1);
    position: sticky; top: 0; z-index: 10;
  }
  .brand{font-weight:800; font-size: 1.2rem; display: flex; align-items: center; gap: 8px;}
  .nav a{
    color:#fff; text-decoration:none; font-weight:600; padding: 6px 12px;
    border-radius: 8px; transition: background-color .2s;
  }
  .nav a:hover{background-color: rgba(255,255,255,0.2);}
  
  /* --- Card Login --- */
  .container{
    flex:1; display:flex; justify-content:center; align-items:center; 
    padding:30px 20px; z-index: 1; 
  }
  .card{
    width:100%; max-width:400px; 
    background: rgba(255, 255, 255, 0.95); /* Sedikit transparan biar modern */
    border-radius:18px; padding:30px; 
    box-shadow:0 20px 50px rgba(0,0,0,.3); 
    animation:fade .5s ease-out;
  }
  @keyframes fade{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}
  
  h3{text-align:center; margin-bottom:20px; color: #1f3b73; font-size: 1.6rem; font-weight: 800;}
  
  /* --- Form --- */
  label{display:block; font-size:.9rem; margin-bottom:6px; color:#475569; font-weight: 600;}
  input{
    width:100%; padding:12px 14px; border-radius:12px; border:2px solid #e2e8f0; 
    margin-bottom:16px; font-size:.95rem; outline:none; transition:.2s;
    background-color: #f8fafc;
  }
  input:focus{border-color:#3b82f6; background-color: #fff; box-shadow:0 0 0 4px rgba(59,130,246,.1);}
  
  /* --- Buttons --- */
  .row-btn{display:flex; gap:12px; margin-top:10px;}
  .btn{
    flex:1; border:none; border-radius:12px; padding:12px; cursor:pointer;
    font-weight:700; font-size:1rem; text-align:center; text-decoration: none;
    transition: transform .1s ease, box-shadow .2s;
  }
  .btn:active{transform: scale(0.96);}
  .btn-primary{
    background:linear-gradient(135deg,#2563eb,#1d4ed8); 
    color:#fff; box-shadow: 0 4px 15px rgba(37,99,235,0.4);
  }
  .btn-primary:hover{box-shadow: 0 6px 20px rgba(37,99,235,0.6);}
  .btn-ghost{background:#e2e8f0; color:#334155;}
  .btn-ghost:hover{background:#cbd5e1;}
  
  /* --- Badges --- */
  .badge{
    padding:12px 14px; border-radius:12px; font-size:.9rem; margin-bottom:20px; font-weight: 600; text-align: center;
  }
  .good{background:#dcfce7; border: 1px solid #86efac; color:#15803d;} 
  .bad{background:#fee2e2; border: 1px solid #fca5a5; color:#b91c1c;}
</style>
</head>
<body>
  <div class="nav">
    <div class="brand">🏠 Kas Warga</div>
    <div>
        <a href="login.php">Login</a>
        <a href="register.php">Daftar</a>
    </div>
  </div>

  <div class="container">
    <div class="card">
      <h3>Masuk ke Aplikasi</h3>

      <?php if ($info): ?>
        <div class="badge good"><?=h($info)?></div>
      <?php endif; ?>

      <?php if ($err): ?>
        <div class="badge bad"><?=h($err)?></div>
      <?php endif; ?>

      <form method="post">
        <label>Username</label>
        <input type="text" name="username" placeholder="Masukkan username..." required value="<?=h($_POST['username'] ?? '')?>"> 

        <label>Password</label>
        <input type="password" name="password" placeholder="Masukkan password..." required>

        <div class="row-btn">
          <button class="btn btn-primary" type="submit">Login</button>
          <a class="btn btn-ghost" href="register.php">Daftar</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>