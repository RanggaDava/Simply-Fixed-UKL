<?php
session_start();
include '../Connect/connect.php';

// Pastikan user sudah login
if (!isset($_SESSION['id_user'])) {
    header('Location: ../Login/Login.php');
    exit();
}

$id_user = $_SESSION['id_user'];

// Ambil data riwayat servis milik user tersebut
$query = "SELECT r.*, v.brand, v.vehicle_type, v.license_plate 
          FROM reservations r 
          JOIN vehicles v ON r.id_vehicle = v.id_vehicle 
          WHERE r.id_user = '$id_user' 
          ORDER BY r.service_date DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Servis - Simply Fixed</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@500&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; padding: 40px; }
        .container { max-width: 900px; margin: auto; background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        h2 { font-family: 'Kanit', sans-serif; color: #2607b1; margin-bottom: 25px; }
        
        .history-card {
            border: 1px solid #eee;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .info h4 { margin: 0 0 5px 0; color: #333; }
        .info p { margin: 0; color: #777; font-size: 14px; }
        
        .status-badge {
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-process { background: #cce5ff; color: #004085; }
        .status-completed { background: #d4edda; color: #155724; }

        .empty-state { text-align: center; padding: 50px; color: #aaa; }
        .btn-back { display: inline-block; margin-bottom: 20px; text-decoration: none; color: #2607b1; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <a href="../MainPage/page.php" class="btn-back">← Kembali ke Utama</a>
    <h2>Riwayat Servis Anda</h2>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
            <div class="history-card">
                <div class="info">
                    <h4><?php echo htmlspecialchars($row['brand'] . " " . $row['vehicle_type']); ?></h4>
                    <p><strong>Plat:</strong> <?php echo htmlspecialchars($row['license_plate']); ?></p>
                    <p><strong>Masalah:</strong> <?php echo htmlspecialchars($row['problem']); ?></p>
                    <p><strong>Tanggal:</strong> <?php echo date('d M Y', strtotime($row['service_date'])); ?></p>
                </div>
                <div>
                    <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                        <?php echo $row['status']; ?>
                    </span>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state">
            <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="100" style="opacity: 0.2; margin-bottom: 20px;">
            <p>Belum ada riwayat survey atau reservasi.</p>
            <a href="page.php?mulai=true" style="color: #2607b1; text-decoration: none; font-weight: bold;">Mulai Survey Sekarang</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>