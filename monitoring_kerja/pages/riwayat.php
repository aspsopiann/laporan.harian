<?php

include __DIR__ . '/../koneksi.php';

$tanggal = $_GET['tanggal'] ?? '';
$karyawan_id = (int)($_GET['karyawan_id'] ?? 0);

// DATA KARYAWAN UNTUK FILTER
$daftar_karyawan = $conn->query(
    "SELECT id, nama FROM karyawan ORDER BY nama ASC"
);

// AMBIL RIWAYAT MONITORING
$sql = "SELECT
            m.*,
            k.nama AS nama_karyawan
        FROM monitoring m
        LEFT JOIN karyawan k
        ON m.karyawan_id = k.id
        WHERE (? = '' OR m.tanggal = ?)
        AND (? = 0 OR m.karyawan_id = ?)
        ORDER BY m.tanggal DESC, m.id DESC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Kesalahan SQL: " . $conn->error);
}

$stmt->bind_param(
    "ssii",
    $tanggal,
    $tanggal,
    $karyawan_id,
    $karyawan_id
);

$stmt->execute();

$result = $stmt->get_result();

$total_data = $result->num_rows;

?>

<style>
.riwayat-page {
    padding: 20px;
    font-family: Arial, sans-serif;
    color: #1e293b;
}

.riwayat-header {
    margin-bottom: 25px;
}

.riwayat-header h2 {
    margin-bottom: 5px;
}

.riwayat-card {
    background: white;
    padding: 20px;
    border-radius: 14px;
    box-shadow: 0 4px 15px rgba(0,0,0,.05);
    margin-bottom: 20px;
}

.riwayat-filter {
    display: grid;
    grid-template-columns: 1fr 1fr auto;
    gap: 12px;
    align-items: end;
}

.riwayat-filter label {
    display: block;
    font-weight: bold;
    margin-bottom: 7px;
}

.riwayat-filter input,
.riwayat-filter select {
    width: 100%;
    padding: 11px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    box-sizing: border-box;
}

.riwayat-filter button,
.riwayat-filter a {
    display: inline-block;
    padding: 11px 16px;
    border: none;
    border-radius: 8px;
    background: #2563eb;
    color: white;
    text-decoration: none;
    cursor: pointer;
}

.riwayat-table-wrapper {
    overflow-x: auto;
}

.riwayat-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 900px;
}

.riwayat-table th {
    background: #eff6ff;
    color: #1e40af;
    padding: 13px;
    text-align: left;
}

.riwayat-table td {
    padding: 13px;
    border-bottom: 1px solid #e2e8f0;
}

.nilai {
    font-weight: bold;
    color: #2563eb;
}

.status-selesai {
    background: #dcfce7;
    color: #166534;
    padding: 5px 9px;
    border-radius: 20px;
}

.status-lain {
    background: #fef3c7;
    color: #92400e;
    padding: 5px 9px;
    border-radius: 20px;
}

.tombol-cetak {
    background: #16a34a;
    color: white;
    border: none;
    padding: 11px 16px;
    border-radius: 8px;
    cursor: pointer;
    margin-bottom: 15px;
}

@media(max-width:650px) {
    .riwayat-filter {
        grid-template-columns: 1fr;
    }
}

@media print {
    .riwayat-filter,
    .tombol-cetak {
        display: none;
    }

    .riwayat-card {
        box-shadow: none;
    }
}
</style>

<div class="riwayat-page">

    <div class="riwayat-header">
        <h2>Riwayat Monitoring</h2>
        <p>Daftar hasil penilaian kinerja karyawan toko.</p>
    </div>

    <div class="riwayat-card">

        <form method="GET" class="riwayat-filter">

            <input type="hidden" name="page" value="riwayat">

            <div>
                <label>Tanggal</label>
                <input
                    type="date"
                    name="tanggal"
                    value="<?= htmlspecialchars($tanggal) ?>"
                >
            </div>

            <div>
                <label>Nama Karyawan</label>

                <select name="karyawan_id">
                    <option value="0">Semua Karyawan</option>

                    <?php while ($k = $daftar_karyawan->fetch_assoc()): ?>

                        <option
                            value="<?= (int)$k['id'] ?>"
                            <?= $karyawan_id == $k['id'] ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($k['nama']) ?>
                        </option>

                    <?php endwhile; ?>

                </select>
            </div>

            <div>
                <button type="submit">Cari Data</button>
                <a href="index.php?page=riwayat">Reset</a>
            </div>

        </form>

    </div>

    <div class="riwayat-card">

        <h3>Daftar Monitoring</h3>

        <p>Total data ditemukan: <strong><?= $total_data ?></strong></p>

        <button
            class="tombol-cetak"
            onclick="window.print()"
        >
            Cetak Laporan
        </button>

        <div class="riwayat-table-wrapper">

            <table class="riwayat-table">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nama Karyawan</th>
                        <th>Bagian</th>
                        <th>Kebersihan</th>
                        <th>Kerapian</th>
                        <th>Label Harga</th>
                        <th>Pelayanan</th>
                        <th>Nilai Rata-rata</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                <?php if ($total_data > 0): ?>

                    <?php $no = 1; ?>

                    <?php while ($row = $result->fetch_assoc()): ?>

                        <?php
                        $nilai = (
                            (int)$row['kebersihan'] +
                            (int)$row['kerapian'] +
                            (int)$row['label_harga'] +
                            (int)$row['pelayanan']
                        ) / 4;
                        ?>

                        <tr>
                            <td><?= $no++ ?></td>

                            <td>
                                <?= date('d-m-Y', strtotime($row['tanggal'])) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['nama_karyawan'] ?? '-') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['bagian']) ?>
                            </td>

                            <td><?= (int)$row['kebersihan'] ?></td>
                            <td><?= (int)$row['kerapian'] ?></td>
                            <td><?= (int)$row['label_harga'] ?></td>
                            <td><?= (int)$row['pelayanan'] ?></td>

                            <td class="nilai">
                                <?= number_format($nilai, 1) ?>
                            </td>

                            <td>
                                <?php if ($row['status'] === 'selesai'): ?>
                                    <span class="status-selesai">
                                        Selesai
                                    </span>
                                <?php else: ?>
                                    <span class="status-lain">
                                        <?= htmlspecialchars($row['status']) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="10" style="text-align:center">
                            Belum ada data monitoring.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?php $stmt->close(); ?>