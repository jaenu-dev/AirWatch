<?php
/**
 * =============================================
 * API ENDPOINT - POST SENSOR DATA
 * Menerima data dari sensor dan menyimpan ke database
 * =============================================
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../koneksi.php';

// Fungsi untuk menghitung AQI berdasarkan PM2.5
function calculateAQI($pm25, $pm10) {
    // Simplified AQI calculation based on PM2.5
    if ($pm25 <= 12.0) {
        $aqi = (50 - 0) / (12.0 - 0) * ($pm25 - 0) + 0;
        $status = "Baik";
    } elseif ($pm25 <= 35.4) {
        $aqi = (100 - 51) / (35.4 - 12.1) * ($pm25 - 12.1) + 51;
        $status = "Sedang";
    } elseif ($pm25 <= 55.4) {
        $aqi = (150 - 101) / (55.4 - 35.5) * ($pm25 - 35.5) + 101;
        $status = "Tidak Sehat untuk Kelompok Sensitif";
    } elseif ($pm25 <= 150.4) {
        $aqi = (200 - 151) / (150.4 - 55.5) * ($pm25 - 55.5) + 151;
        $status = "Tidak Sehat";
    } elseif ($pm25 <= 250.4) {
        $aqi = (300 - 201) / (250.4 - 150.5) * ($pm25 - 150.5) + 201;
        $status = "Sangat Tidak Sehat";
    } else {
        $aqi = (500 - 301) / (500.4 - 250.5) * ($pm25 - 250.5) + 301;
        $status = "Berbahaya";
    }
    
    return [
        'aqi' => round($aqi),
        'status' => $status
    ];
}

// Fungsi untuk membuat alert jika diperlukan
function createAlert($conn, $sensorDataId, $lokasi, $data) {
    $alerts = [];
    
    // Batas aman untuk setiap parameter
    $thresholds = [
        'pm25' => ['value' => 55.4, 'name' => 'PM2.5'],
        'pm10' => ['value' => 154, 'name' => 'PM10'],
        'co' => ['value' => 4.0, 'name' => 'CO'],
        'no2' => ['value' => 0.1, 'name' => 'NO₂'],
        'so2' => ['value' => 0.075, 'name' => 'SO₂'],
        'o3' => ['value' => 0.07, 'name' => 'O₃']
    ];
    
    foreach ($thresholds as $param => $threshold) {
        if ($data[$param] > $threshold['value']) {
            $tingkat = ($data[$param] > $threshold['value'] * 1.2) ? 'Bahaya' : 'Peringatan';
            $pesan = "Konsentrasi {$threshold['name']} melebihi batas aman! ";
            
            if ($tingkat == 'Bahaya') {
                $pesan .= "Hindari aktivitas outdoor dan gunakan masker.";
            } else {
                $pesan .= "Kelompok sensitif disarankan mengurangi aktivitas outdoor.";
            }
            
            $sql = "INSERT INTO alerts (sensor_data_id, lokasi, jenis_alert, nilai, batas_aman, tingkat_bahaya, pesan) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issddss", 
                $sensorDataId, 
                $lokasi, 
                $threshold['name'], 
                $data[$param], 
                $threshold['value'], 
                $tingkat, 
                $pesan
            );
            $stmt->execute();
            
            $alerts[] = [
                'jenis' => $threshold['name'],
                'tingkat' => $tingkat,
                'nilai' => $data[$param],
                'pesan' => $pesan
            ];
        }
    }
    
    return $alerts;
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Ambil data JSON dari request body
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Validasi data
    $required = ['lokasi', 'pm25', 'pm10', 'co', 'no2', 'so2', 'o3', 'suhu', 'kelembapan'];
    $missing = [];
    
    foreach ($required as $field) {
        if (!isset($data[$field])) {
            $missing[] = $field;
        }
    }
    
    if (count($missing) > 0) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Data tidak lengkap',
            'missing_fields' => $missing
        ]);
        exit;
    }
    
    // Hitung AQI dan status
    $aqiData = calculateAQI($data['pm25'], $data['pm10']);
    
    // Insert data ke database
    $sql = "INSERT INTO sensor_data (lokasi, pm25, pm10, co, no2, so2, o3, suhu, kelembapan, aqi, status_kualitas) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sddddddddis", 
        $data['lokasi'],
        $data['pm25'],
        $data['pm10'],
        $data['co'],
        $data['no2'],
        $data['so2'],
        $data['o3'],
        $data['suhu'],
        $data['kelembapan'],
        $aqiData['aqi'],
        $aqiData['status']
    );
    
    if ($stmt->execute()) {
        $sensorDataId = $conn->insert_id;
        
        // Cek dan buat alert jika diperlukan
        $alerts = createAlert($conn, $sensorDataId, $data['lokasi'], $data);
        
        http_response_code(201);
        echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil disimpan',
            'data' => [
                'id' => $sensorDataId,
                'lokasi' => $data['lokasi'],
                'aqi' => $aqiData['aqi'],
                'status_kualitas' => $aqiData['status'],
                'alerts_created' => count($alerts),
                'alerts' => $alerts
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal menyimpan data: ' . $conn->error
        ]);
    }
    
// Handle GET request (untuk testing)
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'API Endpoint aktif',
        'endpoints' => [
            'POST /api/post_data.php' => 'Mengirim data sensor baru',
            'GET /api/post_data.php' => 'Cek status API'
        ],
        'required_fields' => [
            'lokasi' => 'string',
            'pm25' => 'float',
            'pm10' => 'float',
            'co' => 'float',
            'no2' => 'float',
            'so2' => 'float',
            'o3' => 'float',
            'suhu' => 'float',
            'kelembapan' => 'float'
        ],
        'example_request' => [
            'lokasi' => 'Sleman',
            'pm25' => 45.5,
            'pm10' => 78.2,
            'co' => 2.3,
            'no2' => 0.08,
            'so2' => 0.05,
            'o3' => 0.06,
            'suhu' => 32.5,
            'kelembapan' => 68.0
        ]
    ]);
    
} else {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method tidak diizinkan'
    ]);
}

$conn->close();
?>