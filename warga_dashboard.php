<?php
session_start();
require_once __DIR__.'/config/functions.php';

// Proteksi: hanya warga
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'warga') {
  redirect('login.php');
}

$id_warga = $_SESSION['id'];
$nama     = $_SESSION['nama'] ?? 'Warga';

// ==================
// DATA PRIBADI & KAS
// ==================
$belum_lunas   = (int) get_scalar("SELECT COUNT(*) FROM iuran WHERE warga_id=? AND status='BELUM'", 'i', $id_warga);
$lunas_pribadi = (int) get_scalar("SELECT COUNT(*) FROM iuran WHERE warga_id=? AND status='LUNAS'", 'i', $id_warga);

$total_lunas = (int) get_scalar("SELECT COALESCE(SUM(nominal),0) FROM iuran WHERE status='LUNAS'");
$total_out   = (int) get_scalar("SELECT COALESCE(SUM(nominal),0) FROM pengeluaran");
$saldo       = $total_lunas - $total_out;

// ==================
// DATA CHART
// ==================
$tahun_ini = date('Y');
$bulan_ini = date('Y-m');

$bulan_labels = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
$kas_masuk    = [];
$kas_keluar   = [];

for ($i = 1; $i <= 12; $i++) {
    $ym = sprintf('%s-%02d', $tahun_ini, $i);
    $kas_masuk[] = (int) get_scalar("SELECT COALESCE(SUM(nominal),0) FROM iuran WHERE status='LUNAS' AND bulan=?", 's', $ym);
    $kas_keluar[] = (int) get_scalar("SELECT COALESCE(SUM(nominal),0) FROM pengeluaran WHERE DATE_FORMAT(tanggal,'%Y-%m')=?", 's', $ym);
}

// Pie Chart Data
$pie_labels = [];
$pie_data   = [];
$stPie = q("SELECT kategori, COALESCE(SUM(nominal),0) as total FROM pengeluaran WHERE DATE_FORMAT(tanggal,'%Y-%m')=? GROUP BY kategori", 's', $bulan_ini);
$resPie = $stPie->get_result();
while($row = $resPie->fetch_assoc()){
    $pie_labels[] = $row['kategori'];
    $pie_data[]   = (int)$row['total'];
}
$pie_has_data = count($pie_data) > 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Warga - Kas RT</title>
<link rel="stylesheet" href="assets/style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script defer src="assets/app.js"></script>
<style>
    /* --- NAVBAR UTAMA --- */
    .nav {
        background-color: #1f3b73; /* Navy Polos */
        padding: 14px 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        color: #fff;
        position: sticky; top: 0; z-index: 100;
    }

    /* --- TOMBOL MENU (DEFAULT / DIAM) --- */
    .nav .menu a {
        color: #cbd5e1; /* Putih agak abu biar soft */
        text-decoration: none;
        padding: 8px 20px;
        border-radius: 50px; /* Bentuk kapsul */
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.3s ease; /* Animasi transisi halus */
        border: 2px solid transparent; /* Siapin border biar ga loncat pas hover */
    }

    /* --- TOMBOL MENU (HOVER / AKTIF) - KUNING AJA! --- */
    .nav .menu a:hover,
    .nav .menu a.active {
        background-color: #facc15; /* KUNING SOLID */
        color: #1f3b73; /* Teks jadi Navy biar kontras & kebaca */
        
        /* Efek Glow Kuning dikit */
        box-shadow: 0 0 15px rgba(250, 204, 21, 0.5); 
        
        /* Animasi naik dikit */
        transform: translateY(-3px);
    }

    /* --- TOMBOL BAYAR (SPESIAL) --- */
    .nav .menu a.btn-bayar {
        border: 2px solid #facc15; /* Garis kuning */
        color: #facc15;
    }
    
    /* Pas hover tombol bayar */
    .nav .menu a.btn-bayar:hover {
        background-color: #facc15;
        color: #1f3b73;
        box-shadow: 0 0 20px rgba(250, 204, 21, 0.7); /* Glow lebih terang */
    }

    /* Style Chart */
    .chart-container { position: relative; height: 300px; width: 100%; }
    .chart-pie-container { position: relative; height: 250px; width: 100%; display: flex; justify-content: center; }
</style>
</head>
<body>

<div class="nav">
  <div class="brand">✨ Kas Warga</div>
  <div class="menu">
    <a class="active" href="warga_dashboard.php">Dashboard</a>
    
    <a class="btn-bayar" href="bayar.php">💸 Bayar Iuran</a>
    
    <a href="logout.php">Logout</a>
  </div>
  <div class="burger">☰</div>
