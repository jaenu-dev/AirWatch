<?php
require_once '../koneksi.php';

// Pagination & Filter Logic
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
$alertCount = 0; // Alerts disabled
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Data - AirWatch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Extra fix for select dropdowns in dark mode */
        .form-select {
            background-color: #1E293B;
            border-color: #334155;
            color: #fff;
        }
        .form-select:focus {
            background-color: #1E293B;
            border-color: #60A5FA;
            color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(96, 165, 250, 0.25);
        }
    </style>
</head>
<body>
    
    <!-- ADMIN NAVIGATION -->
    <nav class="navbar navbar-expand-lg navbar-glass mb-4">
        <div class="container">
            <div class="brand-wrapper d-flex align-items-center gap-3">
                <img src="../assets/logo.png" alt="Logo" style="width: 45px; height: 45px; object-fit: contain;">
                <span>AirWatch Admin</span>
            </div>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-bars text-white"></i>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="nav-links ms-auto">
                    <a class="nav-link-item active" href="data.php">Kelola Data</a>

                    <a href="../index.php" class="btn-premium btn-outline-light ms-3"><i class="fas fa-external-link-alt"></i> Ke Website Utama</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- CONTENT -->
    <div class="container pb-5">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark">Manajemen Data Sensor</h2>
                <p class="text-muted">Tambah, edit, atau hapus data rekaman sensor.</p>
            </div>
            <button class="btn-premium btn-primary px-4" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus"></i> Tambah Data Manual
            </button>
        </div>

        <!-- Controls -->
        <div class="admin-card mb-4 p-3">
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
            </div>
        </div>

        <!-- Notification -->
        <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Sukses!</strong> 
            <?php 
                if($_GET['msg']=='added') echo 'Data berhasil ditambahkan.';
                if($_GET['msg']=='updated') echo 'Data berhasil diperbarui.';
                if($_GET['msg']=='deleted') echo 'Data berhasil dihapus.';
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Table -->
        <div class="admin-card p-0 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-premium mb-0">
                    <thead class="">
                        <tr>
                            <th class="py-3 ps-4">Waktu</th>
                            <th class="py-3">Lokasi</th>
                            <th class="py-3">Nilai PM2.5</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold text-dark"><?php echo date('d M Y', strtotime($row['timestamp'])); ?></span><br>
                                    <small class="text-muted"><?php echo date('H:i', strtotime($row['timestamp'])); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($row['lokasi']); ?></td>
                                <td><span class="fw-bold"><?php echo $row['pm25']; ?></span> µg/m³</td>
                                <td>
                                    <?php 
                                        // Calculate AQI on the fly to fix stale data
                                        $calc = calculateAQI($row['pm25']);
                                        $s = $calc['status'];
                                        
                                        // Indonesian Translation Mapping
                                        $statusMap = [
                                            'Good'      => ['label' => 'Baik',           'class' => 'bg-good'],
                                            'Moderate'  => ['label' => 'Sedang',         'class' => 'bg-moderate text-dark'],
                                            'Poor'      => ['label' => 'Tidak Sehat (S)', 'class' => 'bg-poor'],
                                            'Unhealthy' => ['label' => 'Tidak Sehat',    'class' => 'bg-unhealthy'],
                                            'Severe'    => ['label' => 'Sangat Tdk Sehat', 'class' => 'bg-severe'],
                                            'Hazardous' => ['label' => 'Berbahaya',      'class' => 'bg-hazardous']
                                        ];

                                        $st = $statusMap[$s] ?? ['label' => $s, 'class' => 'bg-secondary'];
                                    ?>
                                    <span class="badge <?php echo $st['class']; ?>" style="font-weight: 700; padding: 0.5em 1em; border-radius: 6px;">
                                        <?php echo htmlspecialchars($st['label']); ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-info me-1" 
                                        onclick="editData(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <form action="process.php" method="POST" style="display:inline;" onsubmit="return confirm('Yakin hapus data ini?')">
                                        <input type="hidden" name="action_data" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-5 text-white-50">Belum ada data</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="d-flex justify-content-center mt-4">
            <nav>
                <ul class="pagination pagination-premium">
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

    <!-- ADD MODAL -->
    <div class="modal fade modal-glass" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="process.php" method="POST">
                    <input type="hidden" name="action_data" value="add">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Data Manual</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-white-50">Lokasi</label>
                                <select name="lokasi" class="form-select form-control-dark" required>
                                    <option value="Sleman">Sleman</option>
                                    <option value="Godean">Godean</option>
                                    <option value="Kaliurang">Kaliurang</option>
                                    <option value="Bantul">Bantul</option>
                                    <option value="Condongcatur">Condongcatur</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white-50">Suhu (°C)</label>
                                <input type="number" step="0.1" name="suhu" class="form-control form-control-dark" value="30" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-white-50">PM 2.5</label>
                                <input type="number" step="0.1" name="pm25" class="form-control form-control-dark" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-white-50">PM 10</label>
                                <input type="number" step="0.1" name="pm10" class="form-control form-control-dark" value="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-white-50">Kelembapan (%)</label>
                                <input type="number" step="0.1" name="kelembapan" class="form-control form-control-dark" value="70" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-white-50">CO</label>
                                <input type="number" step="0.01" name="co" class="form-control form-control-dark" value="0" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-white-50">NO2</label>
                                <input type="number" step="0.001" name="no2" class="form-control form-control-dark" value="0" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-white-50">SO2</label>
                                <input type="number" step="0.001" name="so2" class="form-control form-control-dark" value="0" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-white-50">O3</label>
                                <input type="number" step="0.001" name="o3" class="form-control form-control-dark" value="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- EDIT MODAL -->
    <div class="modal fade modal-glass" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="process.php" method="POST">
                    <input type="hidden" name="action_data" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Data</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-white-50">Lokasi</label>
                            <input type="text" name="lokasi" id="edit_lokasi" class="form-control form-control-dark bg-secondary" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white-50">PM 2.5</label>
                            <input type="number" step="0.1" name="pm25" id="edit_pm25" class="form-control form-control-dark" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white-50">Suhu</label>
                            <input type="number" step="0.1" name="suhu" id="edit_suhu" class="form-control form-control-dark" required>
                        </div>
                        <small class="text-white-50 text-center d-block">Simpel Editor (Field lain dianggap tetap)</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editData(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_lokasi').value = data.lokasi;
            document.getElementById('edit_pm25').value = data.pm25;
            document.getElementById('edit_suhu').value = data.suhu;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
    </script>
</body>
</html>
