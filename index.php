<?php
session_start();
require_once __DIR__.'/config/functions.php';

// Proteksi: Hanya ADMIN
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  redirect('login.php');
}

// 1. LOGIKA TERIMA / TOLAK (Action)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_iuran'])) {
    $id_iuran = (int) $_POST['id_iuran'];
    $action   = $_POST['action_iuran'];
    
    if ($action === 'terima') {
        q("UPDATE iuran SET status='LUNAS' WHERE id=?", 'i', $id_iuran);
    } elseif ($action === 'tolak') {
        q("UPDATE iuran SET status='BELUM' WHERE id=?", 'i', $id_iuran);
    }
    redirect('index.php');
}

// 2. DATA UTAMA
$total_warga   = (int) get_scalar("SELECT COUNT(*) FROM warga WHERE status='aktif'");
$pending_count = (int) get_scalar("SELECT COUNT(*) FROM iuran WHERE status='MENUNGGU'");
$total_kas     = (int) get_scalar("SELECT COALESCE(SUM(nominal),0) FROM iuran WHERE status='LUNAS'");
$total_out     = (int) get_scalar("SELECT COALESCE(SUM(nominal),0) FROM pengeluaran");
$saldo         = $total_kas - $total_out;

// 3. DATA CHART
$tahun_ini = date('Y');
$bulan_ini = date('Y-m');

// A. Bar Chart (Tahunan)
$bulan_labels = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
$kas_masuk    = [];
$kas_keluar   = [];

for ($i = 1; $i <= 12; $i++) {
    $ym = sprintf('%s-%02d', $tahun_ini, $i);
    $kas_masuk[]  = (int) get_scalar("SELECT COALESCE(SUM(nominal),0) FROM iuran WHERE status='LUNAS' AND bulan=?", 's', $ym);
    $kas_keluar[] = (int) get_scalar("SELECT COALESCE(SUM(nominal),0) FROM pengeluaran WHERE DATE_FORMAT(tanggal,'%Y-%m')=?", 's', $ym);
}

