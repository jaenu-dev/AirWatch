<?php
require_once 'koneksi.php';

// Get available locations
$locations = getAvailableLocations();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang - AirWatch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .welcome-hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(180deg, var(--bg-body-start) 0%, var(--bg-body-mid) 50%, var(--bg-body-end) 100%);
            background-attachment: fixed;
            position: relative;
            overflow: hidden;
        }

        .welcome-card {
            background: var(--bg-card-gradient);
            backdrop-filter: var(--blur-glass);
            -webkit-backdrop-filter: var(--blur-glass);
            border: 1px solid var(--border-glass);
            border-radius: var(--radius-l);
            padding: 3.5rem;
            width: 100%;
            max-width: 550px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.06);
            text-align: center;
            position: relative;
            z-index: 10;
        }

        .location-select {
            background: var(--brand-white);
            border: 1px solid var(--border-glass);
            color: var(--brand-dark);
            padding: 1.2rem;
            border-radius: 20px;
            width: 100%;
            margin-bottom: 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: var(--shadow-soft);
        }

        .location-select:focus {
            border-color: var(--brand-primary);
            outline: none;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .btn-start-premium {
            background: var(--brand-primary);
            border: none;
            color: white;
            font-weight: 800;
            padding: 1.2rem 2.5rem;
            border-radius: 100px;
            width: 100%;
            font-size: 1.1rem;
            box-shadow: 0 10px 30px rgba(37, 99, 235, 0.3);
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            text-transform: uppercase;
            letter-spacing: 1px;
            display: block;
            text-decoration: none;
        }

        .btn-start-premium:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 45px rgba(37, 99, 235, 0.4);
            color: white;
        }


        /* Animated Particles (Blue/Light Subtle) */
        .particle {
            position: absolute;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            animation: float-particle 10s infinite linear;
        }

        @keyframes float-particle {
            0% { transform: translateY(0) rotate(0deg); opacity: 0; }
            50% { opacity: 0.3; }
            100% { transform: translateY(-100vh) rotate(360deg); opacity: 0; }
        }

        @media (max-width: 576px) {
            .welcome-card {
                padding: 2.5rem 1.5rem !important;
            }
            .brand-icon {
                width: 90px !important;
                height: 90px !important;
            }
            h1 {
                font-size: 2rem !important;
            }
        }
    </style>
</head>
<body>

    <section class="welcome-hero">
        <!-- Floating Particles Background -->
        <?php for($i=0; $i<12; $i++): ?>
            <div class="particle" style="
                width: <?php echo rand(50, 200); ?>px;
                height: <?php echo rand(50, 200); ?>px;
                left: <?php echo rand(0, 100); ?>%;
                bottom: -200px;
                animation-duration: <?php echo rand(10, 20); ?>s;
                animation-delay: <?php echo rand(0, 8); ?>s;
            "></div>
        <?php endfor; ?>

        <div class="container px-4">
            <div class="welcome-card mx-auto">
                <div class="mb-5">
                    <div class="brand-icon mx-auto mb-4" style="width: 120px; height: 120px; background: transparent; box-shadow: none;">
                        <img src="assets/logo.png" alt="AirWatch Logo" style="width: 100%; height: 100%; object-fit: contain;">
                    </div>
                    <h1 class="fw-bold mb-3" style="font-size: 2.5rem; color: var(--brand-dark);">AirWatch</h1>
                    <p class="text-muted fs-5">Pantau kualitas udara di sekitar Anda dengan presisi tinggi.</p>
                </div>

                <form action="index.php" method="GET">
                    <div class="text-start mb-2">
                        <label class="text-dark small fw-bold text-uppercase ms-2 opacity-50">Pilih Lokasi Monitoring</label>
                    </div>
                    <select name="lokasi" class="location-select form-select mb-4" required>
                        <option value="" disabled selected>Cari Kota / Area Anda </option>
                        <?php foreach($locations as $loc): ?>
                            <option value="<?php echo htmlspecialchars($loc); ?>"><?php echo htmlspecialchars($loc); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit" class="btn-start-premium">
                        Mulai Monitoring <i class="fas fa-chevron-right ms-2 small"></i>
                    </button>
                </form>
            </div>
            
            <div class="text-center mt-5 position-relative" style="z-index: 10;">
                <p class="text-muted small fw-bold">&copy; <?php echo date('Y'); ?> AirWatch System. All rights reserved.</p>
            </div>
        </div>
    </section>

</body>
</html>
