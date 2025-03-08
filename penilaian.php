<?php
session_start();
require_once 'config/database.php';

// Fungsi untuk mengambil data penilaian
function getPenilaian($db, $keyword = '') {
    $query = "SELECT p.id, k.nama AS nama_karyawan, k.id AS karyawan_id, 
              kr.nama AS nama_kriteria, kr.bobot, kr.tipe, p.nilai, kr.id AS kriteria_id
              FROM penilaian p
              JOIN karyawan k ON p.karyawan_id = k.id
              JOIN kriteria kr ON p.kriteria_id = kr.id";
    
    if ($keyword) {
        $query .= " WHERE k.nama LIKE ?";
        $stmt = $db->prepare($query);
        $stmt->execute(['%' . $keyword . '%']);
    } else {
        $stmt = $db->query($query);
    }

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk mengelompokkan data penilaian per karyawan
function groupPenilaianByKaryawan($penilaian) {
    $grouped = [];
    foreach ($penilaian as $p) {
        $grouped[$p['karyawan_id']][] = $p;
    }
    return $grouped;
}

// Fungsi untuk mendapatkan nilai max dan min per kriteria
function getMaxMinKriteria($penilaian) {
    $maxMin = [];
    foreach ($penilaian as $p) {
        $kriteriaId = $p['kriteria_id'];
        if (!isset($maxMin[$kriteriaId])) {
            $maxMin[$kriteriaId] = [
                'max' => $p['nilai'],
                'min' => $p['nilai'],
                'tipe' => $p['tipe'],
                'bobot' => $p['bobot']
            ];
        } else {
            $maxMin[$kriteriaId]['max'] = max($maxMin[$kriteriaId]['max'], $p['nilai']);
            $maxMin[$kriteriaId]['min'] = min($maxMin[$kriteriaId]['min'], $p['nilai']);
        }
    }
    return $maxMin;
}

// Fungsi untuk mendapatkan mapping kriteria
function getKriteriaMapping($db) {
    $stmt = $db->query("SELECT id FROM kriteria ORDER BY id ASC");
    $kriteria = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $mapping = [];
    foreach ($kriteria as $index => $k) {
        $mapping[$k['id']] = $index + 1;
    }
    return $mapping;
}

// Fungsi untuk menghitung normalisasi dan preferensi SAW
function hitungSAW($penilaian, $db) {
    // Kelompokkan data per karyawan
    $groupedPenilaian = groupPenilaianByKaryawan($penilaian);
    
    // Dapatkan nilai max dan min per kriteria
    $maxMinKriteria = getMaxMinKriteria($penilaian);
    
    // Dapatkan mapping kriteria
    $kriteriaMapping = getKriteriaMapping($db);
    
    $hasilPerhitungan = [];
    
    // Hitung normalisasi dan preferensi untuk setiap karyawan
    foreach ($groupedPenilaian as $karyawanId => $nilaiKaryawan) {
        $normalisasi = [];
        $preferensi = [];
        $totalPreferensi = 0;
        
        foreach ($nilaiKaryawan as $nilai) {
            $kriteriaId = $nilai['kriteria_id'];
            $nomorKriteria = $kriteriaMapping[$kriteriaId];
            
            // Hitung normalisasi
            if ($maxMinKriteria[$kriteriaId]['tipe'] == 'benefit') {
                $normalisasi[$nomorKriteria] = $nilai['nilai'] / $maxMinKriteria[$kriteriaId]['max'];
            } else {
                $normalisasi[$nomorKriteria] = $maxMinKriteria[$kriteriaId]['min'] / $nilai['nilai'];
            }
            
            // Hitung preferensi
            $preferensi[$nomorKriteria] = $normalisasi[$nomorKriteria] * $maxMinKriteria[$kriteriaId]['bobot'];
            $totalPreferensi += $preferensi[$nomorKriteria];
        }
        
        $hasilPerhitungan[$karyawanId] = [
            'nama_karyawan' => $nilaiKaryawan[0]['nama_karyawan'],
            'normalisasi' => $normalisasi,
            'preferensi' => $preferensi,
            'total_preferensi' => $totalPreferensi
        ];
    }
    
    // Urutkan hasil berdasarkan total preferensi (descending)
    uasort($hasilPerhitungan, function($a, $b) {
        return $b['total_preferensi'] <=> $a['total_preferensi'];
    });
    
    return $hasilPerhitungan;
}

// Proses CRUD
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['tambah'])) {
        $stmt = $db->prepare("INSERT INTO penilaian (karyawan_id, kriteria_id, nilai) VALUES (?, ?, ?)");
        $stmt->execute([$_POST['karyawan_id'], $_POST['kriteria_id'], $_POST['nilai']]);
        $_SESSION['success'] = "Data penilaian berhasil ditambahkan!";
    } elseif (isset($_POST['update'])) {
        $stmt = $db->prepare("UPDATE penilaian SET karyawan_id = ?, kriteria_id = ?, nilai = ? WHERE id = ?");
        $stmt->execute([$_POST['karyawan_id'], $_POST['kriteria_id'], $_POST['nilai'], $_POST['id']]);
        $_SESSION['success'] = "Data penilaian berhasil diperbarui!";
    }
    header('Location: penilaian.php');
    exit();
}

