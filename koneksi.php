<?php
/**
 * =============================================
 * FILE KONEKSI DATABASE
 * Sistem Pemantauan Kualitas Udara
 * Klien: Dinas Lingkungan Hidup
 * =============================================
 */

// Konfigurasi database
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');      // Host database (auto-detect Docker or Localhost)
define('DB_USER', getenv('DB_USER') ?: 'root');           // Username database
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : ''); // Password database
define('DB_NAME', getenv('DB_NAME') ?: 'kualitas_udara'); // Nama database

// Koneksi ke database menggunakan MySQLi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Set charset ke UTF-8 untuk mendukung karakter Indonesia
$conn->set_charset("utf8mb4");

/**
 * Fungsi untuk mengambil data sensor terbaru
 * @param int $limit - Jumlah data yang diambil
 * @return array - Array data sensor
 */
function getLatestSensorData($limit = 10) {
    global $conn;
    // Query untuk mengambil data terakhir dari setiap lokasi unik
    $sql = "SELECT t1.* 
            FROM sensor_data t1
            JOIN (
                SELECT lokasi, MAX(id) as max_id
                FROM sensor_data
                GROUP BY lokasi
            ) t2 ON t1.id = t2.max_id
            ORDER BY t1.lokasi ASC
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Fungsi untuk mengambil alert aktif
 * @param int $limit - Jumlah alert yang diambil
 * @return array - Array alert
 */
function getActiveAlerts($limit = 20) {
    global $conn;
    $sql = "SELECT * FROM alerts WHERE status = 'aktif' ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Fungsi untuk menghitung jumlah alert aktif
 * @return int - Jumlah alert
 */
function countActiveAlerts() {
    global $conn;
    $sql = "SELECT COUNT(*) as total FROM alerts WHERE status = 'aktif'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'];
}

/**
 * Fungsi untuk mendapatkan statistik kualitas udara
 * @return array - Array statistik
 */
function getAirQualityStats() {
    global $conn;
    $sql = "SELECT 
                COUNT(DISTINCT lokasi) as total_lokasi,
                AVG(aqi) as avg_aqi,
                MAX(aqi) as max_aqi,
                MIN(aqi) as min_aqi,
                AVG(pm25) as avg_pm25,
                AVG(pm10) as avg_pm10,
                AVG(co) as avg_co,
                AVG(so2) as avg_so2,
                AVG(no2) as avg_no2,
                AVG(o3) as avg_o3,
                AVG(suhu) as avg_suhu,
                AVG(kelembapan) as avg_kelembapan
            FROM sensor_data 
            WHERE DATE(timestamp) = CURDATE()";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

/**
 * Fungsi untuk escape string (keamanan)
 * @param string $string - String yang akan di-escape
 * @return string - String yang aman
 */
function escape($string) {
    global $conn;
    return $conn->real_escape_string($string);
}

/**
 * Fungsi untuk format tanggal Indonesia
 * @param string $date - Tanggal dalam format MySQL
 * @return string - Tanggal dalam format Indonesia
 */
function formatTanggalIndonesia($date) {
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    $timestamp = strtotime($date);
    $hari = date('d', $timestamp);
    $bulan_num = date('n', $timestamp);
    $tahun = date('Y', $timestamp);
    $jam = date('H:i', $timestamp);
    
    return $hari . ' ' . $bulan[$bulan_num] . ' ' . $tahun . ' ' . $jam;
}

/**
 * Fungsi untuk mendapatkan warna badge berdasarkan status kualitas
 * @param string $status - Status kualitas udara
 * @return string - Class CSS Bootstrap
 */
function getBadgeColor($status) {
    $colors = [
        'Baik' => 'success',
        'Sedang' => 'primary',
        'Tidak Sehat untuk Kelompok Sensitif' => 'warning',
        'Tidak Sehat' => 'danger',
        'Sangat Tidak Sehat' => 'danger',
        'Berbahaya' => 'dark'
    ];
    return isset($colors[$status]) ? $colors[$status] : 'secondary';
}

/**
 * Fungsi untuk mendapatkan icon berdasarkan status
 * @param string $status - Status kualitas udara
 * @return string - Emoji icon
 */
function getStatusIcon($status) {
    $icons = [
        'Baik' => '😊',
        'Sedang' => '😐',
        'Tidak Sehat untuk Kelompok Sensitif' => '😷',
        'Tidak Sehat' => '😨',
        'Sangat Tidak Sehat' => '😱',
        'Berbahaya' => '☠️'
    ];
    return isset($icons[$status]) ? $icons[$status] : '❓';
}
/**
 * Fungsi untuk mendapatkan daftar lokasi unik
 * @return array
 */
function getAvailableLocations() {
    global $conn;
    $sql = "SELECT DISTINCT lokasi FROM sensor_data ORDER BY lokasi ASC";
    $result = $conn->query($sql);
    $locations = [];
    while($row = $result->fetch_assoc()) {
        $locations[] = $row['lokasi'];
    }
    return $locations;
}

/**
 * Fungsi untuk mendapatkan statistik berdasarkan lokasi
 * @param string $lokasi
 * @return array
 */
function getAirQualityStatsByLocation($lokasi) {
    global $conn;
    $lokasi = escape($lokasi);
    $sql = "SELECT 
                COUNT(id) as total_data,
                AVG(aqi) as avg_aqi,
                MAX(aqi) as max_aqi,
                MIN(aqi) as min_aqi,
                AVG(pm25) as avg_pm25,
                AVG(pm10) as avg_pm10,
                AVG(co) as avg_co,
                AVG(so2) as avg_so2,
                AVG(no2) as avg_no2,
                AVG(o3) as avg_o3,
                AVG(suhu) as avg_suhu,
                AVG(kelembapan) as avg_kelembapan
            FROM sensor_data 
            WHERE lokasi = '$lokasi' 
            AND DATE(timestamp) = CURDATE()";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

/**
 * Fungsi untuk mendapatkan data sensor terakhir per lokasi
 * @param string $lokasi
 * @return array
 */
function getLatestSensorDataByLocation($lokasi, $limit = 10) {
    global $conn;
    $lokasi = escape($lokasi);
    $sql = "SELECT * FROM sensor_data 
            WHERE lokasi = '$lokasi' 
            ORDER BY timestamp DESC 
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
// Helper function for AQI (PM2.5) matching user screenshot (Global)
function calculateAQI($pm25) {
    // Determine status based on PM2.5 (Standard AQI)
    // 0-12: Good
    // 12.1-35.4: Moderate
    // 35.5-55.4: Poor
    // 55.5-150.4: Unhealthy
    // 150.5-250.4: Severe
    // 250.5+: Hazardous
    
    $aqi = 0;
    $status = 'Good';

    if ($pm25 <= 12.0) {
        $aqi = ((50 - 0) / (12.0 - 0)) * ($pm25 - 0) + 0;
        $status = 'Good';
    } elseif ($pm25 <= 35.4) {
        $aqi = ((100 - 51) / (35.4 - 12.1)) * ($pm25 - 12.1) + 51;
        $status = 'Moderate';
    } elseif ($pm25 <= 55.4) {
        $aqi = ((150 - 101) / (55.4 - 35.5)) * ($pm25 - 35.5) + 101;
        $status = 'Poor'; 
    } elseif ($pm25 <= 150.4) {
        $aqi = ((200 - 151) / (150.4 - 55.5)) * ($pm25 - 55.5) + 151;
        $status = 'Unhealthy';
    } elseif ($pm25 <= 250.4) {
        $aqi = ((300 - 201) / (250.4 - 150.5)) * ($pm25 - 150.5) + 201;
        $status = 'Severe'; 
    } else {
        $aqi = ((500 - 301) / (350.4 - 250.5)) * ($pm25 - 250.5) + 301; 
        $status = 'Hazardous';
    }
    return ['aqi' => round($aqi), 'status' => $status];
}
?>
