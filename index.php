<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Query untuk mendapatkan perangkingan dengan metode SAW
$query = "SELECT 
    k.nama,
    SUM(
        CASE 
            WHEN kr.tipe = 'benefit' THEN
                (p.nilai / (SELECT MAX(nilai) FROM penilaian WHERE kriteria_id = p.kriteria_id)) * kr.bobot
            ELSE
                ((SELECT MIN(nilai) FROM penilaian WHERE kriteria_id = p.kriteria_id) / p.nilai) * kr.bobot
        END
    ) as total_nilai
FROM karyawan k
JOIN penilaian p ON k.id = p.karyawan_id
JOIN kriteria kr ON p.kriteria_id = kr.id
GROUP BY k.id, k.nama
ORDER BY total_nilai DESC
LIMIT 10";

$stmt = $db->query($query);
$rankings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="kota.png" type="image/x-icon">
    <title>Dashboard - SPK Karyawan Terbaik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary-color: #1a237e;
            --secondary-color: #0d47a1;
            --accent-color: #2962ff;
        }

        body {
            background: url('kantor.png') center/cover fixed;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.95);
            z-index: -1;
        }

        .logo-container {
            text-align: center;
            padding: 20px 0;
        }

        .logo {
            width: 120px;
            height: 120px;
            margin-bottom: 10px;
            animation: bounce 2s infinite;
        }

        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
            padding-top: 20px;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 5px;
            margin: 5px 15px;
            padding: 12px 15px;
            display: block;
        }

        .sidebar a:hover {
            background-color: var(--accent-color);
            transform: translateX(5px);
        }

        .sidebar .nav-item {
            margin-bottom: 5px;
        }

        .welcome-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            animation: slideInDown 1s ease-out;
            position: relative;
            overflow: hidden;
        }

        .welcome-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent 48%, rgba(255,255,255,0.1) 50%, transparent 52%);
            animation: shine 3s infinite linear;
        }

        @keyframes shine {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .card {
            background: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: fadeIn 1s ease-out;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .card-title {
            color: var(--primary-color);
            font-weight: bold;
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .footer {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px 0;
            text-align: center;
            position: relative;
            margin-top: 50px;
        }

        .footer-content {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .footer-logo {
            width: 50px;
            height: 50px;
            animation: pulse 2s infinite;
        }

        .developer-info {
            font-size: 14px;
            opacity: 0.9;
        }

        .developer-info span {
            color: #ffd700;
            font-weight: bold;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        #rankingChart {
            padding: 20px;
            animation: fadeInUp 1s ease-out;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-20px); }
            60% { transform: translateY(-10px); }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="logo-container">
                    <img src="kota.png" alt="Logo Pemkot Medan" class="logo">
                    <h5 class="text-white text-center">Pemkot Medan</h5>
                </div>
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">
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
                                <i class="fas fa-list me-2"></i> Kriteria
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="penilaian.php">
                                <i class="fas fa-star me-2"></i> Penilaian
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="welcome-section animate__animated animate__fadeIn">
                    <h1>Selamat Datang di SPK Karyawan Terbaik</h1>
                    <p class="lead">Kantor Walikota Medan</p>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card animate__animated animate__fadeInLeft">
                            <div class="card-body">
                                <h5 class="card-title">Sejarah Kantor Walikota Medan</h5>
                                <p class="card-text">
                                    Kantor Wali Kota Medan memiliki sejarah panjang yang mencerminkan perkembangan kota Medan dari sebuah kampung kecil menjadi salah satu pusat administrasi penting di Sumatera Utara. Pada abad ke-19, Medan mulai berkembang pesat dengan hadirnya perkebunan tembakau Deli yang menarik banyak pendatang dari berbagai daerah, termasuk Tionghoa, Eropa, dan India. Pertumbuhan ekonomi ini mendorong pemerintah kolonial Belanda untuk membangun infrastruktur kota yang lebih baik, termasuk kantor pemerintahan.
                                
                                    Kantor Wali Kota Medan yang ada saat ini, terletak di Jalan Raden Saleh, merupakan gedung bersejarah yang dibangun pada masa kolonial Belanda pada awal abad ke-20. Gedung ini dirancang dengan gaya arsitektur Eropa klasik, lengkap dengan pilar-pilar megah dan ornamen khas. Sebagai pusat pemerintahan, kantor ini menjadi saksi dari berbagai perubahan yang terjadi di kota Medan, mulai dari masa penjajahan Belanda, pendudukan Jepang, hingga kemerdekaan Indonesia.
                                    
                                    Setelah Indonesia merdeka, kantor ini terus berfungsi sebagai pusat pemerintahan kota Medan. Wali Kota Medan yang pertama setelah kemerdekaan, Dr. T. Mansoer, memimpin dari gedung ini dan melanjutkan pembangunan kota. Hingga kini, Kantor Wali Kota Medan tetap menjadi simbol administrasi dan pemerintahan kota, serta menjadi salah satu bangunan bersejarah yang melambangkan perjalanan panjang kota Medan menuju modernitas.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card animate__animated animate__fadeInRight">
                            <div class="card-body">
                                <h5 class="card-title">Grafik Perangkingan Karyawan Terbaik</h5>
                                <canvas id="rankingChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-content">
            <img src="logosumut.png" alt="Footer Logo" class="footer-logo">
            <div class="developer-info">
                <p>Dikembangkan dengan ❤️ oleh:</p>
                <p><span>Hazrul Anshari Ulvi</span>
                <p>&copy; 2024 - Sistem Pendukung Keputusan Karyawan Terbaik</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Data untuk grafik
        const rankings = <?php echo json_encode($rankings); ?>;
        const labels = rankings.map(r => r.nama);
        const values = rankings.map(r => r.total_nilai);

        // Membuat grafik
        const ctx = document.getElementById('rankingChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Nilai Total',
                    data: values,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>