</div>

<div class="container">

  <div class="hero">
    <div class="hero-title">Halo, <?=h($nama)?> 👋</div>
    <div class="hero-sub">Lihat status iuranmu & transparansi keuangan RT kita.</div>
  </div>

  <div class="row">
    <div class="col-4">
      <div class="card">
        <h3>Status Kamu</h3>
        <p class="small">Tunggakan Iuran</p>
        <hr>
        <?php if($belum_lunas > 0): ?>
            <h2 style="color: #ef4444;"><?= $belum_lunas ?> <span style="font-size:1rem; color:#64748b;">Bulan Belum Lunas</span></h2>
            <a href="bayar.php" style="color:#ef4444; font-weight:bold; font-size:0.9rem;">Bayar Sekarang &raquo;</a>
        <?php else: ?>
            <h2 style="color: #22c55e;">Aman ✨</h2>
            <p style="font-size:0.9rem;">Terima kasih sudah tertib!</p>
        <?php endif; ?>
      </div>
    </div>

    <div class="col-4">
      <div class="card">
        <h3>Saldo Kas RT</h3>
        <p class="small">Dana Tersedia Saat Ini</p>
        <hr>
        <h2 style="color: #1f3b73;"><?= rupiah($saldo) ?></h2>
      </div>
    </div>

    <div class="col-4">
      <div class="card">
        <h3>Total Pengeluaran</h3>
        <p class="small">Dana Keluar (All Time)</p>
        <hr>
        <h2 style="color: #f59e0b;"><?= rupiah($total_out) ?></h2>
      </div>
    </div>
  </div>

  <div class="row mt-3">
      <div class="col-8">
          <div class="card">
              <h3>📊 Arus Kas RT (Tahun <?= $tahun_ini ?>)</h3>
              <p class="small">Pemasukan vs Pengeluaran per Bulan</p>
              <div class="chart-container">
                  <canvas id="barChart"></canvas>
              </div>
          </div>
      </div>

      <div class="col-4">
          <div class="card" style="min-height: 100%;">
              <h3>🍩 Pengeluaran Bulan Ini</h3>
              <p class="small">Alokasi Dana (<?= date('F Y') ?>)</p>
              <?php if($pie_has_data): ?>
                  <div class="chart-pie-container">
                      <canvas id="pieChart"></canvas>
                  </div>
              <?php else: ?>
                  <div class="empty" style="margin-top: 50px;">Belum ada pengeluaran bulan ini.</div>
              <?php endif; ?>
          </div>
      </div>
  </div>

  <div class="card mt-3">
    <h3>Detail Pengeluaran Terbaru</h3>
    <?php
      $resx = q("SELECT * FROM pengeluaran ORDER BY tanggal DESC, id DESC LIMIT 5")->get_result();
      if($resx->num_rows===0): ?>
        <div class="empty">Belum ada pengeluaran tercatat.</div>
    <?php else: ?>
    <table class="table mt-1">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>Kategori</th>
          <th>Nominal</th>
          <th>Keterangan</th>
        </tr>
      </thead>
      <tbody>
        <?php while($x=$resx->fetch_assoc()): ?>
        <tr>
          <td><?=h($x['tanggal'])?></td>
          <td><span class="badge info"><?=h($x['kategori'])?></span></td>
          <td><?=rupiah($x['nominal'])?></td>
          <td><?=h($x['keterangan']??'-')?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

</div>

<?php require_once __DIR__.'/partials/footer.php'; ?>

<script>
    // 1. BAR CHART
    const ctxBar = document.getElementById('barChart');
    if(ctxBar){
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: <?= json_encode($bulan_labels) ?>,
                datasets: [
                    {
                        label: 'Pemasukan',
                        data: <?= json_encode($kas_masuk) ?>,
                        backgroundColor: '#22c55e',
                        borderRadius: 4
                    },
                    {
                        label: 'Pengeluaran',
                        data: <?= json_encode($kas_keluar) ?>,
                        backgroundColor: '#ef4444',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // 2. DOUGHNUT CHART
    <?php if($pie_has_data): ?>
    const ctxPie = document.getElementById('pieChart');
    if(ctxPie){
        new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($pie_labels) ?>,
                datasets: [{
                    data: <?= json_encode($pie_data) ?>,
                    backgroundColor: ['#3b82f6', '#facc15', '#ec4899', '#f97316', '#a855f7', '#06b6d4'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, usePointStyle: true } } },
                cutout: '60%'
            }
        });
    }
    <?php endif; ?>
</script>

</body>
</html>