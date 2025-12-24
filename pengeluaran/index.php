<?php
require_once __DIR__ . '/../config/functions.php';

/* ===== SIDEBAR ADMIN ===== */
$base   = '..';               // karena folder /pengeluaran
$active = 'pengeluaran';
$title  = 'Kas Warga — Pengeluaran';
include __DIR__ . '/../partials/layout_admin_top.php';
?>

<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
    <div>
      <h3 style="margin:0;">Data Pengeluaran</h3>
      <div class="small" style="color:#64748b;margin-top:4px;">
        Catatan pengeluaran kas RT.
      </div>
    </div>
    <a class="btn" href="create.php">+ Catat Pengeluaran</a>
  </div>

  <form method="get" class="mt-2"
        style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px">
    <div>
      <label>Dari Tanggal</label>
      <input type="date" name="dari" value="<?= h($_GET['dari'] ?? '') ?>">
    </div>

    <div>
      <label>Sampai Tanggal</label>
      <input type="date" name="sampai" value="<?= h($_GET['sampai'] ?? '') ?>">
    </div>

    <div>
      <label>Kategori</label>
      <input name="kategori" value="<?= h($_GET['kategori'] ?? '') ?>"
             placeholder="mis. Kebersihan">
    </div>

    <div style="display:flex;align-items:flex-end;gap:10px">
      <button class="btn" type="submit">Filter</button>
      <a class="btn ghost" href="index.php">Reset</a>
    </div>
  </form>

  <table class="table mt-2">
    <thead>
      <tr>
        <th>Tanggal</th>
        <th>Kategori</th>
        <th>Nominal</th>
        <th>Keterangan</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php
        $sql = "SELECT * FROM pengeluaran WHERE 1=1";
        $types = '';
        $p = [];

        if (($d = $_GET['dari'] ?? '') !== '') {
          $sql .= " AND tanggal >= ?";
          $types .= 's';
          $p[] = $d;
        }

        if (($s = $_GET['sampai'] ?? '') !== '') {
          $sql .= " AND tanggal <= ?";
          $types .= 's';
          $p[] = $s;
        }

        if (($k = trim($_GET['kategori'] ?? '')) !== '') {
          $sql .= " AND kategori LIKE ?";
          $types .= 's';
          $p[] = '%' . $k . '%';
        }

        $sql .= " ORDER BY tanggal DESC, id DESC";

        $res = q($sql, $types, ...$p)->get_result();
        while ($r = $res->fetch_assoc()):
      ?>
      <tr>
        <td><?= h($r['tanggal']) ?></td>
        <td><span class="badge info"><?= h($r['kategori']) ?></span></td>
        <td><?= rupiah($r['nominal']) ?></td>
        <td><?= h($r['keterangan'] ?? '-') ?></td>
        <td class="actions">
          <a class="btn ghost" href="edit.php?id=<?= (int)$r['id'] ?>">Edit</a>
          <a class="btn bad"
             onclick="return confirm('Hapus pengeluaran ini?')"
             href="delete.php?id=<?= (int)$r['id'] ?>">Hapus</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/../partials/layout_admin_bottom.php'; ?>