// B. Pie/Doughnut Chart (Pengeluaran Kategori Bulan Ini)
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
<title>Dashboard Admin - Kas Warga</title>
<link rel="stylesheet" href="assets/style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script defer src="assets/app.js"></script>
<style>
    .alert-pending {
        background-color:#fffbeb; border:1px solid #fcd34d; color:#92400e;
        padding:15px; border-radius:8px; margin-bottom:20px;
        display:flex; justify-content:space-between; align-items:center;
    }
    .img-thumb {
        width:60px; height:60px; object-fit:cover; border-radius:4px;
        border:1px solid #ddd; cursor:pointer; transition:.2s;
    }
    .img-thumb:hover { transform:scale(1.5); box-shadow:0 4px 10px rgba(0,0,0,0.2); }
    .btn-mini { padding:5px 10px; border-radius:4px; border:none; cursor:pointer; font-size:0.8rem; font-weight:bold; }
    .btn-acc { background:#22c55e; color:white; }
    .btn-rej { background:#ef4444; color:white; }
    .chart-box { position:relative; height:300px; width:100%; }
    .pie-box   { position:relative; height:250px; width:100%; display:flex; justify-content:center; }
</style>
</head>
<body class="has-sidebar">

<div class="layout">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-brand">Kas Warga</div>
    <nav class="sidebar-menu">
      <a href="index.php" class="active">Dashboard</a>
      <a href="warga/index.php">Data Warga</a>
      <a href="iuran/index.php">Data Iuran</a>
      <a href="pengeluaran/index.php">Pengeluaran</a>
      <a href="laporan/index.php">Laporan</a>
      <a href="logout.php">Logout</a>
    </nav>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="main">

    <div class="hero">
      <div class="hero-title">Dashboard Admin</div>
      <div class="hero-sub">Kelola data kas warga RT secara terpusat.</div>
    </div>

    <?php if ($pending_count > 0): ?>
      <div class="alert-pending">
        <div><strong>⚠️ Ada <?= $pending_count ?> pembayaran menunggu konfirmasi!</strong></div>
      </div>

      <div class="card mb-3" style="border:1px solid #fcd34d;">
        <h3>⏳ Konfirmasi Pembayaran</h3>
        <table class="table mt-1">
          <thead>
            <tr><th>Warga</th><th>Bulan</th><th>Nominal</th><th>Bukti</th><th>Aksi</th></tr>
          </thead>
          <tbody>
            <?php
            $res_pending = q("SELECT i.*, w.nama FROM iuran i JOIN warga w ON i.warga_id = w.id WHERE i.status='MENUNGGU' ORDER BY i.created_at ASC")->get_result();
            while($row = $res_pending->fetch_assoc()):
                $img_url = 'assets/uploads/' . $row['bukti_transfer'];
            ?>
            <tr>
              <td><?= h($row['nama']) ?></td>
              <td><?= h(month_name($row['bulan'])) ?></td>
              <td><?= rupiah($row['nominal']) ?></td>
              <td>
                <?php if($row['bukti_transfer']): ?>
                  <a href="<?= $img_url ?>" target="_blank"><img src="<?= $img_url ?>" class="img-thumb"></a>
                <?php else: ?>
                  <span style="color:red;">No Image</span>
                <?php endif; ?>
              </td>
              <td>
                <form method="post" style="display:inline-block;">
                  <input type="hidden" name="id_iuran" value="<?= $row['id'] ?>">
                  <button type="submit" name="action_iuran" value="terima" class="btn-mini btn-acc" onclick="return confirm('Terima pembayaran ini?')">✔</button>
                  <button type="submit" name="action_iuran" value="tolak" class="btn-mini btn-rej" onclick="return confirm('Tolak pembayaran ini?')">✖</button>
                </form>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <div class="row">
      <div class="col-4">
        <div class="card">
          <h3>Total Warga</h3>
          <p class="small">Status Aktif</p>
          <hr>
          <h2><?= $total_warga ?></h2>
        </div>
      </div>
      <div class="col-4">
        <div class="card">
          <h3>Saldo Kas</h3>
          <p class="small">Dana Tersedia</p>
          <hr>
          <h2 style="color:#1f3b73;"><?= rupiah($saldo) ?></h2>
        </div>
      </div>
      <div class="col-4">
        <div class="card">
          <h3>Pengeluaran</h3>
          <p class="small">Total Keluar</p>
          <hr>
          <h2 style="color:#ef4444;"><?= rupiah($total_out) ?></h2>
        </div>
      </div>
    </div>

    <div class="row mt-3">
      <div class="col-8">
        <div class="card">
          <h3>Grafik Arus Kas (<?= $tahun_ini ?>)</h3>
          <div class="chart-box">
            <canvas id="adminBarChart"></canvas>
          </div>
        </div>
      </div>
      <div class="col-4">
        <div class="card" style="min-height:100%;">
          <h3>Pengeluaran Bulan Ini</h3>
          <p class="small" style="text-align:center;margin-bottom:10px;"><?= date('F Y') ?></p>
          <?php if($pie_has_data): ?>
            <div class="pie-box">
              <canvas id="adminPieChart"></canvas>
            </div>
          <?php else: ?>
            <div class="empty" style="margin-top:50px;">Belum ada pengeluaran.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="card mt-3">
      <h3>Pengeluaran Terbaru</h3>
      <?php $resx = q("SELECT * FROM pengeluaran ORDER BY tanggal DESC, id DESC LIMIT 5")->get_result(); ?>
      <table class="table mt-1">
        <thead><tr><th>Tanggal</th><th>Kategori</th><th>Nominal</th><th>Ket</th></tr></thead>
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
    </div>

  </main>
</div>

<?php require_once __DIR__.'/partials/footer.php'; ?>

<script>
  const ctxBar = document.getElementById('adminBarChart');
  if(ctxBar){
    new Chart(ctxBar, {
      type: 'bar',
      data: {
        labels: <?= json_encode($bulan_labels) ?>,
        datasets: [
          { label: 'Masuk',  data: <?= json_encode($kas_masuk) ?>,  backgroundColor: '#22c55e' },
          { label: 'Keluar', data: <?= json_encode($kas_keluar) ?>, backgroundColor: '#ef4444' }
        ]
      },
      options: { responsive:true, maintainAspectRatio:false }
    });
  }

  <?php if($pie_has_data): ?>
  const ctxPie = document.getElementById('adminPieChart');
  if(ctxPie){
    new Chart(ctxPie, {
      type: 'doughnut',
      data: {
        labels: <?= json_encode($pie_labels) ?>,
        datasets: [{
          data: <?= json_encode($pie_data) ?>,
          backgroundColor: ['#3b82f6','#facc15','#ec4899','#f97316','#a855f7','#06b6d4'],
          borderWidth:1
        }]
      },
      options: {
        responsive:true, maintainAspectRatio:false,
        plugins:{ legend:{ position:'bottom', labels:{ boxWidth:12 } } }
      }
    });
  }
  <?php endif; ?>
</script>

</body>
</html>
