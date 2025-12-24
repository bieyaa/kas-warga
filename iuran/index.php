<?php
require_once __DIR__ . '/../config/functions.php';

/* ===== SIDEBAR ADMIN ===== */
$base   = '..';        // karena file ini di /iuran
$active = 'iuran';
$title  = 'Kas Warga — Iuran';
include __DIR__ . '/../partials/layout_admin_top.php';
?>

<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
    <div>
      <h3 style="margin:0;">Data Iuran</h3>
      <div class="small" style="color:#64748b;margin-top:4px;">Kelola data pembayaran iuran warga.</div>
    </div>
    <a class="btn" href="create.php">+ Catat Iuran</a>
  </div>

  <form method="get" class="mt-2" style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px">
    <div>
      <label>Bulan (YYYY-MM)</label>
      <input name="bulan" value="<?= h($_GET['bulan'] ?? '') ?>" placeholder="2025-10">
    </div>

    <div>
      <label>Status</label>
      <select name="status">
        <?php $s = $_GET['status'] ?? ''; ?>
        <option value="">Semua</option>
        <option value="LUNAS" <?= $s === 'LUNAS' ? 'selected' : '' ?>>LUNAS</option>
        <option value="BELUM" <?= $s === 'BELUM' ? 'selected' : '' ?>>BELUM</option>
      </select>
    </div>

    <div>
      <label>Warga</label>
      <input name="q" value="<?= h($_GET['q'] ?? '') ?>" placeholder="cari nama/alamat">
    </div>

    <div style="display:flex;align-items:flex-end;gap:10px">
      <button class="btn" type="submit">Filter</button>
      <a class="btn ghost" href="index.php">Reset</a>
    </div>
  </form>

  <table class="table mt-2">
    <thead>
      <tr>
        <th>Warga</th>
        <th>Bulan</th>
        <th>Nominal</th>
        <th>Status</th>
        <th>Keterangan</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $bulan  = trim($_GET['bulan'] ?? '');
      $status = trim($_GET['status'] ?? '');
      $qtext  = '%' . trim($_GET['q'] ?? '') . '%';

      $sql = "SELECT i.*, w.nama
              FROM iuran i
              JOIN warga w ON w.id=i.warga_id
              WHERE 1=1";

      $types  = '';
      $params = [];

      if ($bulan !== '') {
        $sql .= " AND i.bulan=?";
        $types .= 's';
        $params[] = $bulan;
      }

      if ($status !== '') {
        $sql .= " AND i.status=?";
        $types .= 's';
        $params[] = $status;
      }

      // selalu bisa search (kalau kosong berarti %% dan aman)
      $sql .= " AND (w.nama LIKE ? OR w.alamat LIKE ?)";
      $types .= 'ss';
      $params[] = $qtext;
      $params[] = $qtext;

      $sql .= " ORDER BY i.created_at DESC";

      $st  = q($sql, $types, ...$params);
      $res = $st->get_result();

      while ($r = $res->fetch_assoc()):
      ?>
        <tr>
          <td><?= h($r['nama']) ?></td>
          <td><?= h(month_name($r['bulan'])) ?></td>
          <td><?= rupiah($r['nominal']) ?></td>
          <td>
            <?= $r['status'] === 'LUNAS'
              ? '<span class="badge ok">LUNAS</span>'
              : '<span class="badge warn">BELUM</span>' ?>
          </td>
          <td><?= h($r['keterangan'] ?? '-') ?></td>
          <td class="actions">
            <a class="btn ghost" href="edit.php?id=<?= (int)$r['id'] ?>">Edit</a>
            <a class="btn bad" onclick="return confirm('Hapus iuran ini?')" href="delete.php?id=<?= (int)$r['id'] ?>">Hapus</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/../partials/layout_admin_bottom.php'; ?>
