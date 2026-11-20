<?php
include __DIR__ . "/../koneksi.php";

$tanggal_awal = $_GET['awal'] ?? date('Y-m-01');
$tanggal_akhir = $_GET['akhir'] ?? date('Y-m-d');
$kategori = $_GET['kategori'] ?? '';
$status = $_GET['status'] ?? '';

$awal = mysqli_real_escape_string($conn, $tanggal_awal);
$akhir = mysqli_real_escape_string($conn, $tanggal_akhir);

$where = "WHERE m.tanggal BETWEEN '$awal' AND '$akhir'";

if (in_array($kategori, ['Kasir', 'Frontliner'], true)) {
    $kategori_sql = mysqli_real_escape_string($conn, $kategori);
    $where .= " AND m.kategori = '$kategori_sql'";
}

if (in_array($status, ['belum', 'proses', 'selesai'], true)) {
    $status_sql = mysqli_real_escape_string($conn, $status);
    $where .= " AND m.status = '$status_sql'";
}

$query = mysqli_query($conn, "
    SELECT m.*, k.nama, k.jabatan
    FROM monitoring m
    JOIN karyawan k ON m.karyawan_id = k.id
    $where
    ORDER BY m.tanggal DESC, m.id DESC
");

$total = mysqli_num_rows($query);
$selesai = 0;
$belum = 0;
$proses = 0;

$data = [];
while ($row = mysqli_fetch_assoc($query)) {
    $data[] = $row;

    if ($row['status'] === 'selesai') $selesai++;
    if ($row['status'] === 'belum') $belum++;
    if ($row['status'] === 'proses') $proses++;
}

$persentase = $total > 0 ? round(($selesai / $total) * 100) : 0;
?>

<div class="page-header">
    <h1>Laporan Monitoring</h1>
    <p>Rekap hasil pekerjaan karyawan toko</p>
</div>

<div class="content-card">
    <h3>Filter Laporan</h3>

    <form method="GET" action="index.php">
        <input type="hidden" name="page" value="laporan">

        <div class="form-group">
            <label>Tanggal Awal</label>
            <input type="date" name="awal"
                   value="<?= htmlspecialchars($tanggal_awal) ?>" required>
        </div>

        <div class="form-group">
            <label>Tanggal Akhir</label>
            <input type="date" name="akhir"
                   value="<?= htmlspecialchars($tanggal_akhir) ?>" required>
        </div>

        <div class="form-group">
            <label>Kategori</label>
            <select name="kategori">
                <option value="">Semua Kategori</option>
                <option value="Kasir" <?= $kategori === 'Kasir' ? 'selected' : '' ?>>Kasir</option>
                <option value="Frontliner" <?= $kategori === 'Frontliner' ? 'selected' : '' ?>>Frontliner</option>
            </select>
        </div>

        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="">Semua Status</option>
                <option value="belum" <?= $status === 'belum' ? 'selected' : '' ?>>Belum</option>
                <option value="proses" <?= $status === 'proses' ? 'selected' : '' ?>>Proses</option>
                <option value="selesai" <?= $status === 'selesai' ? 'selected' : '' ?>>Selesai</option>
            </select>
        </div>

        <button type="submit" class="btn-primary">Tampilkan Laporan</button>
        <a href="index.php?page=laporan" class="btn-secondary">Reset</a>
    </form>
</div>

<div class="stats-container">
    <div class="stat-card">
        <h3>Total Pekerjaan</h3>
        <h2><?= $total ?></h2>
    </div>

    <div class="stat-card">
        <h3>Selesai</h3>
        <h2><?= $selesai ?></h2>
    </div>

    <div class="stat-card">
        <h3>Dalam Proses</h3>
        <h2><?= $proses ?></h2>
    </div>

    <div class="stat-card">
        <h3>Belum Dikerjakan</h3>
        <h2><?= $belum ?></h2>
    </div>

    <div class="stat-card">
        <h3>Penyelesaian</h3>
        <h2><?= $persentase ?>%</h2>
    </div>
</div>

<div class="content-card">
    <div class="report-heading">
        <h3>Detail Laporan Pekerjaan</h3>
        <button onclick="window.print()" class="btn-primary">Cetak Laporan</button>
    </div>

    <div class="table-responsive">
        <table class="employee-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Nama Karyawan</th>
                    <th>Jabatan</th>
                    <th>Kategori</th>
                    <th>Pekerjaan</th>
                    <th>Status</th>
                    <th>Catatan</th>
                    <th>Bukti Foto</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($data) > 0): ?>
                    <?php foreach ($data as $i => $row): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($row['tanggal']) ?></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= htmlspecialchars($row['jabatan']) ?></td>
                            <td><?= htmlspecialchars($row['kategori']) ?></td>
                            <td><?= htmlspecialchars($row['pekerjaan']) ?></td>
                            <td><?= htmlspecialchars(ucfirst($row['status'])) ?></td>
                            <td><?= htmlspecialchars($row['catatan'] ?? '') ?></td>
                            <td>
                                <?php if (!empty($row['bukti_foto'])): ?>
                                    <a href="uploads/<?= rawurlencode(basename($row['bukti_foto'])) ?>" target="_blank">Lihat Foto</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align:center;">Belum ada data laporan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
@media print {
    .sidebar, .page-header, .content-card form,
    .report-heading button, .btn-primary, .btn-secondary {
        display: none !important;
    }

    .main {
        margin: 0 !important;
        padding: 10px !important;
    }

    .content-card {
        box-shadow: none !important;
    }

    body {
        background: white !important;
    }
}
</style>