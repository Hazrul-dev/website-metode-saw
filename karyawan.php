<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fungsi untuk mendapatkan semua data karyawan
function getAllKaryawan($db) {
    $query = "SELECT * FROM karyawan ORDER BY id DESC";
    return $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk mendapatkan satu data karyawan berdasarkan ID
function getKaryawanById($db, $id) {
    $query = "SELECT * FROM karyawan WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Proses tambah data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah'])) {
    try {
        $query = "INSERT INTO karyawan (nip, nama, tempat_lahir, tanggal_lahir, jenis_kelamin) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            $_POST['nip'],
            $_POST['nama'],
            $_POST['tempat_lahir'],
            $_POST['tanggal_lahir'],
            $_POST['jenis_kelamin']
        ]);
        $_SESSION['success'] = "Data karyawan berhasil ditambahkan!";
        header('Location: karyawan.php');
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

// Proses update data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    try {
        $query = "UPDATE karyawan 
                  SET nip = ?, nama = ?, tempat_lahir = ?, tanggal_lahir = ?, jenis_kelamin = ? 
                  WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([
            $_POST['nip'],
            $_POST['nama'],
            $_POST['tempat_lahir'],
            $_POST['tanggal_lahir'],
            $_POST['jenis_kelamin'],
            $_POST['id']
        ]);
        $_SESSION['success'] = "Data karyawan berhasil diupdate!";
        header('Location: karyawan.php');
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

// Proses hapus data
if (isset($_GET['delete'])) {
    try {
        $query = "DELETE FROM karyawan WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$_GET['delete']]);
        $_SESSION['success'] = "Data karyawan berhasil dihapus!";
        header('Location: karyawan.php');
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

// Ambil semua data karyawan untuk ditampilkan
$karyawan = getAllKaryawan($db);

// Jika ada request edit, ambil data karyawan yang akan diedit
$editData = null;
if (isset($_GET['edit'])) {
    $editData = getKaryawanById($db, $_GET['edit']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="kota.png" type="image/x-icon">
    <title>Data Karyawan - SPK Karyawan Terbaik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/datatables@1.10.18/media/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* CSS untuk print */
        @media print {
            body * {
                visibility: hidden;
            }
            .print-area, .print-area * {
                visibility: visible;
            }
            .print-area {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
            }
            /* Sembunyikan kolom aksi saat cetak */
            .print-area th.aksi, .print-area td.aksi {
                display: none;
            }
        }

        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
        }
        .sidebar a:hover {
            background-color: rgba(255,255,255,0.1);
        }
        .content {
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link px-3 py-2" href="index.php">
                            <i class="fas fa-home me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active px-3 py-2" href="karyawan.php">
                            <i class="fas fa-users me-2"></i> Data Karyawan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3 py-2" href="kriteria.php">
                            <i class="fas fa-list-check me-2"></i> Kriteria
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3 py-2" href="penilaian.php">
                            <i class="fas fa-star me-2"></i> Penilaian
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3 py-2" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="content">
                     <!-- Tombol Kembali ke Beranda -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2><i class="fas fa-users me-2"></i> Data Karyawan</h2>
                        <a href="index.php" class="btn btn-secondary">Kembali ke Beranda</a>
                    </div>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?php 
                            echo $_SESSION['success'];
                            unset($_SESSION['success']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php 
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <!-- Form Input/Edit -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <?php echo $editData ? 'Edit Data Karyawan' : 'Tambah Data Karyawan'; ?>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <?php if ($editData): ?>
                                    <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                                <?php endif; ?>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nip" class="form-label">NIP</label>
                                            <input type="text" class="form-control" id="nip" name="nip" 
                                                   value="<?php echo $editData ? $editData['nip'] : ''; ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nama" class="form-label">Nama Lengkap</label>
                                            <input type="text" class="form-control" id="nama" name="nama"
                                                   value="<?php echo $editData ? $editData['nama'] : ''; ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                                            <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir"
                                                   value="<?php echo $editData ? $editData['tempat_lahir'] : ''; ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                                            <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir"
                                                   value="<?php echo $editData ? $editData['tanggal_lahir'] : ''; ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                                            <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                                <option value="">Pilih...</option>
                                                <option value="L" <?php echo $editData && $editData['jenis_kelamin'] == 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                                                <option value="P" <?php echo $editData && $editData['jenis_kelamin'] == 'P' ? 'selected' : ''; ?>>Perempuan</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary me-2" name="<?php echo $editData ? 'update' : 'tambah'; ?>">
                                        <?php echo $editData ? 'Update' : 'Tambah'; ?>
                                    </button>
                                    <a href="karyawan.php" class="btn btn-secondary">Batal</a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tabel Data Karyawan -->
                    <div class="card print-area"> <!-- print-area untuk bagian yang dicetak -->
                        <div class="card-header d-flex justify-content-between">
                            <span>Daftar Karyawan</span>
                            <button onclick="window.print()" class="btn btn-success">Cetak Data</button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="karyawanTable" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>NIP</th>
                                            <th>Nama</th>
                                            <th>Tempat Lahir</th>
                                            <th>Tanggal Lahir</th>
                                            <th>Jenis Kelamin</th>
                                            <th class="aksi">Aksi</th> <!-- Tambahkan kelas "aksi" -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($karyawan as $index => $k): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($k['nip']); ?></td>
                                                <td><?php echo htmlspecialchars($k['nama']); ?></td>
                                                <td><?php echo htmlspecialchars($k['tempat_lahir']); ?></td>
                                                <td><?php echo date('d-m-Y', strtotime($k['tanggal_lahir'])); ?></td>
                                                <td><?php echo $k['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                                                <td class="aksi"> <!-- Tambahkan kelas "aksi" -->
                                                    <a href="karyawan.php?edit=<?php echo $k['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                                    <a href="karyawan.php?delete=<?php echo $k['id']; ?>" 
                                                       class="btn btn-sm btn-danger"
                                                       onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Hapus</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#karyawanTable').DataTable();
        });
    </script>
</body>
</html>
