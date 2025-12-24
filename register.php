<?php
session_start();
require_once __DIR__.'/config/functions.php';

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $alamat   = trim($_POST['alamat'] ?? '');
    $no_hp    = trim($_POST['no_hp'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validasi dasar
    if ($nama === '' || $alamat === '' || $username === '' || $password === '') {
        $err = "Nama, Alamat, Username, dan Password wajib diisi.";
    } else {
        // Cek username kembar
        $st   = q("SELECT id FROM warga WHERE username=?",'s',$username);
        $ada  = $st->get_result()->fetch_assoc();

        if ($ada) {
            $err = "Username sudah digunakan. Silakan pilih username lain.";
        } else {
            // Masukkan data (status default: pending)
            q(
                "INSERT INTO warga (nama, alamat, no_hp, username, password, status) 
                 VALUES (?,?,?,?,?,'pending')",
                'sssss',
                $nama, $alamat, $no_hp, $username, $password
            );
            // Redirect ke login dengan pesan sukses
            redirect('login.php?registered=1');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Daftar Warga - Kas Warga</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
  /* --- Global Reset --- */
  *{margin:0;padding:0;box-sizing:border-box;font-family:system-ui,-apple-system,"Segoe UI",sans-serif;}

  /* --- Body & Background Poster --- */
  body{
    min-height:100vh;
    background-color: #1f3b73; 
    display:flex;
    flex-direction:column;
    
    /* Pastikan file ini bernama bg.jpg (Sama kayak Login) */
    background-image: url('bg.jpg'); 
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    position: relative;
  }
  
  /* Overlay Transparan (Sama kayak Login) */
  body::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
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
    background-color: rgba(0,0,0,0.3);
    backdrop-filter: blur(5px);
    box-shadow: 0 2px 10px rgba(0,0,0,.1);
    position: sticky; top: 0; z-index: 10;
  }
  .brand{font-weight:800; font-size: 1.2rem; display: flex; align-items: center; gap: 8px;}
  .nav a{
    color:#fff; text-decoration:none; font-weight:600; padding: 6px 12px;
    border-radius: 8px; transition: background-color .2s;
  }
  .nav a:hover{background-color: rgba(255,255,255,0.2);}
  
  /* --- Card Register --- */
  .container{
    flex:1; display:flex; justify-content:center; align-items:center; 
    padding:40px 20px; z-index: 1; 
  }
  .card{
    width:100%; max-width:450px; /* Sedikit lebih lebar dari login */
    background: rgba(255, 255, 255, 0.95);
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
      <h3>Daftar Akun Baru</h3>

      <?php if ($err): ?>
        <div class="badge bad"><?=h($err)?></div>
      <?php endif; ?>

      <form method="post">
        <label>Nama Lengkap</label>
        <input type="text" name="nama" required value="<?=h($_POST['nama'] ?? '')?>" placeholder="Nama lengkap kamu">

        <label>Alamat Rumah</label>
        <input type="text" name="alamat" required value="<?=h($_POST['alamat'] ?? '')?>" placeholder="Contoh: Blok A No. 12">

        <label>No. HP (WhatsApp)</label>
        <input type="text" name="no_hp" value="<?=h($_POST['no_hp'] ?? '')?>" placeholder="08xxxxxxxxxx">

        <label>Username</label>
        <input type="text" name="username" required value="<?=h($_POST['username'] ?? '')?>" placeholder="Buat username unik">

        <label>Password</label>
        <input type="password" name="password" required placeholder="Buat password aman">

        <div class="row-btn">
          <button class="btn btn-primary" type="submit">Daftar Sekarang</button>
          <a class="btn btn-ghost" href="login.php">Batal</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>