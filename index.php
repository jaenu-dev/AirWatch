<?php
require_once 'koneksi.php';

// redirect to welcome page if no location selected
if (!isset($_GET['lokasi']) || empty($_GET['lokasi'])) {
    header("Location: welcome.php");
    exit;
}

$selectedLocation = $_GET['lokasi'];

// Data Logic filtered by Location
$latestData = getLatestSensorDataByLocation($selectedLocation, 1); // Get latest 1 for specific location
$stats = getAirQualityStatsByLocation($selectedLocation);
$alertCount = countActiveAlerts(); // Global alerts, or could be filtered if table supported it
$activeAlerts = getActiveAlerts(); // Fetch alert details
$avgAQI = round($stats['avg_aqi'] ?? 0);

// --- AQI Tier Definitions & Styles (Global) ---
$aqiTiers = [
    ['name' => 'Baik', 'class' => 'good', 'color' => '#10B981', 'light_color' => '#A7F3D0', 'icon' => 'fa-smile', 'desc' => 'Udara segar dan bebas racun. Nikmati aktivitas luar ruangan tanpa khawatir.'],
    ['name' => 'Sedang', 'class' => 'moderate', 'color' => '#F59E0B', 'light_color' => '#FDE68A', 'icon' => 'fa-meh', 'desc' => 'Kualitas udara dapat diterima, namun orang yang sensitif mungkin mengalami gangguan ringan.'],
    ['name' => 'Tidak Sehat (S)', 'class' => 'poor', 'color' => '#F97316', 'light_color' => '#FED7AA', 'icon' => 'fa-frown-open', 'desc' => 'Pernapasan mungkin sedikit terganggu, terutama bagi mereka yang memiliki masalah pernapasan.'],
    ['name' => 'Tidak Sehat', 'class' => 'unhealthy', 'color' => '#EF4444', 'light_color' => '#FECACA', 'icon' => 'fa-frown', 'desc' => 'Berisiko bagi anak-anak, ibu hamil, dan lansia. Batasi aktivitas di luar ruangan.'],
    ['name' => 'Sangat Tdk Sehat', 'class' => 'severe', 'color' => '#8B5CF6', 'light_color' => '#DDD6FE', 'icon' => 'fa-dizzy', 'desc' => 'Paparan berkepanjangan dapat menyebabkan masalah kesehatan kronis. Hindari aktivitas luar.'],
    ['name' => 'Berbahaya', 'class' => 'hazardous', 'color' => '#7F1D1D', 'light_color' => '#FCA5A5', 'icon' => 'fa-skull-crossbones', 'desc' => 'Tingkat polusi berbahaya. Risiko kesehatan yang mengancam jiwa. Tetap di dalam ruangan.']
];

// --- Pollutant Reference Ranges ---
$pollutantRefs = [
    'pm25' => ['label' => 'PM2.5', 'unit' => 'µg/m³', 'ranges' => [12.0, 35.4, 55.4, 150.4, 250.4, 99999]],
    'pm10' => ['label' => 'PM10', 'unit' => 'µg/m³', 'ranges' => [54, 154, 254, 354, 424, 99999]],
    'o3'   => ['label' => 'O3',   'unit' => 'ppb',   'ranges' => [54, 70, 85, 105, 200, 99999]],
    'co'   => ['label' => 'CO',   'unit' => 'ppb',   'ranges' => [4400, 9400, 12400, 15400, 30400, 99999]],
    'so2'  => ['label' => 'SO2',  'unit' => 'ppb',   'ranges' => [35, 75, 185, 304, 604, 99999]],
    'no2'  => ['label' => 'NO2',  'unit' => 'ppb',   'ranges' => [53, 100, 360, 361, 649, 99999]],
    'aqi'  => ['label' => 'AQI',  'unit' => '',      'ranges' => [50, 100, 150, 200, 300, 99999]]
];

/**
 * Helper to get the correct tier for a pollutant value
 */
function getTierInfo($type, $val, $tiers, $refs) {
    if (!isset($refs[$type])) return $tiers[0];
    $ranges = $refs[$type]['ranges'];
    foreach ($ranges as $idx => $limit) {
        if ($val <= $limit) return $tiers[$idx];
    }
    return $tiers[count($tiers)-1];
}

