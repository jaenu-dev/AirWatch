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
$avgAQI = round($stats['avg_aqi'] ?? 0);

// Summary Logic matching Standard AQI
$statusParams = [
    'class' => 'hero-bg-good',
    'status' => 'Good',
    'message' => 'Air quality is satisfactory, and air pollution poses little or no risk.',
    'icon' => 'fa-smile',
    'text_class' => 'text-good'
];

if($avgAQI > 50) {
    $statusParams = [
        'class' => 'hero-bg-moderate',
        'status' => 'Moderate',
        'message' => 'Air quality is acceptable. However, there may be a risk for some people.',
        'icon' => 'fa-meh',
        'text_class' => 'text-moderate'
    ];
}
if($avgAQI > 100) {
    $statusParams = [
        'class' => 'hero-bg-poor',
        'status' => 'Poor',
        'message' => 'Members of sensitive groups may experience health effects.',
        'icon' => 'fa-frown-open',
        'text_class' => 'text-poor'
    ];
}
if($avgAQI > 150) {
    $statusParams = [
        'class' => 'hero-bg-unhealthy',
        'status' => 'Unhealthy',
        'message' => 'Everyone may begin to experience health effects.',
        'icon' => 'fa-frown',
        'text_class' => 'text-unhealthy'
    ];
}
if($avgAQI > 200) {
    $statusParams = [
        'class' => 'hero-bg-severe',
        'status' => 'Severe',
        'message' => 'Health warnings of emergency conditions. The entire population is more likely to be affected.',
        'icon' => 'fa-dizzy',
        'text_class' => 'text-severe'
    ];
}
if($avgAQI > 300) {
    $statusParams = [
        'class' => 'hero-bg-hazardous',
        'status' => 'Hazardous',
        'message' => 'Health alert: everyone may experience more serious health effects.',
        'icon' => 'fa-skull-crossbones',
        'text_class' => 'text-hazardous'
    ];
}
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
                <a href="welcome.php" class="brand-wrapper">
                    <span>AirWatch</span>
                </a>
                


                <div class="nav-links">
                     <a href="welcome.php" class="nav-link-item small me-3">
                        <i class="fas fa-map-marker-alt me-1"></i> Ganti Lokasi
                    </a>
                    <a href="#" class="nav-link-item" data-bs-toggle="modal" data-bs-target="#aboutModal" title="Tentang AirWatch">
                        <i class="fas fa-info-circle fa-lg"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- NEW DASHBOARD HERO -->
    <section class="hero-section-new">
        <div class="container pt-4">
            <div class="dashboard-card <?php echo $statusParams['class']; ?>">
                <!-- Header Info -->
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <h2 class="fw-bold mb-1">Real-time Air Quality Index (AQI)</h2>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-location-arrow small"></i>
                            <span class="fw-bold text-decoration-underline"><?php echo htmlspecialchars($selectedLocation); ?>, Yogyakarta</span>
                        </div>
                        <small class="opacity-75">Last Updated: <?php echo date('d M Y, H:i'); ?></small>
                    </div>
                </div>

                <div class="row align-items-center justify-content-center">
                    <!-- Left: AQI Big Display -->
                    <div class="col-lg-7 mb-4 mb-lg-0">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-danger rounded-circle p-1"></span>
                            <span class="fw-bold text-uppercase small" style="opacity:0.9; font-size: 0.8rem;">Live AQI</span>
                        </div>
                        
                        <div class="d-flex align-items-center gap-3">
                            <h1 class="display-1 fw-bold m-0" style="font-size: 5rem; letter-spacing: -2px;"><?php echo $avgAQI; ?></h1>
                            <div class="bg-white bg-opacity-25 px-3 py-2 rounded-3 text-center" style="min-width: 140px;">
                                <small class="d-block text-uppercase fw-bold opacity-75" style="font-size: 0.7rem;">Status</small>
                                <h4 class="fw-bold m-0"><?php echo $statusParams['status']; ?></h4>
                            </div>
                        </div>
                        <p class="mt-3 fs-6 opacity-90" style="max-width: 90%;">
                            <i class="fas <?php echo $statusParams['icon']; ?> me-2"></i>
                            <?php echo $statusParams['message']; ?>
                        </p>

                        <!-- Pollutants Grid -->
                        <div class="row mt-4 border-top border-white border-opacity-25 pt-3 g-4">
                            <div class="col-auto">
                                <small class="text-uppercase fw-bold opacity-75 d-block text-warning" style="font-size: 0.7rem;">PM2.5</small>
                                <span class="h4 fw-bold"><?php echo round($stats['avg_pm25'] ?? 0); ?></span> <small class="opacity-75">µg/m³</small>
                            </div>
                            <div class="col-auto ps-4 border-start border-white border-opacity-10">
                                <small class="text-uppercase fw-bold opacity-75 d-block text-warning" style="font-size: 0.7rem;">PM10</small>
                                <span class="h4 fw-bold"><?php echo round(($stats['avg_pm25'] ?? 0) * 1.8); ?></span> <small class="opacity-75">µg/m³</small>
                            </div>
                            <!-- Added standard gas parameters if available in DB logic later, for now placeholders to fill space if needed, else plain -->
                            <div class="col-auto ps-4 border-start border-white border-opacity-10">
                                <small class="text-uppercase fw-bold opacity-75 d-block" style="font-size: 0.7rem;">Suhu</small>
                                <span class="h4 fw-bold"><?php echo round($stats['avg_suhu'] ?? 0); ?></span> <small class="opacity-75">°C</small>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Illustration (Icon) -->
                    <div class="col-lg-5 text-center position-relative">
                        <div class="illustration-circle">
                            <i class="fas <?php echo $statusParams['icon']; ?> fa-8x illustration-icon" style="filter: drop-shadow(0 10px 20px rgba(0,0,0,0.2));"></i>
                        </div>
                    </div>
                </div>

                <!-- AQI Color Scale Bar -->
                <div class="aqi-scale-container mt-5">
                    <div class="d-flex justify-content-between mb-1 small fw-bold opacity-75">
                        <span>Good</span>
                        <span>Moderate</span>
                        <span>Unhealthy</span>
                        <span>Hazardous</span>
                    </div>
                    <div class="aqi-scale-bar position-relative">
                        <!-- Marker Position Logic: (AQI / 300) * 100 -->
                        <?php $markerPos = min(($avgAQI / 300) * 100, 100); ?>
                        <div class="aqi-marker" style="left: <?php echo $markerPos; ?>%;">
                            <div class="marker-dot"></div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-1 small opacity-50 font-monospace">
                        <span>0</span>
                        <span>50</span>
                        <span>100</span>
                        <span>150</span>
                        <span>200</span>
                        <span>300+</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- MAIN CONTENT OVERLAP -->
    <div class="container content-wrapper">
        
        <!-- Health Insights Grid -->
        <!-- MAJOR POLLUTANTS SECTION -->
        <h5 class="fw-bold mb-3 text-secondary ps-2">Major Air Pollutants</h5>
        <div class="row g-4 mb-5">
            <!-- Row 1: PM2.5, PM10, CO -->
            <div class="col-md-4">
                <div class="pollutant-card" style="border-left-color: #10B981;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="opacity-50"><i class="fas fa-smog fa-2x"></i></div>
                        <div class="pollutant-info">
                            <h5>Particulate Matter</h5>
                            <h3>PM2.5</h3>
                        </div>
                    </div>
                    <div class="pollutant-value">
                        <span class="value"><?php echo round($stats['avg_pm25'] ?? 0); ?></span>
                        <span class="unit">µg/m³</span>
                    </div>
                </div>
            </div>

             <div class="col-md-4">
                <div class="pollutant-card" style="border-left-color: #10B981;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="opacity-50"><i class="fas fa-wind fa-2x"></i></div>
                        <div class="pollutant-info">
                            <h5>Particulate Matter</h5>
                            <h3>PM10</h3>
                        </div>
                    </div>
                    <div class="pollutant-value">
                        <span class="value"><?php echo round(($stats['avg_pm10'] ?? 0)); ?></span>
                        <span class="unit">µg/m³</span>
                    </div>
                </div>
            </div>

             <div class="col-md-4">
                <div class="pollutant-card" style="border-left-color: #10B981;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="opacity-50"><i class="fas fa-burn fa-2x"></i></div>
                        <div class="pollutant-info">
                            <h5>Carbon Monoxide</h5>
                            <h3>CO</h3>
                        </div>
                    </div>
                    <div class="pollutant-value">
                        <span class="value"><?php echo round(($stats['avg_co'] ?? 0)); ?></span>
                        <span class="unit">ppb</span>
                    </div>
                </div>
            </div>

            <!-- Row 2: SO2, NO2, O3 -->
             <div class="col-md-4">
                <div class="pollutant-card" style="border-left-color: #10B981;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="opacity-50"><i class="fas fa-industry fa-2x"></i></div>
                        <div class="pollutant-info">
                            <h5>Sulfur Dioxide</h5>
                            <h3>SO2</h3>
                        </div>
                    </div>
                    <div class="pollutant-value">
                        <span class="value"><?php echo round(($stats['avg_so2'] ?? 0)); ?></span>
                        <span class="unit">ppb</span>
                    </div>
                </div>
            </div>

             <div class="col-md-4">
                <div class="pollutant-card" style="border-left-color: #10B981;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="opacity-50"><i class="fas fa-flask fa-2x"></i></div>
                        <div class="pollutant-info">
                            <h5>Nitrogen Dioxide</h5>
                            <h3>NO2</h3>
                        </div>
                    </div>
                    <div class="pollutant-value">
                        <span class="value"><?php echo round(($stats['avg_no2'] ?? 0)); ?></span>
                        <span class="unit">ppb</span>
                    </div>
                </div>
            </div>

             <div class="col-md-4">
                <div class="pollutant-card" style="border-left-color: #10B981;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="opacity-50"><i class="fas fa-cloud fa-2x"></i></div>
                        <div class="pollutant-info">
                            <h5>Ozone</h5>
                            <h3>O3</h3>
                        </div>
                    </div>
                    <div class="pollutant-value">
                        <span class="value"><?php echo round(($stats['avg_o3'] ?? 0)); ?></span>
                        <span class="unit">ppb</span>
                    </div>
                </div>
            </div>
        </div>


        <!-- MAP & CHARTS ROW removed as per user request -->


        <!-- Detail Data Section -->
        <!-- AQI SCALE REFERENCE SECTION -->
        <div class="mb-5">
            <h4 class="fw-bold mb-1 text-white">Air Quality Index (AQI) Scale</h4>
            <p class="text-secondary mb-4">Know about the category of air quality index (AQI) your ambient air falls in and what it implies.</p>
            
            <div class="row g-3">
                <!-- Good -->
                <div class="col-12">
                    <div class="aqi-ref-card aqi-ref-good">
                        <div class="aqi-square-icon bg-good"></div>
                        <div class="row w-100 align-items-center">
                            <div class="col-md-3 aqi-ref-text">
                                <h5>Good</h5>
                                <small>(0 to 50)</small>
                            </div>
                            <div class="col-md-8 aqi-ref-desc">
                                The air is fresh and free from toxins. Enjoy outdoor activities without any health concerns.
                            </div>
                            <div class="col-md-1 text-end">
                                <i class="fas fa-smile fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Moderate -->
                <div class="col-12">
                    <div class="aqi-ref-card aqi-ref-moderate">
                        <div class="aqi-square-icon bg-moderate"></div>
                        <div class="row w-100 align-items-center">
                            <div class="col-md-3 aqi-ref-text">
                                <h5>Moderate</h5>
                                <small>(51 to 100)</small>
                            </div>
                            <div class="col-md-8 aqi-ref-desc">
                                Air quality is acceptable for most, but sensitive individuals might experience mild discomfort.
                            </div>
                            <div class="col-md-1 text-end">
                                <i class="fas fa-meh fa-2x text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Poor (Using simple orange for spacing between Mod and Unhealthy) -->
                <div class="col-12">
                    <div class="aqi-ref-card aqi-ref-poor">
                        <div class="aqi-square-icon bg-poor"></div>
                        <div class="row w-100 align-items-center">
                            <div class="col-md-3 aqi-ref-text">
                                <h5>Poor</h5>
                                <small>(101 to 150)</small>
                            </div>
                            <div class="col-md-8 aqi-ref-desc">
                                Breathing may become slightly uncomfortable, especially for those with respiratory issues.
                            </div>
                            <div class="col-md-1 text-end">
                                <i class="fas fa-frown fa-2x" style="color: #F97316;"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Unhealthy -->
                <div class="col-12">
                    <div class="aqi-ref-card aqi-ref-unhealthy">
                        <div class="aqi-square-icon bg-unhealthy"></div>
                        <div class="row w-100 align-items-center">
                            <div class="col-md-3 aqi-ref-text">
                                <h5>Unhealthy</h5>
                                <small>(151 to 200)</small>
                            </div>
                            <div class="col-md-8 aqi-ref-desc">
                                This air quality is particularly risky for children, pregnant women, and the elderly. Limit outdoor activities.
                            </div>
                            <div class="col-md-1 text-end">
                                <i class="fas fa-mask fa-2x text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Severe -->
                <div class="col-12">
                    <div class="aqi-ref-card aqi-ref-severe">
                        <div class="aqi-square-icon bg-severe"></div>
                        <div class="row w-100 align-items-center">
                            <div class="col-md-3 aqi-ref-text">
                                <h5>Severe</h5>
                                <small>(201 to 300)</small>
                            </div>
                            <div class="col-md-8 aqi-ref-desc">
                                Prolonged exposure can cause chronic health issues or organ damage. Avoid outdoor activities.
                            </div>
                            <div class="col-md-1 text-end">
                                <i class="fas fa-skull-crossbones fa-2x" style="color: #A855F7;"></i>
                            </div>
                        </div>
                    </div>
                </div>

                 <!-- Hazardous -->
                <div class="col-12">
                    <div class="aqi-ref-card aqi-ref-hazardous">
                        <div class="aqi-square-icon bg-hazardous"></div>
                        <div class="row w-100 align-items-center">
                            <div class="col-md-3 aqi-ref-text">
                                <h5>Hazardous</h5>
                                <small>(301+)</small>
                            </div>
                            <div class="col-md-8 aqi-ref-desc">
                                Dangerously high pollution levels. Life-threatening health risks with prolonged exposure. Stay indoors and take precautions.
                            </div>
                            <div class="col-md-1 text-end">
                                <i class="fas fa-biohazard fa-2x" style="color: #B91C1C;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ABOUT MODAL -->
    <div class="modal fade" id="aboutModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background-color: #1E293B; color: white;">
                <div class="modal-header border-0 p-4 pb-0">
                    <div>
                        <h3 class="fw-bold mb-1 text-white">Tentang AirWatch</h3>
                        <p class="text-white-50 small mb-0">Sistem Monitoring Kualitas Udara Yogyakarta</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white m-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-5" style="background-color: #1E293B;">
                    <div class="row g-4">
                        <div class="col-md-7">
                            <h5 class="fw-bold text-white mb-3">Visi & Misi</h5>
                            <p class="text-white-50 mb-4" style="line-height: 1.6;">
                                AirWatch hadir untuk memberikan visibilitas terhadap kualitas udara di sekitar kita. 
                                Dengan data real-time dari sensor yang tersebar di titik strategis Yogyakarta, 
                                kami membantu masyarakat mengambil keputusan yang lebih sehat.
                            </p>
                            
                            <h5 class="fw-bold text-white mb-3">Tim Pengembang</h5>
                            <div class="d-flex align-items-center gap-3 p-3 rounded-3 shadow-sm border border-secondary" style="background-color: #0F172A;">
                                <div class="bg-primary bg-opacity-25 text-primary rounded-circle p-2">
                                    <i class="fas fa-code"></i>
                                </div>
                                <div>
                                    <small class="d-block text-white-50 text-uppercase fw-bold" style="font-size: 0.7rem;">Created By</small>
                                    <span class="fw-bold text-white">Kelompok 3 - Sangkala Network</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="p-4 rounded-4 shadow-sm border border-secondary h-100 text-center" style="background-color: #0F172A;">
                                <h6 class="fw-bold text-white-50 mb-4">Statistik Sistem</h6>
                                
                                <div class="mb-4">
                                    <h2 class="display-4 fw-bold text-primary mb-0">5</h2>
                                    <small class="text-uppercase fw-bold text-white-50">Titik Sensor</small>
                                </div>
                                
                                <div>
                                    <h2 class="display-4 fw-bold text-success mb-0">24/7</h2>
                                    <small class="text-uppercase fw-bold text-white-50">Real-time Data</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 justify-content-center" style="background-color: #1E293B;">
                    <small class="text-white-50">&copy;AirWatch System</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="py-4 mt-5 border-top border-secondary">
        <div class="container text-center">
            <p class="mb-0 text-white-50">&copy; AirWatch System. All rights reserved.</p>
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