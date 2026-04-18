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
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #d7ecff;
            color: #0f2240;
        }
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 28px;
            background-color: #001b3f;
            color: #ffffff;
            flex-wrap: wrap;
        }
        .navbar .brand {
            font-size: 1.2rem;
            font-weight: bold;
        }
        .navbar nav {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
        }
        .navbar nav a {
            color: #ffffff;
            text-decoration: none;
            font-size: 0.95rem;
        }
        .navbar nav a:hover {
            text-decoration: underline;
        }

        .hero-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 60px 8%;
        gap: 50px;
        }

        .hero-text { flex: 1; }
        .hero-image { flex: 1; text-align: right; }
        .hero-image img { 
            width: 100%; 
            max-width: 450px; 
            border-radius: 15px; 
            box-shadow: 0 10px 20px rgba(0,0,0,0.1); 
        }

      
        .form-expand-area {
            width: 80%;
            margin: 50px auto; 
            padding: 40px;
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid #eee;
        }

        .btn-start {
            padding: 12px 30px;
            background-color: #2607b1;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            display: inline-block;
            margin-top: 20px;
        }

        .form-expand-area {
            width: 84%;
            margin: 0 auto 50px auto;
            padding: 40px;
            background: #fdfdfd;
            border: 1px solid #eee;
            border-radius: 20px;
        }
        .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
        }
        
        .form-group-full {
            width: 100%;
            margin-bottom: 20px;
        }
        .textarea, input[type="text"], input[type="date"],
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: white;
        }
        .btn-start {
            display: inline-block;
            padding: 12px 25px;
            background-color: #2607b1;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            margin-top: 20px;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #c4d6eb;
            border-radius: 8px;
            background-color: #f7fbff;
            font-size: 0.96rem;
            box-sizing: border-box;
        }
        .form-group textarea {
            min-height: 110px;
            resize: vertical;
        }
        .btn-primary {
            background-color: #001b3f;
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
        }
        .btn-primary:hover {
            background-color: #002c6c;
        }
        .info-box {
            background-color: #eef6ff;
            border: 1px solid #c9ddf5;
            border-radius: 10px;
            padding: 18px;
        }
        .info-box p {
            margin: 10px 0;
            line-height: 1.6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }
        table th,
        table td {
            padding: 14px 10px;
            border-bottom: 1px solid #e3edf7;
            text-align: left;
        }
        table th {
            background-color: #f1f7ff;
        }
        .grid-two {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }
        .link-row {
            margin-top: 16px;
            text-align: right;
        }
        .link-row a {
            color: #001b3f;
            text-decoration: none;
            font-weight: 700;
        }
        .link-row a:hover {
            text-decoration: underline;
        }
        .alert {
            background-color: #e8f4ff;
            padding: 10px 16px;
            margin-bottom: 18px;
        }
        @media (max-width: 920px) {
            .hero-grid,
            .grid-two {
                grid-template-columns: 1fr;
            }
            .navbar {
                justify-content: center;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <header class="navbar">
        <div class="brand">Simply Fixed</div>
        <nav>
            <a href="../Login/Logout.php">Log Out</a>
            <a href="../History/repair_history.php">History</a>
            <a href="#profile">Profile</a>
            <a href="#settings">Settings</a>
        </nav>
    </header>

    <main class="page-container">
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
        <img src="path/ke/gambar-kamu.jpg" alt="Bengkel Illustration">
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
</div>
</div>  
                </div>
            </div>
        </div>


