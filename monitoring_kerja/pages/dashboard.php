<?php
include __DIR__ . "/../koneksi.php";

function hitung($conn, $sql) {
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        die("Kesalahan database: " . mysqli_error($conn));
    }
    return mysqli_fetch_assoc($result)['total'];
}

$totalKaryawan = hitung($conn,
    "SELECT COUNT(*) AS total FROM karyawan WHERE status='aktif'"
);

$tanggal = date('Y-m-d');

$totalPekerjaan = hitung($conn,
    "SELECT COUNT(*) AS total FROM monitoring WHERE tanggal='$tanggal'"
);

$selesai = hitung($conn,
    "SELECT COUNT(*) AS total FROM monitoring WHERE tanggal='$tanggal' AND status='selesai'"
);

$belum = hitung($conn,
    "SELECT COUNT(*) AS total FROM monitoring WHERE tanggal='$tanggal' AND status!='selesai'"
);

$aktivitas = mysqli_query($conn, "
    SELECT monitoring.*, karyawan.nama
    FROM monitoring
    JOIN karyawan ON monitoring.karyawan_id=karyawan.id
    ORDER BY monitoring.id DESC
    LIMIT 8
");
?>

<div class="page-heading">
    <div>
        <h1>Dashboard Monitoring</h1>
        <p>Selamat datang di sistem Monitoring Kerja NICESO.</p>
    </div>
    <div class="date-label">
        <?= date('d/m/Y') ?>
    </div>
</div>

<div class="stats-grid">

    <div class="stat-card">
        <span>Total Karyawan</span>
        <h2><?= $totalKaryawan ?></h2>
        <small>Karyawan aktif</small>
    </div>

    <div class="stat-card">
        <span>Total Pekerjaan</span>
        <h2><?= $totalPekerjaan ?></h2>
        <small>Pekerjaan hari ini</small>
    </div>

    <div class="stat-card">
        <span>Pekerjaan Selesai</span>
        <h2><?= $selesai ?></h2>
        <small>Sudah diselesaikan</small>
    </div>

    <div class="stat-card">
        <span>Belum Selesai</span>
        <h2><?= $belum ?></h2>
        <small>Perlu ditindaklanjuti</small>
    </div>

</div>

<div class="content-card">
    <h3>Aktivitas Terbaru</h3>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Nama Karyawan</th>
                    <th>Kategori</th>
                    <th>Pekerjaan</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
            <?php if(mysqli_num_rows($aktivitas) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($aktivitas)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['nama']) ?></td>
                    <td><?= htmlspecialchars($row['kategori']) ?></td>
                    <td><?= htmlspecialchars($row['pekerjaan']) ?></td>
                    <td>
                        <span class="status <?= htmlspecialchars($row['status']) ?>">
                            <?= htmlspecialchars(ucfirst($row['status'])) ?>
                        </span>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">Belum ada aktivitas hari ini.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>