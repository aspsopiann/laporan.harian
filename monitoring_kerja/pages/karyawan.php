<?php
include __DIR__ . "/../koneksi.php";

$pesan = "";
$edit = null;

// TAMBAH KARYAWAN
if (isset($_POST['tambah'])) {
    $nama = trim($_POST['nama']);
    $jabatan = $_POST['jabatan'];

    $stmt = mysqli_prepare($conn,
        "INSERT INTO karyawan (nama, jabatan) VALUES (?, ?)"
    );

    mysqli_stmt_bind_param($stmt, "ss", $nama, $jabatan);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: index.php?page=karyawan&pesan=tambah");
        exit;
    }

    $pesan = "Gagal menambahkan karyawan.";
}

// EDIT KARYAWAN
if (isset($_POST['edit'])) {
    $id = (int) $_POST['id'];
    $nama = trim($_POST['nama']);
    $jabatan = $_POST['jabatan'];
    $status = $_POST['status'];

    $stmt = mysqli_prepare($conn,
        "UPDATE karyawan SET nama=?, jabatan=?, status=? WHERE id=?"
    );

    mysqli_stmt_bind_param($stmt, "sssi",
        $nama, $jabatan, $status, $id
    );

    if (mysqli_stmt_execute($stmt)) {
        header("Location: index.php?page=karyawan&pesan=edit");
        exit;
    }

    $pesan = "Gagal mengubah data.";
}

// HAPUS KARYAWAN
if (isset($_POST['hapus'])) {
    $id = (int) $_POST['id'];

    $stmt = mysqli_prepare($conn,
        "DELETE FROM karyawan WHERE id=?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: index.php?page=karyawan&pesan=hapus");
        exit;
    }

    $pesan = "Data tidak bisa dihapus. Periksa apakah karyawan masih memiliki riwayat monitoring.";
}

// AMBIL DATA EDIT
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];

    $stmt = mysqli_prepare($conn,
        "SELECT * FROM karyawan WHERE id=?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $edit = mysqli_fetch_assoc(
        mysqli_stmt_get_result($stmt)
    );
}

// PESAN BERHASIL
$pesanUrl = $_GET['pesan'] ?? '';

$daftarPesan = [
    'tambah' => 'Karyawan berhasil ditambahkan.',
    'edit' => 'Data karyawan berhasil diperbarui.',
    'hapus' => 'Data karyawan berhasil dihapus.'
];

if (isset($daftarPesan[$pesanUrl])) {
    $pesan = $daftarPesan[$pesanUrl];
}

// AMBIL SELURUH KARYAWAN
$data = mysqli_query($conn,
    "SELECT * FROM karyawan ORDER BY id DESC"
);

$total = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS jumlah FROM karyawan WHERE status='aktif'"
))['jumlah'];
?>

<div class="page-heading">
    <div>
        <h1>Data Karyawan</h1>
        <p>Kelola data karyawan toko NICESO.</p>
    </div>
    <div class="date-label">
        Total Aktif: <?= $total ?>
    </div>
</div>

<?php if ($pesan): ?>
    <div class="alert-success">
        <?= htmlspecialchars($pesan) ?>
    </div>
<?php endif; ?>

<div class="content-card">

    <h3><?= $edit ? 'Edit Karyawan' : 'Tambah Karyawan Baru' ?></h3>

    <form method="POST">

        <?php if ($edit): ?>
            <input type="hidden" name="id"
                   value="<?= (int)$edit['id'] ?>">
        <?php endif; ?>

        <div class="form-group">
            <label>Nama Karyawan</label>
            <input type="text" name="nama"
                value="<?= htmlspecialchars($edit['nama'] ?? '') ?>"
                placeholder="Masukkan nama karyawan"
                required>
        </div>

        <div class="form-group">
            <label>Jabatan</label>
            <select name="jabatan" required>
                <option value="">Pilih Jabatan</option>
                <option value="Frontliner"
                    <?= ($edit['jabatan'] ?? '') == 'Frontliner' ? 'selected' : '' ?>>
                    Frontliner
                </option>
                <option value="Kasir"
                    <?= ($edit['jabatan'] ?? '') == 'Kasir' ? 'selected' : '' ?>>
                    Kasir
                </option>
                <option value="SPV"
                    <?= ($edit['jabatan'] ?? '') == 'SPV' ? 'selected' : '' ?>>
                    SPV
                </option>
            </select>
        </div>

        <?php if ($edit): ?>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="aktif"
                        <?= $edit['status'] == 'aktif' ? 'selected' : '' ?>>
                        Aktif
                    </option>
                    <option value="nonaktif"
                        <?= $edit['status'] == 'nonaktif' ? 'selected' : '' ?>>
                        Nonaktif
                    </option>
                </select>
            </div>
        <?php endif; ?>

        <div class="form-actions">
            <?php if ($edit): ?>
                <button type="submit" name="edit" class="btn-primary">
                    Simpan Perubahan
                </button>
                <a href="index.php?page=karyawan" class="btn-secondary">
                    Batal
                </a>
            <?php else: ?>
                <button type="submit" name="tambah" class="btn-primary">
                    + Tambah Karyawan
                </button>
            <?php endif; ?>
        </div>

    </form>
</div>

<div class="content-card employee-table">
    <h3>Daftar Karyawan</h3>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Jabatan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
            <?php $no = 1; ?>

            <?php while ($row = mysqli_fetch_assoc($data)): ?>
                <tr>
                    <td><?= $no++ ?></td>

                    <td>
                        <?= htmlspecialchars($row['nama']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($row['jabatan']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars(ucfirst($row['status'])) ?>
                    </td>

                    <td>
                        <a class="btn-edit"
                           href="index.php?page=karyawan&edit=<?= $row['id'] ?>">
                           Edit
                        </a>

                        <form method="POST" style="display:inline"
                            onsubmit="return confirm('Yakin ingin menghapus karyawan ini?')">
                            <input type="hidden" name="id"
                                value="<?= $row['id'] ?>">
                            <button type="submit" name="hapus"
                                class="btn-delete">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>

            <?php if (mysqli_num_rows($data) == 0): ?>
                <tr>
                    <td colspan="5">
                        Belum ada data karyawan.
                    </td>
                </tr>
            <?php endif; ?>

            </tbody>
        </table>
    </div>
</div>