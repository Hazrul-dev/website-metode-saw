<?php
session_start();
require_once 'config/database.php';

// Reuse existing functions
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

function groupPenilaianByKaryawan($penilaian) {
    $grouped = [];
    foreach ($penilaian as $p) {
        $grouped[$p['karyawan_id']][] = $p;
    }
    return $grouped;
}

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

function hitungSAW($penilaian) {
    $groupedPenilaian = groupPenilaianByKaryawan($penilaian);
    $maxMinKriteria = getMaxMinKriteria($penilaian);
    
    $hasilPerhitungan = [];
    
    foreach ($groupedPenilaian as $karyawanId => $nilaiKaryawan) {
        $normalisasi = [];
        $preferensi = [];
        $totalPreferensi = 0;
        
        foreach ($nilaiKaryawan as $nilai) {
            $kriteriaId = $nilai['kriteria_id'];
            
            if ($maxMinKriteria[$kriteriaId]['tipe'] == 'benefit') {
                $normalisasi[$kriteriaId] = $nilai['nilai'] / $maxMinKriteria[$kriteriaId]['max'];
            } else {
                $normalisasi[$kriteriaId] = $maxMinKriteria[$kriteriaId]['min'] / $nilai['nilai'];
            }
            
            $preferensi[$kriteriaId] = $normalisasi[$kriteriaId] * $maxMinKriteria[$kriteriaId]['bobot'];
            $totalPreferensi += $preferensi[$kriteriaId];
        }
        
        $hasilPerhitungan[$karyawanId] = [
            'nama_karyawan' => $nilaiKaryawan[0]['nama_karyawan'],
            'normalisasi' => $normalisasi,
            'preferensi' => $preferensi,
            'total_preferensi' => $totalPreferensi
        ];
    }
    
    uasort($hasilPerhitungan, function($a, $b) {
        return $b['total_preferensi'] <=> $a['total_preferensi'];
    });
    
    return $hasilPerhitungan;
}

$penilaian = getPenilaian($db);
$hasilSAW = hitungSAW($penilaian);

// Get current date for the report
$tanggal = date('d-m-Y');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="kota.png" type="image/x-icon">
    <title>Cetak Hasil Penilaian SAW</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .title {
            font-size: 20px;
            font-weight: bold;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .footer {
            margin-top: 50px;
            text-align: right;
        }
        .print-button {
            display: none;
        }
        @media print {
            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()" style="margin: 20px;">Cetak Laporan</button>

    <div class="header">
        <div class="title">LAPORAN HASIL PENILAIAN KARYAWAN</div>
        <div class="title">METODE SIMPLE ADDITIVE WEIGHTING (SAW)</div>
        <div>Tanggal: <?php echo $tanggal; ?></div>
    </div>

    <h3>Hasil Perangkingan Karyawan</h3>
    <table>
        <thead>
            <tr>
                <th>Peringkat</th>
                <th>Nama Karyawan</th>
                <th>Total Nilai</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $rank = 1;
            foreach ($hasilSAW as $karyawanId => $hasil): 
                // Tentukan keterangan berdasarkan peringkat
                $keterangan = $rank === 1 ? "Karyawan Terbaik" : "";
            ?>
            <tr>
                <td><?php echo $rank; ?></td>
                <td><?php echo htmlspecialchars($hasil['nama_karyawan']); ?></td>
                <td><?php echo number_format($hasil['total_preferensi'], 3); ?></td>
                <td><?php echo $keterangan; ?></td>
            </tr>
            <?php 
            $rank++;
            endforeach; 
            ?>
        </tbody>
    </table>

    <h3>Detail Perhitungan</h3>
    <table>
        <thead>
            <tr>
                <th>Nama Karyawan</th>
                <th>Nilai Normalisasi</th>
                <th>Nilai Preferensi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($hasilSAW as $karyawanId => $hasil): ?>
            <tr>
                <td><?php echo htmlspecialchars($hasil['nama_karyawan']); ?></td>
                <td>
                    <?php foreach ($hasil['normalisasi'] as $kriteriaId => $nilai): ?>
                        Kriteria <?php echo $kriteriaId; ?>: <?php echo number_format($nilai, 2); ?><br>
                    <?php endforeach; ?>
                </td>
                <td>
                    <?php foreach ($hasil['preferensi'] as $kriteriaId => $nilai): ?>
                        Kriteria <?php echo $kriteriaId; ?>: <?php echo number_format($nilai, 2); ?><br>
                    <?php endforeach; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: <?php echo $tanggal; ?></p>
        <br><br><br>
        <p>(_____________________)</p>
        <p>Kepala Bagian</p>
    </div>
</body>
</html>