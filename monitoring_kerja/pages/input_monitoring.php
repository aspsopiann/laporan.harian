<?php
include "../koneksi.php";

if (isset($_POST['simpan'])) {
    $tanggal = $_POST['tanggal'];
    $nama = $_POST['nama'];
    $jabatan = $_POST['jabatan'];
    $kebersihan = $_POST['kebersihan'];
    $kerapihan = $_POST['kerapihan'];
    $pelayanan = $_POST['pelayanan'];
    $keterangan = $_POST['keterangan'];

    $query = mysqli_query($koneksi, "INSERT INTO monitoring
    (tanggal, nama, jabatan, kebersihan, kerapihan, pelayanan, keterangan)
    VALUES
    ('$tanggal', '$nama', '$jabatan', '$kebersihan', '$kerapihan', '$pelayanan', '$keterangan')");

    if ($query) {
        echo "<script>alert('Data berhasil disimpan'); window.location='input_monitoring.php';</script>";
    } else {
        echo "Gagal menyimpan data: " . mysqli_error($koneksi);
    }
}
?>

<h2>Input Monitoring Kerja</h2>

<form method="POST">
    <label>Tanggal</label><br>
    <input type="date" name="tanggal" required><br><br>

    <label>Nama Karyawan</label><br>
    <input type="text" name="nama" required><br><br>

    <label>Jabatan</label><br>
    <select name="jabatan" required>
        <option value="">Pilih Jabatan</option>
        <option value="Frontliner">Frontliner</option>
        <option value="Kasir">Kasir</option>
    </select><br><br>

    <label>Kebersihan Area</label><br>
    <select name="kebersihan" required>
        <option value="Baik">Baik</option>
        <option value="Cukup">Cukup</option>
        <option value="Kurang">Kurang</option>
    </select><br><br>

    <label>Kerapihan Display</label><br>
    <select name="kerapihan" required>
        <option value="Baik">Baik</option>
        <option value="Cukup">Cukup</option>
        <option value="Kurang">Kurang</option>
    </select><br><br>

    <label>Pelayanan</label><br>
    <select name="pelayanan" required>
        <option value="Baik">Baik</option>
        <option value="Cukup">Cukup</option>
        <option value="Kurang">Kurang</option>
    </select><br><br>

    <label>Keterangan</label><br>
    <textarea name="keterangan"></textarea><br><br>

    <button type="submit" name="simpan">Simpan Data</button>
</form>