// Summary Logic matching Standard AQI
$statusParams = getTierInfo('aqi', $avgAQI, $aqiTiers, $pollutantRefs);
$statusParams['message'] = $statusParams['desc']; // Map desc to message
$statusParams['status'] = $statusParams['name'];   // Map name to status
$statusParams['text_class'] = 'text-' . $statusParams['class'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AirWatch - <?php echo htmlspecialchars($selectedLocation); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    
    <!-- NAVIGATION -->
    <nav class="navbar-glass">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between w-100">
                <a href="welcome.php" class="brand-wrapper d-flex align-items-center gap-3">
                    <img src="assets/logo.png" alt="Logo" style="width: 55px; height: 55px; object-fit: contain;">
                    <span class="fs-4 fw-bold">AirWatch</span>
                </a>
                


                <div class="nav-links d-flex align-items-center">
                     <a href="welcome.php" class="btn-premium me-2">
                        <i class="fas fa-map-marker-alt"></i> Ganti Lokasi
                    </a>

                    <!-- Notification Icon -->
                    <a href="#" class="btn-premium btn-circle-premium me-2 position-relative" data-bs-toggle="modal" data-bs-target="#notificationModal" title="Pemberitahuan">
                        <i class="fas fa-bell"></i>
                        <?php if($alertCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem; padding: 0.35em 0.5em; border: 2px solid white;">
                                <?php echo $alertCount; ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <a href="#" class="btn-premium btn-circle-premium" data-bs-toggle="modal" data-bs-target="#aboutModal" title="Tentang AirWatch">
                        <i class="fas fa-info-circle"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- NEW DASHBOARD HERO -->
    <section class="hero-section-new">
        <div class="container pt-4">
            <div class="row g-4 mb-5">
                <!-- Left Column: Summary Card -->
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <div class="hero-summary-card" style="--card-gradient-start: <?php echo $statusParams['light_color']; ?>;">
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <i class="fas fa-location-dot text-danger"></i>
                            <h5 class="fw-bold m-0"><?php echo htmlspecialchars($selectedLocation); ?></h5>
                        </div>

                        <!-- Circular AQI Indicator -->
                        <div class="aqi-circle-container">
                            <div class="aqi-circle-outer" style="border-color: <?php echo $statusParams['color']; ?>33;">
                                <div class="aqi-circle-value" style="color: <?php echo $statusParams['color']; ?>;">
                                    <?php echo $avgAQI; ?>
                                </div>
                                <div class="aqi-circle-label">US AQI</div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="text-center mb-4">
                                <div class="badge px-4 py-2 rounded-pill" style="background: <?php echo $statusParams['color']; ?>22; color: <?php echo $statusParams['color']; ?>; border: 1px solid <?php echo $statusParams['color']; ?>44;">
                                    <h6 class="fw-bold m-0"><?php echo $statusParams['status']; ?></h6>
                                </div>
                            </div>
                            
                            <!-- AQI Scale Bar -->
                            <?php 
                                $markerPos = min(($avgAQI / 300) * 100, 100);
                            ?>
                            <div class="aqi-scale-container" style="width: 100%; margin-top: 1.5rem;">
                                <!-- Grid Labels -->
                                <div class="aqi-scale-labels" style="display: grid !important; grid-template-columns: repeat(4, 1fr) !important; width: 100% !important; margin-bottom: 6px;">
                                    <span style="font-size: 0.6rem; font-weight: 800; color: rgba(0,0,0,0.6); text-transform: uppercase; text-align: left;">Baik</span>
                                    <span style="font-size: 0.6rem; font-weight: 800; color: rgba(0,0,0,0.6); text-transform: uppercase; text-align: center;">Sedang</span>
                                    <span style="font-size: 0.6rem; font-weight: 800; color: rgba(0,0,0,0.6); text-transform: uppercase; text-align: center;">Buruk</span>
                                    <span style="font-size: 0.6rem; font-weight: 800; color: rgba(0,0,0,0.6); text-transform: uppercase; text-align: right;">Bahaya</span>
                                </div>
                                
                                <!-- Bar -->
                                <div class="aqi-bar-wrapper" style="position: relative; height: 8px; width: 100% !important; border-radius: 10px; background: linear-gradient(90deg, #10B981 0%, #FACC15 20%, #F97316 40%, #EF4444 60%, #A855F7 80%, #B91C1C 100%); box-shadow: inset 0 1px 2px rgba(0,0,0,0.1); margin-bottom: 6px;">
                                    <div class="aqi-marker" style="position: absolute; top: 50%; left: <?php echo $markerPos; ?>%; transform: translate(-50%, -50%); width: 18px; height: 18px; background: #FFFFFF; border: 3px solid <?php echo $statusParams['color']; ?>; border-radius: 50%; box-shadow: 0 0 10px rgba(0,0,0,0.2); z-index: 2; transition: left 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);"></div>
                                </div>
                                
                                <!-- Grid Values -->
                                <div class="aqi-scale-values" style="display: grid !important; grid-template-columns: repeat(6, 1fr) !important; width: 100% !important;">
                                    <span style="font-size: 0.65rem; font-weight: 700; color: rgba(0,0,0,0.5); text-align: left;">0</span>
                                    <span style="font-size: 0.65rem; font-weight: 700; color: rgba(0,0,0,0.5); text-align: center;">50</span>
                                    <span style="font-size: 0.65rem; font-weight: 700; color: rgba(0,0,0,0.5); text-align: center;">100</span>
                                    <span style="font-size: 0.65rem; font-weight: 700; color: rgba(0,0,0,0.5); text-align: center;">150</span>
                                    <span style="font-size: 0.65rem; font-weight: 700; color: rgba(0,0,0,0.5); text-align: center;">200</span>
                                    <span style="font-size: 0.65rem; font-weight: 700; color: rgba(0,0,0,0.5); text-align: right;">300</span>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <p class="small text-secondary mb-0">Diperbarui: <?php echo date('H:i'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Details Card -->
                <div class="col-lg-8">
                    <div class="hero-details-card" style="--card-gradient-start: <?php echo $statusParams['light_color']; ?>;">
                        <div class="polutan-header mb-4 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold m-0 text-dark">Polutan Udara</h6>
                        </div>

                        <div class="pollutant-mini-grid">
                            <?php
                            $miniPollutants = [
                                ['type' => 'pm25', 'label' => 'PM2.5', 'val' => round($stats['avg_pm25'] ?? 0), 'unit' => 'µg/m³'],
                                ['type' => 'pm10', 'label' => 'PM10', 'val' => round($stats['avg_pm10'] ?? 0), 'unit' => 'µg/m³'],
                                ['type' => 'co',   'label' => 'CO',   'val' => round($stats['avg_co'] ?? 0),   'unit' => 'ppb'],
                                ['type' => 'no2',  'label' => 'NO2',  'val' => round($stats['avg_no2'] ?? 0),  'unit' => 'ppb'],
                                ['type' => 'so2',  'label' => 'SO2',  'val' => round($stats['avg_so2'] ?? 0),  'unit' => 'ppb'],
                                ['type' => 'o3',   'label' => 'O3',   'val' => round($stats['avg_o3'] ?? 0),   'unit' => 'ppb'],
                            ];
                            foreach($miniPollutants as $mp):
                                $pStatus = getTierInfo($mp['type'], $mp['val'], $aqiTiers, $pollutantRefs);
                            ?>
                            <div class="pollutant-mini-box" style="border-top: 4px solid <?php echo $pStatus['color']; ?>;">
                                <div class="pollutant-mini-label"><?php echo $mp['label']; ?></div>
                                <div class="pollutant-mini-value" style="color: <?php echo $pStatus['color']; ?>;"><?php echo $mp['val']; ?></div>
                                <div class="pollutant-mini-unit"><?php echo $mp['unit']; ?></div>
                                <div class="mt-1" style="font-size: 0.65rem; font-weight: 700; color: <?php echo $pStatus['color']; ?>; text-transform: uppercase;">
                                    <?php echo $pStatus['name']; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            </div>
        </div>
    </section>

    <!-- MAIN CONTENT OVERLAP -->
    <div class="container content-wrapper">
        
        <!-- HEALTH ADVISORY SECTION (Replaced Major Pollutants for cleaner look) -->


        <!-- MAP & CHARTS ROW removed as per user request -->


        <!-- Detail Data Section -->
        <!-- AQI SCALE REFERENCE SECTION (New Design - Dynamic) -->
            <!-- Pollutant Tabs Generated via PHP -->
            <div class="nav nav-pills nav-fill p-1 rounded-pill bg-white shadow-sm border border-light mb-4" id="pills-tab" role="tablist">
                <?php 
                // Display versions of ranges for the tabs
                $displayRanges = [
                    'aqi'   => ['0 - 50', '51 - 100', '101 - 150', '151 - 200', '201 - 300', '300+'],
                    'pm25'  => ['0 - 12.0', '12.1 - 35.4', '35.5 - 55.4', '55.5 - 150.4', '150.5 - 250.4', '250.5+'],
                    'pm10'  => ['0 - 54', '55 - 154', '155 - 254', '255 - 354', '355 - 424', '425+'],
                    'o3'    => ['0 - 54', '55 - 70', '71 - 85', '86 - 105', '106 - 200', '200+'],
                    'co'    => ['0 - 4400', '4401 - 9400', '9401 - 12400', '12401 - 15400', '15401 - 30400', '30401+'],
                    'so2'   => ['0 - 35', '36 - 75', '76 - 185', '186 - 304', '305 - 604', '605+'],
                    'no2'   => ['0 - 53', '54 - 100', '101 - 360', '361 - 649', '650 - 1249', '1250+'],
                ];

                $isFirst = true;
                foreach ($pollutantRefs as $key => $data): 
                    $activeClass = $isFirst ? 'active' : '';
                    $btnTextClass = 'text-dark';
                ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill <?php echo $activeClass . ' ' . $btnTextClass; ?> small fw-bold" 
                            id="pills-<?php echo $key; ?>-tab" 
                            data-bs-toggle="pill" 
                            data-bs-target="#pills-<?php echo $key; ?>" 
                            type="button" 
                            role="tab" 
                            aria-controls="pills-<?php echo $key; ?>" 
                            aria-selected="<?php echo $isFirst ? 'true' : 'false'; ?>">
                        <?php echo $data['label']; ?>
                    </button>
                </li>
                <?php 
                    $isFirst = false;
                endforeach; 
                ?>
            </div>

            <!-- Tab Content Panes Generated via PHP -->
            <div class="tab-content" id="pills-tabContent">
                <?php 
                $isFirstContent = true;
                foreach ($pollutantRefs as $key => $data): 
                    $paneClass = $isFirstContent ? 'show active' : '';
                    $unit = $data['unit'] ? "({$data['unit']})" : "";
                ?>
                <div class="tab-pane fade <?php echo $paneClass; ?>" id="pills-<?php echo $key; ?>" role="tabpanel" tabindex="0">
                    <div class="d-flex flex-column gap-3">
                        <?php 
                        foreach ($aqiTiers as $index => $tier): 
                            $range = $displayRanges[$key][$index] ?? 'N/A';
                            $colorClass = $tier['class'];
                            $bgClass = "bg-" . $colorClass;
                        ?>
                        <div class="card border-0 shadow-sm position-relative overflow-hidden" style="background: var(--brand-white); border: 1px solid var(--border-glass);">
                            <div class="position-absolute top-0 bottom-0 start-0 <?php echo $bgClass; ?>" style="width: 6px;"></div>
                            
                            <div class="card-body d-flex align-items-center p-4 ms-2 flex-wrap">
                                <div class="d-flex align-items-center me-4 mb-2 mb-md-0" style="min-width: 220px;">
                                    <div>
                                        <h5 class="fw-bold mb-0 text-dark">
                                            <?php echo $tier['name']; ?>
                                            <?php if(isset($tier['sub_name'])): ?>
                                                <span class="small text-muted fw-normal" style="font-size: 0.7em"><?php echo $tier['sub_name']; ?></span>
                                            <?php endif; ?>
                                        </h5>
                                        <small class="text-muted"><?php echo $range . ' ' . $unit; ?></small>
                                    </div>
                                </div>
                                <div class="flex-grow-1 text-secondary border-start border-dark border-opacity-10 ps-md-4 py-1">
                                    <?php echo $tier['desc']; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php 
                    $isFirstContent = false;
                endforeach; 
                ?>
            </div>

        </div> <!-- End Main Container -->

    </div>

    <!-- NOTIFICATION MODAL -->
    <div class="modal fade" id="notificationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background-color: var(--brand-white); color: var(--brand-dark);">
                <div class="modal-header border-0 p-4 pb-0">
                    <div>
                        <h4 class="fw-bold mb-1 text-danger"><i class="fas fa-bell me-2"></i>Pemberitahuan</h4>
                        <p class="text-secondary small mb-0">Informasi peringatan kualitas udara terbaru</p>
                    </div>
                    <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" style="max-height: 450px; overflow-y: auto;">
                    <?php if (empty($activeAlerts)): ?>
                        <div class="text-center py-5">
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                                <i class="fas fa-check-circle text-success fs-2"></i>
                            </div>
                            <h6 class="fw-bold text-dark">Tidak Ada Peringatan</h6>
                            <p class="text-muted small">Saat ini kualitas udara terpantau aman.</p>
                        </div>
                    <?php else: ?>
                        <div class="alert-list">
                            <?php foreach ($activeAlerts as $alert): 
                                $isDanger = ($alert['tingkat_bahaya'] == 'Bahaya');
                                $accentColor = $isDanger ? '#ef4444' : '#f59e0b';
                                $bgAlpha = $isDanger ? '0.05' : '0.08';
                                $badgeClass = $isDanger ? 'bg-danger' : 'bg-warning text-dark';
                                $iconClass = $isDanger ? 'fa-triangle-exclamation' : 'fa-circle-exclamation';
                            ?>
                                <div class="alert-item p-3 mb-3 rounded-3 border-start border-4 shadow-sm" 
                                     style="background: <?php echo $accentColor; echo $isDanger ? '0d' : '15'; ?>; border-color: <?php echo $accentColor; ?> !important;">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-bold mb-0" style="color: <?php echo $accentColor; ?>;"><?php echo htmlspecialchars($alert['pesan']); ?></h6>
                                        <small class="text-muted"><?php echo date('H:i', strtotime($alert['created_at'])); ?></small>
                                    </div>
                                    <p class="text-secondary small mb-2"><?php echo htmlspecialchars($alert['keterangan'] ?? 'Terdeteksi kondisi udara yang memerlukan perhatian di lokasi proyek.'); ?></p>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge <?php echo $badgeClass; ?> rounded-pill" style="font-size: 0.65rem;">
                                            <i class="fas <?php echo $iconClass; ?> me-1"></i> <?php echo $alert['tingkat_bahaya']; ?>
                                        </span>
                                        <small class="text-muted" style="font-size: 0.7rem;">
                                            <i class="fas fa-calendar-alt me-1"></i> <?php echo date('d M Y', strtotime($alert['created_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light w-100 rounded-pill fw-bold py-2" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ABOUT MODAL -->
    <div class="modal fade" id="aboutModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background-color: var(--brand-white); color: var(--brand-dark);">
                <div class="modal-header border-0 p-4 pb-0">
                    <div>
                        <h3 class="fw-bold mb-1 text-primary">Tentang AirWatch</h3>
                        <p class="text-secondary small mb-0">Sistem Monitoring Kualitas Udara Yogyakarta</p>
                    </div>
                    <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-5" style="background-color: var(--brand-white);">
                    <div class="row g-4">
                        <div class="col-md-7">
                            <h5 class="fw-bold text-dark mb-3">Visi & Misi</h5>
                            <p class="text-muted mb-4" style="line-height: 1.6;">
                                AirWatch hadir untuk memberikan visibilitas terhadap kualitas udara di sekitar kita. 
                                Dengan data real-time dari sensor yang tersebar di titik strategis Yogyakarta, 
                                kami membantu masyarakat mengambil keputusan yang lebih sehat.
                            </p>
                            
                            <h5 class="fw-bold text-dark mb-3">Tim Pengembang</h5>
                            <div class="d-flex align-items-center gap-3 p-3 rounded-3 shadow-sm border border-light" style="background-color: var(--bg-body);">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2">
                                    <i class="fas fa-code"></i>
                                </div>
                                <div>
                                    <small class="d-block text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Created By</small>
                                    <span class="fw-bold text-dark">Kelompok 3 - Sangkala Network</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="p-4 rounded-4 shadow-sm border border-light h-100 text-center" style="background-color: var(--bg-body);">
                                <h6 class="fw-bold text-muted mb-4">Statistik Sistem</h6>
                                
                                <div class="mb-4">
                                    <h2 class="display-4 fw-bold text-primary mb-0">5</h2>
                                    <small class="text-uppercase fw-bold text-muted">Titik Sensor</small>
                                </div>
                                
                                <div>
                                    <h2 class="display-4 fw-bold text-success mb-0">24/7</h2>
                                    <small class="text-uppercase fw-bold text-muted">Real-time Data</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 justify-content-center" style="background-color: var(--brand-white);">
                    <small class="text-muted">&copy;AirWatch System</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="py-5 mt-5">
        <div class="container text-center">
            <p class="mb-0 text-muted">&copy; AirWatch System. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <script>
        // --- 1. INITIALIZE MAP (Yogyakarta) ---
        var map = L.map('map').setView([-7.7956, 110.3695], 12); // Center of Jogja

        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 19
        }).addTo(map);

        // Custom Icon
        var sensorIcon = L.divIcon({
            className: 'custom-div-icon',
            html: "<div style='background-color:#E11D48;width:12px;height:12px;border-radius:50%;border:2px solid white;box-shadow:0 0 10px rgba(225,29,72,0.5);'></div>",
            iconSize: [15, 15],
            iconAnchor: [7, 7]
        });

        // Function to determine color based on AQI
        function getAqiColor(aqi) {
            if(aqi <= 50) return '#10B981'; // Good
            if(aqi <= 100) return '#F59E0B'; // Moderate
            if(aqi <= 150) return '#F97316'; // Unhealthy for Sensitive
            return '#EF4444'; // Unhealthy+
        }

        // Add Markers from PHP Data
        var locations = <?php echo json_encode($latestData); ?>;
        
        locations.forEach(function(loc) {
            // Random small offset for demo (since DB might not have coords)
            // IN REAL APP: Use loc.latitude and loc.longitude
            var lat = -7.7956 + (Math.random() - 0.5) * 0.1; 
            var lng = 110.3695 + (Math.random() - 0.5) * 0.1;
            
            // If you have strict coords for Sleman, Bantul, etc hardcode them here logic-wise or in DB
            // Demo mapping for Names to Approx Coords
            if(loc.lokasi.includes("Sleman")) { lat = -7.7137; lng = 110.3551; }
            if(loc.lokasi.includes("Bantul")) { lat = -7.8927; lng = 110.3220; }
            if(loc.lokasi.includes("Kaliurang")) { lat = -7.5976; lng = 110.4285; }
            if(loc.lokasi.includes("Godean")) { lat = -7.7690; lng = 110.2930; }
            if(loc.lokasi.includes("Condong")) { lat = -7.7600; lng = 110.4098; }

            var color = getAqiColor(loc.aqi);
            
            var customIcon = L.divIcon({
                className: 'custom-marker',
                html: `<div style="background-color:${color};width:16px;height:16px;border-radius:50%;border:3px solid white;box-shadow:0 4px 10px rgba(0,0,0,0.2);"></div>`,
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            });

            L.marker([lat, lng], {icon: customIcon})
                .addTo(map)
                .bindPopup(`<b>${loc.lokasi}</b><br>AQI: <strong style="color:${color}">${loc.aqi}</strong><br>${loc.status_kualitas}`);
        });


        // --- 2. INITIALIZE CHART (Simulated Trends) ---
        const ctx = document.getElementById('trendChart').getContext('2d');
        
        // Generate last 24h labels
        const labels = [];
        for(let i=23; i>=0; i--) {
            labels.push(i + ":00");
        }

        // Dummy data generator
        function generateData(base) {
            let data = [];
            for(let i=0; i<24; i++) {
                let change = Math.floor(Math.random() * 20) - 10;
                let val = base + change;
                if(val < 0) val = 10;
                data.push(val);
            }
            return data;
        }

        const gradientFill = ctx.createLinearGradient(0, 0, 0, 300);
        gradientFill.addColorStop(0, 'rgba(26, 92, 255, 0.2)');
        gradientFill.addColorStop(1, 'rgba(26, 92, 255, 0)');

        const myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Rata-rata AQI (24 Jam)',
                    data: generateData(75),
                    borderColor: '#1A5CFF',
                    backgroundColor: gradientFill,
                    borderWidth: 3,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#0F172A',
                        bodyColor: '#1A5CFF',
                        borderColor: '#E2E8F0',
                        borderWidth: 1,
                        padding: 10,
                        titleFont: { weight: 'bold' }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { maxTicksLimit: 6 } },
                    y: { border: { display: false }, grid: { borderDash: [5, 5] } }
                }
            }
        });

        // Interactive changing of chart based on dropdown
        document.getElementById('locationSelect').addEventListener('change', function(e) {
            let baseAQI = 75; // Default for 'all'
            // Simple logic to change graph pattern
            if(this.value.includes("Kaliurang")) baseAQI = 40;
            if(this.value.includes("Sleman")) baseAQI = 110;
            
            myChart.data.datasets[0].data = generateData(baseAQI);
            myChart.data.datasets[0].label = this.value === 'all' ? 'Rata-rata AQI' : 'AQI ' + this.value;
            myChart.update();
        });
    </script>
</body>
</html>