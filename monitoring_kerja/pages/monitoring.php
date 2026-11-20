<?php

include __DIR__ . '/../koneksi.php';

$pesan = "";
$error = "";

// PROSES SIMPAN MONITORING
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $tanggal = $_POST["tanggal"] ?? "";
    $karyawan_id = (int)($_POST["karyawan_id"] ?? 0);
    $bagian = $_POST["bagian"] ?? "";

    $kebersihan = (int)($_POST["kebersihan"] ?? 0);
    $kerapian = (int)($_POST["kerapian"] ?? 0);
    $label_harga = (int)($_POST["label_harga"] ?? 0);
    $pelayanan = (int)($_POST["pelayanan"] ?? 0);

    $catatan = trim($_POST["catatan"] ?? "");

    if (
        empty($tanggal) ||
        $karyawan_id <= 0 ||
        !in_array($bagian, ["Kasir", "Frontliner"]) ||
        !in_array($kebersihan, [40, 60, 80, 100]) ||
        !in_array($kerapian, [40, 60, 80, 100]) ||
        !in_array($label_harga, [40, 60, 80, 100]) ||
        !in_array($pelayanan, [40, 60, 80, 100])
    ) {

        $error = "Mohon lengkapi semua data dengan benar.";

    } else {

        $kategori = "Penilaian Kerja";
        $pekerjaan = "Monitoring " . $bagian;

        $sql = "INSERT INTO monitoring
        (
            tanggal,
            karyawan_id,
            bagian,
            kategori,
            pekerjaan,
            status,
            kebersihan,
            kerapian,
            label_harga,
            pelayanan,
            catatan
        )
        VALUES (?, ?, ?, ?, ?, 'selesai', ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {

            $error = "Kesalahan SQL: " . $conn->error;

        } else {

            $stmt->bind_param(
                "sisssiiiis",
                $tanggal,
                $karyawan_id,
                $bagian,
                $kategori,
                $pekerjaan,
                $kebersihan,
                $kerapian,
                $label_harga,
                $pelayanan,
                $catatan
            );

            if ($stmt->execute()) {

                $pesan = "Monitoring berhasil disimpan!";

            } else {

                $error = "Gagal menyimpan: " . $stmt->error;

            }

            $stmt->close();
        }
    }
}

// AMBIL DATA KARYAWAN
$karyawan = $conn->query(
    "SELECT id, nama FROM karyawan ORDER BY nama ASC"
);

if (!$karyawan) {
    die("Gagal mengambil data karyawan: " . $conn->error);
}

?>

<style>

.monitoring-page {
    padding: 20px;
    font-family: Arial, sans-serif;
}

.monitoring-header {
    margin-bottom: 25px;
}

.monitoring-header h2 {
    color: #1e293b;
    margin-bottom: 5px;
}

.monitoring-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,.06);
    max-width: 900px;
}

.monitoring-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

.monitoring-group {
    margin-bottom: 16px;
}

.monitoring-group label {
    display: block;
    font-weight: bold;
    margin-bottom: 8px;
    color: #334155;
}

.monitoring-group input,
.monitoring-group select,
.monitoring-group textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    box-sizing: border-box;
    background: white;
}

.monitoring-button {
    width: 100%;
    padding: 14px;
    background: #2563eb;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
    margin-top: 10px;
}

.monitoring-button:hover {
    background: #1d4ed8;
}

.monitoring-success {
    background: #dcfce7;
    color: #166534;
    padding: 14px;
    border-radius: 8px;
    margin-bottom: 15px;
}

.monitoring-error {
    background: #fee2e2;
    color: #991b1b;
    padding: 14px;
    border-radius: 8px;
    margin-bottom: 15px;
}

.monitoring-note {
    color: #64748b;
    font-size: 13px;
}

@media(max-width:650px) {
    .monitoring-grid {
        grid-template-columns: 1fr;
    }
}

</style>

<div class="monitoring-page">

    <div class="monitoring-header">
        <h2>Monitoring Kerja</h2>
        <p>Penilaian kinerja karyawan toko</p>
    </div>

    <div class="monitoring-card">

        <?php if ($pesan): ?>
            <div class="monitoring-success">
                <?= htmlspecialchars($pesan) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="monitoring-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <h3>Form Penilaian Karyawan</h3>

        <form method="POST">

            <div class="monitoring-grid">

                <div class="monitoring-group">
                    <label>Tanggal Monitoring</label>
                    <input
                        type="date"
                        name="tanggal"
                        value="<?= date('Y-m-d') ?>"
                        required
                    >
                </div>

                <div class="monitoring-group">
                    <label>Nama Karyawan</label>

                    <select name="karyawan_id" required>
                        <option value="">Pilih Karyawan</option>

                        <?php while ($row = $karyawan->fetch_assoc()): ?>

                            <option value="<?= (int)$row['id'] ?>">
                                <?= htmlspecialchars($row['nama']) ?>
                            </option>

                        <?php endwhile; ?>

                    </select>
                </div>

                <div class="monitoring-group">
                    <label>Bagian</label>

                    <select name="bagian" required>
                        <option value="">Pilih Bagian</option>
                        <option value="Kasir">Kasir</option>
                        <option value="Frontliner">Frontliner</option>
                    </select>
                </div>

                <div class="monitoring-group">
                    <label>Kebersihan Area</label>

                    <select name="kebersihan" required>
                        <option value="100">Sangat Baik - 100</option>
                        <option value="80">Baik - 80</option>
                        <option value="60">Cukup - 60</option>
                        <option value="40">Kurang - 40</option>
                    </select>
                </div>

                <div class="monitoring-group">
                    <label>Kerapian Display Rak</label>

                    <select name="kerapian" required>
                        <option value="100">Sangat Baik - 100</option>
                        <option value="80">Baik - 80</option>
                        <option value="60">Cukup - 60</option>
                        <option value="40">Kurang - 40</option>
                    </select>
                </div>

                <div class="monitoring-group">
                    <label>Kelengkapan Label Harga</label>

                    <select name="label_harga" required>
                        <option value="100">Lengkap - 100</option>
                        <option value="80">Hampir Lengkap - 80</option>
                        <option value="60">Cukup - 60</option>
                        <option value="40">Tidak Lengkap - 40</option>
                    </select>
                </div>

                <div class="monitoring-group">
                    <label>Pelayanan Pelanggan</label>

                    <select name="pelayanan" required>
                        <option value="100">Sangat Baik - 100</option>
                        <option value="80">Baik - 80</option>
                        <option value="60">Cukup - 60</option>
                        <option value="40">Kurang - 40</option>
                    </select>
                </div>

            </div>

            <div class="monitoring-group">
                <label>Catatan Hasil Monitoring</label>

                <textarea
                    name="catatan"
                    rows="4"
                    placeholder="Masukkan catatan hasil penilaian..."
                ></textarea>
            </div>

            <p class="monitoring-note">
                Pastikan seluruh penilaian sudah benar sebelum disimpan.
            </p>

            <button type="submit" class="monitoring-button">
                SIMPAN MONITORING
            </button>

        </form>

    </div>

</div>