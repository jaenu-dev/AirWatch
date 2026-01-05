<?php
require_once 'koneksi.php';

// Pagination & Filter Logic (Same as before)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 15;
$offset = ($page - 1) * $perPage;
$filterLokasi = isset($_GET['lokasi']) ? escape($_GET['lokasi']) : '';
$whereClause = "";
if ($filterLokasi != '') {
    $whereClause = "WHERE lokasi = '$filterLokasi'";
}
$countSql = "SELECT COUNT(*) as total FROM sensor_data $whereClause";
$countResult = $conn->query($countSql);
$totalData = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalData / $perPage);
$sql = "SELECT * FROM sensor_data $whereClause ORDER BY timestamp DESC LIMIT $perPage OFFSET $offset";
$result = $conn->query($sql);
$lokasiSql = "SELECT DISTINCT lokasi FROM sensor_data ORDER BY lokasi";
$lokasiResult = $conn->query($lokasiSql);
$alertCount = 0; // Disabled
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Lengkap - AirWatch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    
    <!-- NAVIGATION -->
    <nav class="navbar-glass">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between w-100">
                <a href="index.php" class="brand-wrapper">
                    <div class="brand-icon"><i class="fas fa-wind"></i></div>
                    <span>AirWatch</span>
                </a>
                
                <button class="mobile-menu-btn" type="button" aria-label="Toggle Menu">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="nav-links">
                    <a href="index.php" class="nav-link-item">Beranda</a>
                    <a href="data.php" class="nav-link-item active">Data Lengkap</a>
                    <a href="alert.php" class="nav-link-item">
                        Peringatan
                        <?php if($alertCount > 0): ?><span class="nav-badge"><?php echo $alertCount; ?></span><?php endif; ?>
                    </a>
                    <a href="about.php" class="nav-link-item">Tentang</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- HEADER TITLE -->
    <div class="bg-white border-bottom py-5">
        <div class="container">
            <h1 class="fw-bold mb-2">Data Pengukuran</h1>
            <p class="text-muted mb-0">Arsip data sensor kualitas udara real-time</p>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="container my-5">
        
        <!-- Controls -->
        <div class="glass-panel mb-4 p-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <form method="GET" action="data.php" class="d-flex gap-2">
                        <select name="lokasi" class="form-select w-auto" onchange="this.form.submit()">
                            <option value="">Semua Lokasi</option>
                            <?php while($lok = $lokasiResult->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($lok['lokasi']); ?>" <?php echo ($filterLokasi == $lok['lokasi']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($lok['lokasi']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </form>
                </div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-primary rounded-pill px-4" onclick="window.print()">
                        <i class="fas fa-print me-2"></i> Cetak Laporan
                    </button>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="table-panel">
            <div class="table-responsive">
                <table class="table modern-table mb-0">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Lokasi</th>
                            <th>Status AQI</th>
                            <th>Partikel (PM2.5)</th>
                            <th>Suhu / Lembap</th>
                            <th>Gas (CO/NO2)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): 
                                $badge = 'badge-good';
                                if($row['status_kualitas'] != 'Baik') $badge = 'badge-moderate';
                                if($row['status_kualitas'] == 'Tidak Sehat') $badge = 'badge-unhealthy';
                            ?>
                            <tr>
                                <td>
                                    <span class="d-block fw-bold text-dark"><?php echo date('d M Y', strtotime($row['timestamp'])); ?></span>
                                    <small class="text-muted"><?php echo date('H:i', strtotime($row['timestamp'])); ?> WIB</small>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['lokasi']); ?></strong>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $badge; ?> me-2"><?php echo htmlspecialchars($row['status_kualitas']); ?></span>
                                    <span class="fw-bold text-primary"><?php echo $row['aqi']; ?> AQI</span>
                                </td>
                                <td><?php echo $row['pm25']; ?> <small class="text-muted">µg/m³</small></td>
                                <td><?php echo $row['suhu']; ?>°C / <?php echo $row['kelembapan']; ?>%</td>
                                <td>
                                    <small class="d-block">CO: <?php echo $row['co']; ?></small>
                                    <small class="d-block">NO2: <?php echo $row['no2']; ?></small>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-5">Belum ada data</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="d-flex justify-content-center mt-4">
            <nav>
                <ul class="pagination">
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link rounded-start-pill" href="?page=<?php echo $page-1; ?>">Prev</a>
                    </li>
                    <li class="page-item active"><span class="page-link"><?php echo $page; ?></span></li>
                    <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                        <a class="page-link rounded-end-pill" href="?page=<?php echo $page+1; ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>