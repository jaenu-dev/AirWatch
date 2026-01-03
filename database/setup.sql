-- =============================================
-- DATABASE SISTEM PEMANTAUAN KUALITAS UDARA
-- Klien: Dinas Lingkungan Hidup Yogyakarta
-- Kelompok: 3
-- Lokasi: 5 Titik Monitoring di Yogyakarta
-- =============================================

-- Hapus database lama jika ada
DROP DATABASE IF EXISTS kualitas_udara;

-- Buat database baru
CREATE DATABASE kualitas_udara;
USE kualitas_udara;

-- =============================================
-- STRUKTUR TABEL
-- =============================================

-- Tabel untuk menyimpan data sensor kualitas udara
CREATE TABLE sensor_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lokasi VARCHAR(100) NOT NULL,
    pm25 FLOAT NOT NULL COMMENT 'Particulate Matter 2.5 (µg/m³)',
    pm10 FLOAT NOT NULL COMMENT 'Particulate Matter 10 (µg/m³)',
    co FLOAT NOT NULL COMMENT 'Carbon Monoxide (ppm)',
    no2 FLOAT NOT NULL COMMENT 'Nitrogen Dioxide (ppm)',
    so2 FLOAT NOT NULL COMMENT 'Sulfur Dioxide (ppm)',
    o3 FLOAT NOT NULL COMMENT 'Ozone (ppm)',
    suhu FLOAT NOT NULL COMMENT 'Suhu dalam Celsius',
    kelembapan FLOAT NOT NULL COMMENT 'Kelembapan dalam persen',
    aqi INT NOT NULL COMMENT 'Air Quality Index',
    status_kualitas VARCHAR(50) NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_timestamp (timestamp),
    INDEX idx_lokasi (lokasi),
    INDEX idx_aqi (aqi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel untuk menyimpan alert/peringatan
CREATE TABLE alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sensor_data_id INT,
    lokasi VARCHAR(100) NOT NULL,
    jenis_alert VARCHAR(50) NOT NULL,
    nilai FLOAT NOT NULL,
    batas_aman FLOAT NOT NULL,
    tingkat_bahaya VARCHAR(50) NOT NULL,
    pesan TEXT NOT NULL,
    status VARCHAR(20) DEFAULT 'aktif',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sensor_data_id) REFERENCES sensor_data(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- DATA SENSOR - 5 LOKASI YOGYAKARTA
-- =============================================
-- 📍 Sleman         : Pusat kota
-- 🏭 Godean         : Area industri (polusi tinggi)
-- ⛰️  Kaliurang     : Pegunungan (udara bersih)
-- 🌊 Bantul         : Pesisir selatan
-- 🎓 Condongcatur   : Area kampus UGM
-- =============================================

-- ========== DATA 7 HARI TERAKHIR ==========

-- HARI KE-7 (Minggu Lalu)
INSERT INTO sensor_data (lokasi, pm25, pm10, co, no2, so2, o3, suhu, kelembapan, aqi, status_kualitas, timestamp) VALUES
('Sleman', 28.5, 45.2, 1.2, 0.04, 0.03, 0.03, 28.5, 72.0, 68, 'Sedang', DATE_SUB(NOW(), INTERVAL 7 DAY)),
('Godean', 55.8, 88.5, 3.1, 0.11, 0.08, 0.07, 29.8, 65.5, 138, 'Tidak Sehat untuk Kelompok Sensitif', DATE_SUB(NOW(), INTERVAL 7 DAY)),
('Kaliurang', 18.2, 32.5, 0.8, 0.03, 0.02, 0.02, 24.8, 78.5, 52, 'Baik', DATE_SUB(NOW(), INTERVAL 7 DAY)),
('Bantul', 35.8, 58.3, 1.6, 0.06, 0.04, 0.04, 30.5, 70.2, 88, 'Sedang', DATE_SUB(NOW(), INTERVAL 7 DAY)),
('Condongcatur', 32.4, 52.8, 1.4, 0.05, 0.04, 0.03, 28.8, 71.5, 78, 'Sedang', DATE_SUB(NOW(), INTERVAL 7 DAY));

-- HARI KE-6
INSERT INTO sensor_data (lokasi, pm25, pm10, co, no2, so2, o3, suhu, kelembapan, aqi, status_kualitas, timestamp) VALUES
('Sleman', 31.2, 48.6, 1.3, 0.05, 0.03, 0.04, 29.0, 70.5, 75, 'Sedang', DATE_SUB(NOW(), INTERVAL 6 DAY)),
('Godean', 58.5, 92.2, 3.3, 0.12, 0.09, 0.08, 30.2, 64.8, 145, 'Tidak Sehat untuk Kelompok Sensitif', DATE_SUB(NOW(), INTERVAL 6 DAY)),
('Kaliurang', 20.5, 35.8, 0.9, 0.03, 0.02, 0.02, 25.2, 76.8, 58, 'Sedang', DATE_SUB(NOW(), INTERVAL 6 DAY)),
('Bantul', 38.2, 62.5, 1.7, 0.06, 0.05, 0.04, 31.0, 68.8, 95, 'Sedang', DATE_SUB(NOW(), INTERVAL 6 DAY)),
('Condongcatur', 35.6, 56.2, 1.5, 0.05, 0.04, 0.04, 29.5, 70.0, 85, 'Sedang', DATE_SUB(NOW(), INTERVAL 6 DAY));

-- HARI KE-5
INSERT INTO sensor_data (lokasi, pm25, pm10, co, no2, so2, o3, suhu, kelembapan, aqi, status_kualitas, timestamp) VALUES
('Sleman', 26.8, 42.3, 1.1, 0.04, 0.03, 0.03, 28.2, 73.5, 65, 'Sedang', DATE_SUB(NOW(), INTERVAL 5 DAY)),
('Godean', 52.2, 85.8, 2.9, 0.10, 0.07, 0.07, 29.5, 66.2, 128, 'Tidak Sehat untuk Kelompok Sensitif', DATE_SUB(NOW(), INTERVAL 5 DAY)),
('Kaliurang', 17.5, 30.2, 0.7, 0.02, 0.02, 0.02, 24.5, 79.2, 48, 'Baik', DATE_SUB(NOW(), INTERVAL 5 DAY)),
('Bantul', 33.5, 55.8, 1.5, 0.05, 0.04, 0.04, 30.2, 71.5, 82, 'Sedang', DATE_SUB(NOW(), INTERVAL 5 DAY)),
('Condongcatur', 30.2, 50.5, 1.3, 0.05, 0.03, 0.03, 28.5, 72.8, 72, 'Sedang', DATE_SUB(NOW(), INTERVAL 5 DAY));

-- HARI KE-4
INSERT INTO sensor_data (lokasi, pm25, pm10, co, no2, so2, o3, suhu, kelembapan, aqi, status_kualitas, timestamp) VALUES
('Sleman', 29.5, 46.8, 1.2, 0.04, 0.03, 0.03, 28.8, 71.0, 70, 'Sedang', DATE_SUB(NOW(), INTERVAL 4 DAY)),
('Godean', 60.8, 96.5, 3.5, 0.13, 0.09, 0.08, 30.8, 63.5, 152, 'Tidak Sehat', DATE_SUB(NOW(), INTERVAL 4 DAY)),
('Kaliurang', 19.8, 34.2, 0.8, 0.03, 0.02, 0.02, 25.0, 77.5, 55, 'Sedang', DATE_SUB(NOW(), INTERVAL 4 DAY)),
('Bantul', 36.8, 60.2, 1.6, 0.06, 0.04, 0.04, 30.8, 69.5, 90, 'Sedang', DATE_SUB(NOW(), INTERVAL 4 DAY)),
('Condongcatur', 33.5, 54.8, 1.4, 0.05, 0.04, 0.03, 29.2, 71.0, 80, 'Sedang', DATE_SUB(NOW(), INTERVAL 4 DAY));

-- HARI KE-3
INSERT INTO sensor_data (lokasi, pm25, pm10, co, no2, so2, o3, suhu, kelembapan, aqi, status_kualitas, timestamp) VALUES
('Sleman', 32.8, 51.2, 1.4, 0.05, 0.03, 0.04, 29.5, 69.5, 78, 'Sedang', DATE_SUB(NOW(), INTERVAL 3 DAY)),
('Godean', 65.5, 102.8, 3.8, 0.14, 0.10, 0.09, 31.5, 62.0, 168, 'Tidak Sehat', DATE_SUB(NOW(), INTERVAL 3 DAY)),
('Kaliurang', 21.2, 36.8, 0.9, 0.03, 0.02, 0.02, 25.5, 76.0, 60, 'Sedang', DATE_SUB(NOW(), INTERVAL 3 DAY)),
('Bantul', 39.5, 64.8, 1.8, 0.07, 0.05, 0.04, 31.5, 68.0, 98, 'Sedang', DATE_SUB(NOW(), INTERVAL 3 DAY)),
('Condongcatur', 36.2, 58.5, 1.6, 0.06, 0.04, 0.04, 29.8, 70.2, 88, 'Sedang', DATE_SUB(NOW(), INTERVAL 3 DAY));

-- HARI KE-2
INSERT INTO sensor_data (lokasi, pm25, pm10, co, no2, so2, o3, suhu, kelembapan, aqi, status_kualitas, timestamp) VALUES
('Sleman', 35.2, 55.8, 1.5, 0.06, 0.04, 0.04, 30.0, 68.5, 85, 'Sedang', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('Godean', 68.2, 108.5, 4.0, 0.15, 0.11, 0.10, 32.0, 61.5, 175, 'Tidak Sehat', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('Kaliurang', 23.5, 39.2, 1.0, 0.03, 0.02, 0.03, 26.0, 75.0, 65, 'Sedang', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('Bantul', 42.8, 70.2, 1.9, 0.07, 0.05, 0.05, 32.0, 67.0, 105, 'Tidak Sehat untuk Kelompok Sensitif', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('Condongcatur', 38.5, 62.8, 1.7, 0.06, 0.05, 0.04, 30.2, 69.5, 95, 'Sedang', DATE_SUB(NOW(), INTERVAL 2 DAY));

-- HARI KE-1 (Kemarin)
INSERT INTO sensor_data (lokasi, pm25, pm10, co, no2, so2, o3, suhu, kelembapan, aqi, status_kualitas, timestamp) VALUES
('Sleman', 38.5, 60.2, 1.7, 0.06, 0.05, 0.04, 30.5, 67.5, 95, 'Sedang', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('Godean', 72.5, 115.8, 4.2, 0.16, 0.12, 0.11, 32.5, 60.8, 182, 'Tidak Sehat', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('Kaliurang', 25.8, 42.5, 1.1, 0.04, 0.03, 0.03, 26.5, 74.0, 68, 'Sedang', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('Bantul', 45.2, 74.8, 2.0, 0.08, 0.06, 0.05, 32.5, 66.0, 112, 'Tidak Sehat untuk Kelompok Sensitif', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('Condongcatur', 41.8, 68.5, 1.8, 0.07, 0.05, 0.05, 30.8, 68.5, 102, 'Tidak Sehat untuk Kelompok Sensitif', DATE_SUB(NOW(), INTERVAL 1 DAY));

-- ========== DATA HARI INI ==========

-- PAGI (06:00 WIB)
INSERT INTO sensor_data (lokasi, pm25, pm10, co, no2, so2, o3, suhu, kelembapan, aqi, status_kualitas, timestamp) VALUES
('Sleman', 25.2, 40.5, 1.0, 0.03, 0.03, 0.03, 26.5, 75.5, 62, 'Sedang', CONCAT(CURDATE(), ' 06:00:00')),
('Godean', 48.5, 78.8, 2.5, 0.09, 0.07, 0.06, 27.0, 74.0, 118, 'Tidak Sehat untuk Kelompok Sensitif', CONCAT(CURDATE(), ' 06:00:00')),
('Kaliurang', 15.8, 28.5, 0.6, 0.02, 0.02, 0.02, 23.5, 82.0, 42, 'Baik', CONCAT(CURDATE(), ' 06:00:00')),
('Bantul', 30.5, 50.2, 1.2, 0.04, 0.03, 0.03, 27.5, 76.0, 72, 'Sedang', CONCAT(CURDATE(), ' 06:00:00')),
('Condongcatur', 28.2, 46.5, 1.1, 0.04, 0.03, 0.03, 26.8, 75.0, 68, 'Sedang', CONCAT(CURDATE(), ' 06:00:00'));

-- SIANG (12:00 WIB)
INSERT INTO sensor_data (lokasi, pm25, pm10, co, no2, so2, o3, suhu, kelembapan, aqi, status_kualitas, timestamp) VALUES
('Sleman', 42.5, 68.5, 1.9, 0.07, 0.05, 0.05, 32.5, 62.0, 105, 'Tidak Sehat untuk Kelompok Sensitif', CONCAT(CURDATE(), ' 12:00:00')),
('Godean', 70.8, 112.5, 4.0, 0.15, 0.11, 0.10, 33.5, 58.5, 178, 'Tidak Sehat', CONCAT(CURDATE(), ' 12:00:00')),
('Kaliurang', 28.5, 46.2, 1.2, 0.04, 0.03, 0.03, 29.0, 68.0, 68, 'Sedang', CONCAT(CURDATE(), ' 12:00:00')),
('Bantul', 48.2, 78.5, 2.2, 0.08, 0.06, 0.05, 33.0, 60.5, 118, 'Tidak Sehat untuk Kelompok Sensitif', CONCAT(CURDATE(), ' 12:00:00')),
('Condongcatur', 45.8, 74.2, 2.0, 0.08, 0.06, 0.05, 32.8, 61.5, 112, 'Tidak Sehat untuk Kelompok Sensitif', CONCAT(CURDATE(), ' 12:00:00'));

-- SORE (17:00 WIB)
INSERT INTO sensor_data (lokasi, pm25, pm10, co, no2, so2, o3, suhu, kelembapan, aqi, status_kualitas, timestamp) VALUES
('Sleman', 48.5, 78.2, 2.3, 0.09, 0.06, 0.06, 31.5, 65.0, 118, 'Tidak Sehat untuk Kelompok Sensitif', CONCAT(CURDATE(), ' 17:00:00')),
('Godean', 75.8, 120.8, 4.5, 0.17, 0.13, 0.12, 32.5, 62.0, 192, 'Tidak Sehat', CONCAT(CURDATE(), ' 17:00:00')),
('Kaliurang', 32.5, 52.8, 1.4, 0.05, 0.04, 0.04, 28.0, 70.5, 78, 'Sedang', CONCAT(CURDATE(), ' 17:00:00')),
('Bantul', 52.8, 85.2, 2.4, 0.09, 0.07, 0.06, 31.8, 64.5, 128, 'Tidak Sehat untuk Kelompok Sensitif', CONCAT(CURDATE(), ' 17:00:00')),
('Condongcatur', 50.2, 82.5, 2.3, 0.08, 0.06, 0.06, 31.2, 65.8, 122, 'Tidak Sehat untuk Kelompok Sensitif', CONCAT(CURDATE(), ' 17:00:00'));

-- REAL-TIME (SEKARANG)
INSERT INTO sensor_data (lokasi, pm25, pm10, co, no2, so2, o3, suhu, kelembapan, aqi, status_kualitas, timestamp) VALUES
('Sleman', 40.5, 65.8, 1.8, 0.07, 0.05, 0.05, 30.8, 66.5, 100, 'Sedang', NOW()),
('Godean', 68.5, 108.2, 3.9, 0.15, 0.11, 0.10, 32.0, 63.5, 175, 'Tidak Sehat', NOW()),
('Kaliurang', 22.5, 38.5, 1.0, 0.03, 0.02, 0.03, 26.8, 73.5, 62, 'Sedang', NOW()),
('Bantul', 46.8, 76.5, 2.1, 0.08, 0.06, 0.05, 31.5, 66.0, 115, 'Tidak Sehat untuk Kelompok Sensitif', NOW()),
('Condongcatur', 44.2, 72.5, 1.9, 0.07, 0.05, 0.05, 30.5, 67.5, 108, 'Tidak Sehat untuk Kelompok Sensitif', NOW());

-- =============================================
-- DATA ALERTS
-- =============================================

-- Alert untuk Godean (Area Industri - Bahaya)
INSERT INTO alerts (sensor_data_id, lokasi, jenis_alert, nilai, batas_aman, tingkat_bahaya, pesan, created_at) VALUES
((SELECT id FROM sensor_data WHERE lokasi = 'Godean' ORDER BY timestamp DESC LIMIT 1), 
 'Godean', 'PM2.5', 68.5, 55.4, 'Bahaya', 
 'Konsentrasi PM2.5 di area industri Godean melebihi batas aman! Hindari aktivitas outdoor dan gunakan masker N95.', NOW());

INSERT INTO alerts (sensor_data_id, lokasi, jenis_alert, nilai, batas_aman, tingkat_bahaya, pesan, created_at) VALUES
((SELECT id FROM sensor_data WHERE lokasi = 'Godean' ORDER BY timestamp DESC LIMIT 1), 
 'Godean', 'CO', 3.9, 4.0, 'Peringatan', 
 'Kadar Carbon Monoxide di Godean mendekati batas bahaya. Waspada bagi penderita gangguan pernapasan.', NOW());

-- Alert untuk Bantul
INSERT INTO alerts (sensor_data_id, lokasi, jenis_alert, nilai, batas_aman, tingkat_bahaya, pesan, created_at) VALUES
((SELECT id FROM sensor_data WHERE lokasi = 'Bantul' ORDER BY timestamp DESC LIMIT 1), 
 'Bantul', 'PM2.5', 46.8, 55.4, 'Peringatan', 
 'Kualitas udara di Bantul tidak sehat untuk kelompok sensitif. Anak-anak dan lansia disarankan mengurangi aktivitas outdoor.', NOW());

-- Alert untuk Condongcatur
INSERT INTO alerts (sensor_data_id, lokasi, jenis_alert, nilai, batas_aman, tingkat_bahaya, pesan, created_at) VALUES
((SELECT id FROM sensor_data WHERE lokasi = 'Condongcatur' ORDER BY timestamp DESC LIMIT 1), 
 'Condongcatur', 'PM2.5', 44.2, 55.4, 'Peringatan', 
 'Area kampus Condongcatur mengalami peningkatan polusi. Mahasiswa disarankan menggunakan masker saat beraktivitas outdoor.', NOW());

-- =============================================
-- SELESAI ✅
-- Total Data: 40 record sensor + 4 alert
-- =============================================

-- Verifikasi data
SELECT 'Data berhasil diimport!' as status;
SELECT CONCAT('Total lokasi: ', COUNT(DISTINCT lokasi)) as info FROM sensor_data;
SELECT CONCAT('Total data sensor: ', COUNT(*)) as info FROM sensor_data;
SELECT CONCAT('Total alert aktif: ', COUNT(*)) as info FROM alerts WHERE status = 'aktif';