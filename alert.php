<?php
require_once 'koneksi.php';
$alerts = getActiveAlerts(50);
$alertCount = count($alerts);
$countBahaya = 0;
foreach($alerts as $a) {
    if($a['tingkat_bahaya'] == 'Bahaya' || $a['tingkat_bahaya'] == 'Sangat Bahaya') $countBahaya++;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peringatan - AirWatch</title>
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
                    <a href="data.php" class="nav-link-item">Data Lengkap</a>
                    <a href="alert.php" class="nav-link-item active">
                        Peringatan
                        <?php if($alertCount > 0): ?><span class="nav-badge"><?php echo $alertCount; ?></span><?php endif; ?>
                    </a>
                    <a href="about.php" class="nav-link-item">Tentang</a>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- HERO TITLE Danger -->
    <div class="bg-danger text-white py-5" style="background: linear-gradient(135deg, #FF3B30, #991B1B);">
        <div class="container text-center">
            <i class="fas fa-exclamation-triangle fa-3x mb-3 opacity-50"></i>
            <h1 class="fw-bold">Peringatan Kualitas Udara</h1>
            <p class="mb-0 opacity-75">Notifikasi bahaya aktif dan status kewaspadaan</p>
        </div>
    </div>

    <div class="container my-5">
        
        <div class="row g-4">
            <div class="col-lg-8">
                 <h4 class="fw-bold mb-3">Daftar Peringatan Aktif</h4>
                 
                 <?php if(count($alerts) > 0): ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach($alerts as $alert): 
                             $isDanger = ($alert['tingkat_bahaya'] == 'Bahaya' || $alert['tingkat_bahaya'] == 'Sangat Bahaya');
                        ?>
                        <div class="glass-panel p-4 border-start border-<?php echo $isDanger ? 'danger' : 'warning'; ?> border-5">
                            <div class="d-flex justify-content-between">
                                <span class="badge bg-<?php echo $isDanger ? 'danger' : 'warning text-dark'; ?> mb-2"><?php echo $alert['tingkat_bahaya']; ?></span>
                                <small class="text-muted"><?php echo formatTanggalIndonesia($alert['created_at']); ?></small>
                            </div>
                            <h5 class="fw-bold mt-1"><?php echo htmlspecialchars($alert['pesan']); ?></h5>
                            <div class="text-secondary small mt-2">
                                <i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($alert['lokasi']); ?> &bullet; 
                                Nilai: <strong><?php echo $alert['nilai']; ?></strong>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                 <?php else: ?>
                    <div class="glass-panel p-5 text-center">
                        <i class="fas fa-clipboard-check text-success fa-3x mb-3"></i>
                        <h4>Semua Aman</h4>
                        <p class="text-muted">Tidak ada peringatan aktif saat ini.</p>
                    </div>
                 <?php endif; ?>
            </div>
            
            <div class="col-lg-4">
                <div class="glass-panel text-center">
                    <h1 class="display-3 fw-bold text-danger mb-0"><?php echo $countBahaya; ?></h1>
                    <p class="text-danger fw-bold">Bahaya Aktif</p>
                    <hr>
                    <p class="small text-muted mb-0">Jumlah peringatan level 'Bahaya' atau 'Sangat Bahaya' di seluruh sistem.</p>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>