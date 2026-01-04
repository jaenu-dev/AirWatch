<?php
require_once '../koneksi.php';

// Fetch ALL alerts for admin (not just active)
$sql = "SELECT * FROM alerts ORDER BY created_at DESC";
$result = $conn->query($sql);
$alerts = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Alert - AirWatch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-select {
            background-color: #1E293B;
            border-color: #334155;
            color: #fff;
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
                    <a class="nav-link-item" href="data.php">Kelola Data</a>
                    <a class="nav-link-item active" href="alert.php">Kelola Alert</a>
                    <a href="../index.php" class="btn-premium btn-outline-light ms-3"><i class="fas fa-external-link-alt"></i> Ke Website Utama</a>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- HERO TITLE Warning -->
    <div class="py-5" style="background: linear-gradient(135deg, white, #E0F2FE); border-bottom: 1px solid var(--border-glass);">
        <div class="container text-center">
            <i class="fas fa-exclamation-circle fa-3x mb-3 text-warning"></i>
            <h1 class="fw-bold text-dark">Pusat Kendali Peringatan</h1>
            <p class="mb-0 text-muted">Kelola notifikasi bahaya yang ditampilkan ke publik</p>
        </div>
    </div>

    <div class="container my-5">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold m-0 text-dark">Daftar Peringatan Sistem</h4>
            <button class="btn-premium btn-warning px-4" data-bs-toggle="modal" data-bs-target="#addAlertModal">
                <i class="fas fa-plus-circle"></i> Buat Alert Baru
            </button>
        </div>

        <!-- Notification -->
        <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Sukses!</strong> Perubahan alert berhasil disimpan.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row g-4">
            <?php foreach($alerts as $alert): 
                    $isDanger = ($alert['tingkat_bahaya'] == 'Bahaya');
                    $isActive = ($alert['status'] == 'aktif');
            ?>
            <div class="col-md-6">
                <!-- Using .admin-card but adding opacity logic for inactive -->
                <div class="admin-card h-100 p-4 <?php echo $isActive ? '' : 'opacity-50'; ?>" style="<?php echo $isActive ? 'border-left: 5px solid '.($isDanger ? '#ef4444' : '#f59e0b') : ''; ?>">
                    <div class="d-flex justify-content-between mb-3">
                        <div>
                            <span class="badge <?php echo $isDanger ? 'bg-danger' : 'bg-warning text-dark'; ?> me-2">
                                <?php echo $alert['tingkat_bahaya']; ?>
                            </span>
                            <span class="badge <?php echo $isActive ? 'bg-success' : 'bg-secondary'; ?>">
                                <?php echo strtoupper($alert['status']); ?>
                            </span>
                        </div>
                        <small class="text-muted"><?php echo formatTanggalIndonesia($alert['created_at']); ?></small>
                    </div>
                    
                    <h5 class="fw-bold text-dark"><?php echo htmlspecialchars($alert['pesan']); ?></h5>
                    <p class="text-muted mb-4">Lokasi: <?php echo htmlspecialchars($alert['lokasi']); ?></p>

                    <div class="d-flex gap-2 border-top border-secondary pt-3">
                        <form action="process.php" method="POST" class="flex-grow-1">
                            <input type="hidden" name="action_alert" value="toggle">
                            <input type="hidden" name="id" value="<?php echo $alert['id']; ?>">
                            <input type="hidden" name="current_status" value="<?php echo $alert['status']; ?>">
                            <button type="submit" class="btn btn-sm w-100 fw-bold <?php echo $isActive ? 'btn-outline-secondary' : 'btn-success'; ?>">
                                <i class="fas <?php echo $isActive ? 'fa-eye-slash' : 'fa-eye'; ?> me-2"></i> 
                                <?php echo $isActive ? 'Nonaktifkan' : 'Aktifkan'; ?>
                            </button>
                        </form>
                        
                        <form action="process.php" method="POST" onsubmit="return confirm('Hapus permanen alert ini?')">
                            <input type="hidden" name="action_alert" value="delete">
                            <input type="hidden" name="id" value="<?php echo $alert['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    </div>

    <!-- ADD ALERT MODAL -->
    <div class="modal fade modal-glass" id="addAlertModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="process.php" method="POST">
                    <input type="hidden" name="action_alert" value="add">
                    <div class="modal-header">
                        <h5 class="modal-title">Buat Peringatan Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-white-50">Lokasi</label>
                            <select name="lokasi" class="form-select form-control-dark" required>
                                <option value="Sleman">Sleman</option>
                                <option value="Godean">Godean</option>
                                <option value="Kaliurang">Kaliurang</option>
                                <option value="Bantul">Bantul</option>
                                <option value="Condongcatur">Condongcatur</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white-50">Jenis Alert</label>
                            <select name="jenis_alert" class="form-select form-control-dark" required>
                                <option value="PM2.5">Polusi PM2.5</option>
                                <option value="CO">Gas CO</option>
                                <option value="Suhu">Suhu Ekstrem</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white-50">Tingkat Bahaya</label>
                            <select name="tingkat_bahaya" class="form-select form-control-dark" required>
                                <option value="Peringatan">Peringatan (Kuning)</option>
                                <option value="Bahaya">BAHAYA (Merah)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white-50">Nilai Terukur</label>
                            <input type="number" step="0.1" name="nilai" class="form-control form-control-dark" placeholder="Contoh: 155.5" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white-50">Pesan</label>
                            <textarea name="pesan" class="form-control form-control-dark" rows="3" required placeholder="Pesan peringatan untuk masyarakat..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning fw-bold text-dark">Terbitkan Peringatan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