// Hapus penilaian
if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM penilaian WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $_SESSION['success'] = "Data penilaian berhasil dihapus!";
    header('Location: penilaian.php');
    exit();
}

// Proses pencarian
$keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';
$penilaian = getPenilaian($db, $keyword);
$hasilSAW = hitungSAW($penilaian, $db);

// Ambil data karyawan dan kriteria untuk dropdown
$karyawan = $db->query("SELECT * FROM karyawan")->fetchAll(PDO::FETCH_ASSOC);
$kriteria = $db->query("SELECT * FROM kriteria")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="kota.png" type="image/x-icon">
    <title>Data Penilaian Karyawan - SPK Karyawan Terbaik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 20px;
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
        .btn-gradient {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            border: none;
        }
        .btn-secondary {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
        }
        .btn-gradient:hover {
            background: linear-gradient(135deg, #224abe 0%, #4e73df 100%);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                    <i class="fas fa-home me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="karyawan.php">
                    <i class="fas fa-users me-2"></i> Data Karyawan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="kriteria.php">
                    <i class="fas fa-list-check me-2"></i> Kriteria
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="penilaian.php">
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

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 content">
                <h2><i class="fas fa-star me-2"></i> Data Penilaian Karyawan</h2>
                <a href="cetak_penilaian.php" target="_blank" class="btn btn-primary mb-3">
                <i class="fas fa-print"></i> Cetak Hasil Penilaian
                </a>
                <a href="index.php" class="btn btn-secondary mb-3">Kembali ke Beranda</a>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                        ?>
                    </div>
                <?php endif; ?>

                <!-- Form Pencarian -->
            <form method="POST" class="mb-3">
                <div class="input-group">
                    <input type="text" name="keyword" class="form-control" placeholder="Cari karyawan..." value="<?php echo $keyword; ?>">
                    <button type="submit" name="search" class="btn btn-primary">Cari</button>
                </div>
            </form>

            <!-- Form Tambah/Edit Penilaian -->
            <form method="POST" class="mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <select name="karyawan_id" class="form-select" required>
                            <option value="">Pilih Karyawan</option>
                            <?php foreach ($karyawan as $k): ?>
                                <option value="<?php echo $k['id']; ?>"><?php echo $k['nama']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select name="kriteria_id" class="form-select" required>
                            <option value="">Pilih Kriteria</option>
                            <?php foreach ($kriteria as $kr): ?>
                                <option value="<?php echo $kr['id']; ?>"><?php echo $kr['nama']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="nilai" class="form-control" placeholder="Nilai" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="tambah" class="btn btn-gradient">Tambah</button>
                    </div>
                </div>
            </form>

            <!-- Tabel Penilaian -->
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Karyawan</th>
                        <th>Kriteria</th>
                        <th>Nilai</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($penilaian as $index => $p): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($p['nama_karyawan']); ?></td>
                            <td><?php echo htmlspecialchars($p['nama_kriteria']); ?></td>
                            <td><?php echo htmlspecialchars($p['nilai']); ?></td>
                            <td>
                                <a href="penilaian.php?edit=<?php echo $p['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="penilaian.php?delete=<?php echo $p['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

                <!-- Tabel Hasil Perhitungan SAW -->
                <table class="table table-bordered">
                <thead>
                    <tr>
                    <th>Peringkat</th>
                    <th>Nama Karyawan</th>
                    <th>Nilai Normalisasi</th>
                    <th>Nilai Preferensi</th>
                    <th>Total Nilai</th>
                </tr>
    </thead>
    <tbody>
        <?php 
        $rank = 1;
        foreach ($hasilSAW as $karyawanId => $hasil): 
        ?>
        <tr>
            <td><?php echo $rank++; ?></td>
            <td><?php echo htmlspecialchars($hasil['nama_karyawan']); ?></td>
            <td>
                <?php 
                ksort($hasil['normalisasi']);
                foreach ($hasil['normalisasi'] as $nomorKriteria => $nilai): ?>
                    Kriteria <?php echo $nomorKriteria; ?>: <?php echo number_format($nilai, 2); ?><br>
                <?php endforeach; ?>
            </td>
            <td>
                <?php 
                ksort($hasil['preferensi']);
                foreach ($hasil['preferensi'] as $nomorKriteria => $nilai): ?>
                    Kriteria <?php echo $nomorKriteria; ?>: <?php echo number_format($nilai, 2); ?><br>
                <?php endforeach; ?>
            </td>
            <td><?php echo number_format($hasil['total_preferensi'], 2); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>