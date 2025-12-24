<?php
require_once __DIR__ . '/../config/functions.php';

/* =======================
   SIDEBAR ADMIN SETUP
======================= */
$base   = '..';          // karena file ini di /iuran
$active = 'iuran';
$title  = 'Catat Iuran - Kas Warga';
include __DIR__ . '/../partials/layout_admin_top.php';

/* =======================
   LOGIC FORM
======================= */

// ambil list warga untuk dropdown
$wargaRes = q("SELECT id, nama FROM warga ORDER BY nama")->get_result();

// nilai default
$warga_id  = $_POST['warga_id']   ?? '';
$bulan     = $_POST['bulan']      ?? date('Y-m');
$nominal   = $_POST['nominal']    ?? '';
$status    = $_POST['status']     ?? 'BELUM';
$ket       = $_POST['keterangan'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $nominalInt = (int) $nominal;

  if ($warga_id === '' || $bulan === '' || $nominalInt <= 0 || $status === '') {
    $err = "Warga, bulan, nominal, dan status wajib diisi.";
  }

  if (!isset($err)) {
    q(
      "INSERT INTO iuran (warga_id, bulan, nominal, status, keterangan)
       VALUES (?,?,?,?,?)",
      'isdss',
      $warga_id,
      $bulan,
      $nominalInt,
      $status,
      $ket
    );
    redirect('index.php');
  }
}
?>

<div class="card">
  <h3>Catat Iuran</h3>

  <?php if (isset($err)): ?>
    <div class="badge bad" style="display:inline-block; margin-bottom:8px;">
      <?= h($err) ?>
    </div>
  <?php endif; ?>

  <form method="post" class="mt-2">

    <div class="form-group">
      <label for="warga_id">Warga</label>
      <select id="warga_id" name="warga_id" required>
        <option value="">-- pilih --</option>
        <?php while($w = $wargaRes->fetch_assoc()): ?>
          <option value="<?= $w['id'] ?>" <?= $warga_id == $w['id'] ? 'selected' : '' ?>>
            <?= h($w['nama']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="form-group">
      <label for="bulan">Bulan</label>
      <input type="month" id="bulan" name="bulan" value="<?= h($bulan) ?>" required>
    </div>

    <div class="form-group">
      <label for="nominal">Nominal</label>
      <input type="number" id="nominal" name="nominal" min="1"
             placeholder="100000" value="<?= h($nominal) ?>" required>
    </div>

    <div class="form-group">
      <label for="status">Status</label>
      <select id="status" name="status" required>
        <option value="BELUM" <?= $status === 'BELUM' ? 'selected' : '' ?>>BELUM</option>
        <option value="LUNAS" <?= $status === 'LUNAS' ? 'selected' : '' ?>>LUNAS</option>
      </select>
    </div>

    <div class="form-group">
      <label for="keterangan">Keterangan</label>
      <input type="text" id="keterangan" name="keterangan"
             placeholder="Tunai / Transfer / Catatan"
             value="<?= h($ket) ?>">
    </div>

    <div class="mt-2" style="display:flex;gap:10px">
      <button class="btn" type="submit">Simpan</button>
      <a class="btn ghost" href="index.php">Batal</a>
    </div>

  </form>
</div>

<?php include __DIR__ . '/../partials/layout_admin_bottom.php'; ?>
