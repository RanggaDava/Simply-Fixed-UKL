<?php
include '../Connect/connect.php';
session_start();

if (!isset($_SESSION['id_user'])) {
    header('Location: ../Login/Login.php');
    exit();
}

$user_id = $_SESSION['id_user'];
$user_name = $_SESSION['nama_lengkap'] ?? 'Pengguna';
$user_role = $_SESSION['role'] ?? 'user';

$vehicles = [];

$vehicle_stmt = $conn->prepare("SELECT id_vehicle, license_plate, brand, vehicle_type FROM vehicles WHERE id_user = ?");
$vehicle_stmt->bind_param("s", $user_id);
$vehicle_stmt->execute();
$vehicle_result = $vehicle_stmt->get_result();
if ($vehicle_result) {
    while ($row = $vehicle_result->fetch_assoc()) {
        $vehicles[] = $row;
    }
}
$vehicle_stmt->close();

$reservations = [];
$active_reservations = [];
$history_reservations = [];
$res_stmt = $conn->prepare("SELECT id_reservation, id_vehicle, id_workshop, service_date, problem, status FROM reservations WHERE id_user = ? ORDER BY service_date DESC");
$res_stmt->bind_param("s", $user_id);
$res_stmt->execute();
$res_result = $res_stmt->get_result();
if ($res_result) {
    while ($row = $res_result->fetch_assoc()) {
        $reservations[] = $row;
        if ($row['status'] === 'menunggu' || $row['status'] === 'proses') {
            $active_reservations[] = $row;
        } else {
            $history_reservations[] = $row;
        }
    }
}
$res_stmt->close();

$search_message = '';
$search_errors = [];
$search_data = [
    'license_plate' => '',
    'brand' => '',
    'vehicle_type' => '',
    'problem' => '',
    'location' => '',
    'service_date' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $search_data['license_plate'] = trim($_POST['license_plate'] ?? '');
    $search_data['brand'] = trim($_POST['brand'] ?? '');
    $search_data['vehicle_type'] = trim($_POST['vehicle_type'] ?? '');
    $search_data['problem'] = trim($_POST['problem'] ?? '');
    $search_data['location'] = trim($_POST['location'] ?? '');
    $search_data['service_date'] = trim($_POST['service_date'] ?? '');

    if (empty($search_data['brand']) || empty($search_data['vehicle_type']) || empty($search_data['problem'])) {
        $search_errors[] = 'Isi brand, tipe, dan masalah kendaraan terlebih dahulu.';
    } else {
        $search_message = 'Pencarian bengkel untuk kendaraan Anda telah diproses. Hasil tidak disimpan secara permanen tanpa reservasi.';
    }
}

$show_form = $_SERVER['REQUEST_METHOD'] === 'POST';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User</title>
    <link rel="stylesheet" href="page.css">
</head>
<body>
    <header class="navbar">
        <div class="brand">Simply Fixed</div>
        <nav>
            <a href="../Login/Logout.php">Log Out</a>
            <a href="../History/repair_history.php">History</a>
            <a href="../History/service_report.php">Service Report</a>
            <a href="../LandingPage/LandingPage.php">Landing Page</a>
            <a href="#settings">Settings</a>
        </nav>
    </header>

    <main class="page-container" style="flex:1 0 auto;">
        <div class="hero-container">
    <div class="hero-text">
        <h1>Simply Fixed</h1>
        <p>
            Solusi praktis untuk kendala kendaraan Anda. Kami membantu diagnosa 
            masalah dan menghubungkan Anda dengan bengkel tepercaya.
        </p>
        
        <?php if (!isset($_GET['mulai'])): ?>
            <a href="page.php?mulai=true#survey-section" class="btn-start">Mulai Survey Sekarang</a>
        <?php endif; ?>
    </div>

    <div class="hero-image">
        <img src="illustration.png" alt="Bengkel Illustration">
    </div>
</div>

<div id="survey-section">
    <?php if (isset($_GET['mulai'])): ?>
        <div class="form-expand-area">
            <h2 style="margin-bottom: 25px; color: #2607b1; text-align: center;">Detail Kendaraan & Keluhan</h2>
            
            <form method="POST" action="process.php"> <div class="form-grid">
                    <div class="form-group">
                        <label>Jenis Kendaraan</label>
                        <select name="vehicle_category" required>
                            <option value="">-- Pilih Jenis --</option>
                            <option value="Sepeda Motor">Sepeda Motor</option>
                            <option value="Mobil">Mobil</option>
                            <option value="Truk">Truk</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nomor Plat (License Plate)</label>
                        <input type="text" name="license_plate" placeholder="Contoh: B 1234 ABC" required>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Brand / Merk</label>
                        <input type="text" name="brand" placeholder="Contoh: Honda, Toyota" required>
                    </div>
                    <div class="form-group">
                        <label>Tipe Spesifik</label>
                        <input type="text" name="vehicle_type" placeholder="Contoh: Vario 150, Avanza" required>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Lokasi Anda</label>
                        <input type="text" name="location" placeholder="Masukkan Kota" required>
                    </div>
                    <div class="form-group">
                        <label>Rencana Tanggal Servis</label>
                        <input type="date" name="service_date" required>
                    </div>
                </div>

                <div class="form-group-full">
                    <label>Jelaskan Masalah / Keluhan</label>
                    <textarea name="problem" rows="4" placeholder="Apa yang Anda rasakan pada kendaraan Anda?" required></textarea>
                </div>

                <div style="margin-top: 30px; text-align: center;">
                    <button type="submit" class="btn-start" style="width: 100%; border:none; cursor:pointer;">Kirim Laporan & Cari Bengkel</button>
                    <p style="margin-top: 15px;">
                        <a href="page.php" style="color: #666; font-size: 14px; text-decoration: none;">× Batalkan Survey</a>
                    </p>
                </div>
            </form>
        </div>

    <?php endif; ?>
</div>
    <footer class="footer" style="flex-shrink:0;">
        <p>&copy; 2026 Simply Fixed. All Rights Reserved.</p>
        <p class="footer-subtext">Laporan Tugas Akhir UKL - SMK Telkom Sidoarjo</p>
    </footer>
    
    </main>
</body>


