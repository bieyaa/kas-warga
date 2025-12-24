<?php
require_once __DIR__ . '/../config/functions.php';

/* ====== SETUP SIDEBAR ADMIN ====== */
$base   = '..';          // karena ini ada di /warga
$active = 'warga';
$title  = 'Kas Warga — Data Warga';
include __DIR__ . '/../partials/layout_admin_top.php';
?>

<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
    <div>
      <h3 style="margin:0;">Data Warga</h3>
      <div class="small" style="color:#64748b;margin-top:4px;">Kelola data warga aktif RT.</div>
    </div>
    <a class="btn" href="create.php">+ Tambah Warga</a>
  </div>

  <table class="table mt-2">
    <thead>
      <tr>
        <th>Nama</th>
        <th>Alamat</th>
        <th>No. HP</th>
        <th>Dibuat</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $res = q("SELECT * FROM warga ORDER BY created_at DESC")->get_result();
      while ($r = $res->fetch_assoc()):
      ?>
      <tr>
        <td><?= h($r['nama']) ?></td>
        <td><?= h($r['alamat']) ?></td>
        <td><?= h($r['no_hp']) ?></td>
        <td><?= h($r['created_at']) ?></td>
        <td class="actions">
          <a class="btn ghost" href="edit.php?id=<?= (int)$r['id'] ?>">Edit</a>
          <a class="btn bad" onclick="return confirm('Hapus warga ini?')" href="delete.php?id=<?= (int)$r['id'] ?>">Hapus</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/../partials/layout_admin_bottom.php'; ?>
