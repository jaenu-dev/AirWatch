<?php
require_once '../koneksi.php';

// HANDLE SENSOR DATA CRUD
if (isset($_POST['action_data'])) {
    
    // TAMBAH DATA SENSOR
    if ($_POST['action_data'] == 'add') {
        $lokasi = escape($_POST['lokasi']);
        $pm25 = (float)$_POST['pm25'];
        $pm10 = (float)$_POST['pm10'];
        $co = (float)$_POST['co'];
        $no2 = (float)$_POST['no2'];
        $so2 = (float)$_POST['so2'];
        $o3 = (float)$_POST['o3'];
        $suhu = (float)$_POST['suhu'];
        $kelembapan = (float)$_POST['kelembapan'];
        
        // Calculate real AQI
        $aqiResult = calculateAQI($pm25);
        $aqi = $aqiResult['aqi'];
        $status = $aqiResult['status'];

        $sql = "INSERT INTO sensor_data (lokasi, pm25, pm10, co, no2, so2, o3, suhu, kelembapan, aqi, status_kualitas) 
                VALUES ('$lokasi', '$pm25', '$pm10', '$co', '$no2', '$so2', '$o3', '$suhu', '$kelembapan', '$aqi', '$status')";
        
        if($conn->query($sql)) {
            header("Location: data.php?msg=added");
        } else {
            echo "Error: " . $conn->error;
        }
    }

    // EDIT DATA SENSOR
    elseif ($_POST['action_data'] == 'edit') {
        $id = (int)$_POST['id'];
        $lokasi = escape($_POST['lokasi']);
        $pm25 = (float)$_POST['pm25'];
        $suhu = (float)$_POST['suhu'];
        
        // Recalculate AQI on edit
        $aqiResult = calculateAQI($pm25);
        $aqi = $aqiResult['aqi'];
        $status = $aqiResult['status'];
        
        $sql = "UPDATE sensor_data SET 
                lokasi='$lokasi', 
                pm25='$pm25', 
                suhu='$suhu',
                aqi='$aqi',
                status_kualitas='$status'
                WHERE id=$id";
        
        if($conn->query($sql)) {
            header("Location: data.php?msg=updated");
        } else {
            echo "Error: " . $conn->error;
        }
    }

    // HAPUS DATA SENSOR
    elseif ($_POST['action_data'] == 'delete') {
        $id = (int)$_POST['id'];
        $conn->query("DELETE FROM sensor_data WHERE id=$id");
        header("Location: data.php?msg=deleted");
    }
}

// HANDLE ALERT CRUD
if (isset($_POST['action_alert'])) {

    // TAMBAH ALERT
    if ($_POST['action_alert'] == 'add') {
        $lokasi = escape($_POST['lokasi']);
        $judul = escape($_POST['jenis_alert']);
        $pesan = escape($_POST['pesan']);
        $bahaya = escape($_POST['tingkat_bahaya']);
        $nilai = (float)$_POST['nilai'];
        
        $sql = "INSERT INTO alerts (lokasi, jenis_alert, nilai, batas_aman, tingkat_bahaya, pesan, status) 
                VALUES ('$lokasi', '$judul', '$nilai', 0, '$bahaya', '$pesan', 'aktif')";
                
        if($conn->query($sql)) {
            header("Location: alert.php?msg=added");
        }
    }

    // UPDATE STATUS ALERT (TOGGLE)
    elseif ($_POST['action_alert'] == 'toggle') {
        $id = (int)$_POST['id'];
        $status = $_POST['current_status'] == 'aktif' ? 'nonaktif' : 'aktif';
        
        $conn->query("UPDATE alerts SET status='$status' WHERE id=$id");
        header("Location: alert.php?msg=toggled");
    }

    // HAPUS ALERT
    elseif ($_POST['action_alert'] == 'delete') {
        $id = (int)$_POST['id'];
        $conn->query("DELETE FROM alerts WHERE id=$id");
        header("Location: alert.php?msg=deleted");
    }
}
?>
