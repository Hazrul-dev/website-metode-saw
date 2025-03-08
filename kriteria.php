<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Proses tambah data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah'])) {
    try {
        $stmt = $db->prepare("INSERT INTO kriteria (nama, tipe, bobot) VALUES (?, ?, ?)");
        $stmt->execute([$_POST['nama'], $_POST['tipe'], $_POST['bobot']]);
        $_SESSION['success'] = "Kriteria berhasil ditambahkan!";
        header('Location: kriteria.php');
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

// Proses update data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    try {
        $stmt = $db->prepare("UPDATE kriteria SET nama = ?, tipe = ?, bobot = ? WHERE id = ?");
        $stmt->execute([$_POST['nama'], $_POST['tipe'], $_POST['bobot'], $_POST['id']]);
        $_SESSION['success'] = "Kriteria berhasil diupdate!";
        header('Location: kriteria.php');
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

// Proses hapus data
if (isset($_GET['delete'])) {
    try {
        $stmt = $db->prepare("DELETE FROM kriteria WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $_SESSION['success'] = "Kriteria berhasil dihapus!";
        header('Location: kriteria.php');
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

// Ambil data untuk edit
$editData = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM kriteria WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editData = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Ambil semua data kriteria
$stmt = $db->query("SELECT * FROM kriteria ORDER BY id DESC");
$kriteria = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="kota.png" type="image/x-icon">
    <title>Data Kriteria - SPK Karyawan Terbaik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/datatables@1.10.18/media/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
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
        .card {
            border: none;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border-radius: 15px;
        }
        .card-header {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
        }
        .btn-gradient {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            border: none;
        }
        .btn-gradient:hover {
            background: linear-gradient(135deg, #224abe 0%, #4e73df 100%);
            color: white;
        }
        .btn-secondary {
            background: linear-gradient(135deg, #224abe 0%, #4e73df 100%);
            color: white;
        }
        .badge-benefit {
            background-color: #28a745;
        }
        .badge-cost {
            background-color: #dc3545;
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
                            <a class="nav-link px-3 py-2" href="karyawan.php">
                                <i class="fas fa-users me-2"></i> Data Karyawan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active px-3 py-2" href="kriteria.php">
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
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                        <h2><i class="fas fa-list-check me-2"></i>Data Kriteria</h2>
                        <a href="index.php" class="btn btn-secondary">Kembali ke Beranda</a>
                    </div>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php 
                            echo $_SESSION['success'];
                            unset($_SESSION['success']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Form Input/Edit -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-edit me-1"></i>
                            <?php echo $editData ? 'Edit Kriteria' : 'Tambah Kriteria Baru'; ?>
                        </div>
                        <div class="card-body">
                            <form method="POST" class="row g-3">
                                <?php if ($editData): ?>
                                    <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                                <?php endif; ?>
                                
                                <div class="col-md-4">
                                    <label for="nama" class="form-label">Nama Kriteria</label>
                                    <input type="text" class="form-control" id="nama" name="nama" 
                                           value="<?php echo $editData ? $editData['nama'] : ''; ?>" required>
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="tipe" class="form-label">Jenis Kriteria</label>
                                    <select class="form-select" id="tipe" name="tipe" required>
                                        <option value="">Pilih Jenis</option>
                                        <option value="benefit" <?php echo ($editData && $editData['tipe'] == 'benefit') ? 'selected' : ''; ?>>Benefit</option>
                                        <option value="cost" <?php echo ($editData && $editData['tipe'] == 'cost') ? 'selected' : ''; ?>>Cost</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="bobot" class="form-label">Bobot</label>
                                    <input type="number" class="form-control" id="bobot" name="bobot" 
                                           step="0.01" min="0" max="100"
                                           value="<?php echo $editData ? $editData['bobot'] : ''; ?>" required>
                                </div>

                                <div class="col-12">
                                    <button type="submit" name="<?php echo $editData ? 'update' : 'tambah'; ?>" 
                                            class="btn btn-gradient">
                                        <i class="fas <?php echo $editData ? 'fa-save' : 'fa-plus'; ?> me-1"></i>
                                        <?php echo $editData ? 'Update' : 'Simpan'; ?>
                                    </button>
                                    <?php if ($editData): ?>
                                        <a href="kriteria.php" class="btn btn-secondary">
                                            <i class="fas fa-times me-1"></i>Batal
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tabel Data -->
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-table me-1"></i>
                            Daftar Kriteria
                        </div>
                        <div class="card-body">
                            <table id="kriteriaTable" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Kriteria</th>
                                        <th>Jenis</th>
                                        <th>Bobot</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($kriteria as $index => $k): ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td><?php echo htmlspecialchars($k['nama']); ?></td>
                                            <td>
                                                <span class="badge <?php echo $k['tipe'] == 'benefit' ? 'badge-benefit' : 'badge-cost'; ?>">
                                                    <?php echo ucfirst($k['tipe']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $k['bobot']; ?></td>
                                            <td>
                                                <a href="kriteria.php?edit=<?php echo $k['id']; ?>" 
                                                   class="btn btn-warning btn-sm">
                                                   <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="kriteria.php?delete=<?php echo $k['id']; ?>" 
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Apakah Anda yakin ingin menghapus kriteria ini?')">
                                                   <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables@1.10.18/media/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#kriteriaTable').DataTable({
                "pageLength": 10,
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.10.19/i18n/Indonesian.json"
                }
            });
        });
    </script>
</body>
</html>