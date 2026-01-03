<?php
require_once 'koneksi.php';
$alertCount = countActiveAlerts();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang - AirWatch</title>
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
                    <span>AirWatch</span>
                </a>
                
                <button class="mobile-menu-btn" type="button" aria-label="Toggle Menu">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="nav-links">
                    <a href="index.php" class="nav-link-item">Beranda</a>
                    <a href="about.php" class="nav-link-item active">Tentang</a>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- HERO SIMPLE -->
    <div class="bg-white border-bottom py-5 text-center">
        <div class="container">
            <img src="https://ui-avatars.com/api/?name=Air+Watch&background=1A5CFF&color=fff&size=128" class="rounded-4 shadow mb-4" width="80">
            <h1 class="fw-bold mb-2">Tentang AirWatch</h1>
            <p class="text-muted mb-0" style="max-width: 600px; margin: 0 auto;">
                Sistem monitoring kualitas udara pintar yang terintegrasi untuk Yogyakarta yang lebih sehat.
            </p>
        </div>
    </div>

    <div class="container my-5">
        <div class="row g-5">
            <div class="col-lg-6">
                <h4 class="fw-bold mb-4">Latar Belakang</h4>
                <p class="text-secondary" style="line-height: 1.8;">
                    Polusi udara merupakan masalah serius yang seringkali tidak kasat mata. AirWatch hadir untuk memberikan 
                    visibilitas terhadap kualitas udara di sekitar kita. Dengan data yang akurat dan real-time, kami berharap 
                    dapat membantu masyarakat mengambil langkah pencegahan yang tepat.
                </p>
                <div class="row g-3 mt-2">
                    <div class="col-6">
                        <div class="glass-panel p-3 text-center h-100">
                            <h2 class="fw-bold text-primary mb-1">5+</h2>
                            <small class="text-uppercase fw-bold text-muted">Lokasi Sensor</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="glass-panel p-3 text-center h-100">
                            <h2 class="fw-bold text-primary mb-1">24/7</h2>
                            <small class="text-uppercase fw-bold text-muted">Monitoring</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                 <h4 class="fw-bold mb-4">Tim Pengembang</h4>
                 <div class="glass-panel p-4">
                     <div class="d-flex align-items-center gap-3">
                         <div class="bg-primary text-white rounded-circle p-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                             <i class="fas fa-users fa-lg"></i>
                         </div>
                         <div>
                             <h5 class="fw-bold m-0">Kelompok 3</h5>
                             <p class="text-muted m-0 small">Teknologi Informasi</p>
                         </div>
                     </div>
                     <hr class="my-4">
                     <p class="small text-muted m-0">
                         Dikembangkan khusus untuk Dinas Lingkungan Hidup Daerah Istimewa Yogyakarta sebagai proyek percontohan Smart City.
                     </p>
                 </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>