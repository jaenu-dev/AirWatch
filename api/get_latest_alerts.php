<?php
/**
 * =============================================
 * API ENDPOINT - GET LATEST ALERTS
 * Mengambil alert terbaru untuk notifikasi real-time
 * =============================================
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../koneksi.php';

// Ambil parameter last_id dari request (jika ada)
$lastId = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

// Query untuk mengambil alert yang lebih baru dari last_id
// Hanya ambil yang level bahayanya tinggi (Unhealthy ke atas) jika diperlukan, 
// atau ambil semua dan filter di frontend. 
// Sesuai request user: "unhealthy, severe, dan hazardous"
// Di database kita punya tingkat_bahaya: 'Peringatan' dan 'Bahaya'.
// Mari kita lihat post_data.php: 
// 'Bahaya' jika > 1.2x threshold.
// Kita akan kembalikan semua alert baru, nanti frontend yang memutuskan mau popup atau tidak.

$sql = "SELECT * FROM alerts 
        WHERE id > ? 
        AND status = 'aktif' 
        ORDER BY id ASC"; // ASC agar urut kronologis

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $lastId);
$stmt->execute();
$result = $stmt->get_result();
$alerts = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode([
    'status' => 'success',
    'last_id' => $lastId,
    'count' => count($alerts),
    'data' => $alerts
]);

$conn->close();
?>
