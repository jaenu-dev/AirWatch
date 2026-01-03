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
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
            position: relative;
            overflow: hidden;
        }

        .welcome-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 3rem;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            text-align: center;
            position: relative;
            z-index: 10;
        }

        .location-select {
            background: #1E293B;
            border: 2px solid #334155;
            color: white;
            padding: 1rem;
            border-radius: 12px;
            width: 100%;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .location-select:focus {
            border-color: #60A5FA;
            outline: none;
            box-shadow: 0 0 0 4px rgba(96, 165, 250, 0.1);
        }

        .btn-start {
            background:  #60A5FA;
            border: none;
            color: white;
            font-weight: 700;
            padding: 1rem 2rem;
            border-radius: 12px;
            width: 100%;
            font-size: 1.1rem;
        }

        .btn-start:hover {
            transform: translateY(-3px);
        }


        /* Animated Particles */
        .particle {
            position: absolute;
            background: radial-gradient(circle, rgba(96,165,250,0.2) 0%, transparent 70%);
            border-radius: 50%;
            animation: float-particle 10s infinite linear;
        }

        @keyframes float-particle {
            0% { transform: translateY(0) rotate(0deg); opacity: 0; }
            50% { opacity: 0.5; }
            100% { transform: translateY(-100vh) rotate(360deg); opacity: 0; }
        }
    </style>
</head>
<body>

    <section class="welcome-hero">
        <!-- Floating Particles Background -->
        <?php for($i=0; $i<15; $i++): ?>
            <div class="particle" style="
                width: <?php echo rand(20, 100); ?>px;
                height: <?php echo rand(20, 100); ?>px;
                left: <?php echo rand(0, 100); ?>%;
                bottom: -100px;
                animation-duration: <?php echo rand(5, 15); ?>s;
                animation-delay: <?php echo rand(0, 5); ?>s;
            "></div>
        <?php endfor; ?>

        <div class="container px-4">
            <div class="welcome-card mx-auto">
                <div class="mb-4">
                    <h1 class="fw-bold mb-3" style="font-size: 2rem; background: linear-gradient(135deg, #60A5FA, #22D3EE); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">AirWatch</h1>
                    <h2 class="fw-bold text-white mb-2">Selamat Datang</h2>
                    <p class="text-white-50">Pilih lokasi Anda untuk melihat kualitas udara secara real-time.</p>
                </div>

                <form action="index.php" method="GET">
                    <div class="text-start mb-2">
                        <label class="text-white-50 small fw-bold text-uppercase ms-1">Pilih Lokasi</label>
                    </div>
                    <select name="lokasi" class="location-select form-select" required>
                        <option value="" disabled selected>Pilih Kota / Area </option>
                        <?php foreach($locations as $loc): ?>
                            <option value="<?php echo htmlspecialchars($loc); ?>"><?php echo htmlspecialchars($loc); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit" class="btn-start">
                        Lihat Kualitas Udara <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </form>
            </div>
            
            <div class="text-center mt-4 position-relative" style="z-index: 10;">
                <p class="text-white-50 small">&copy; <?php echo date('Y'); ?> AirWatch System. All rights reserved.</p>
            </div>
        </div>
    </section>

</body>
</html>
