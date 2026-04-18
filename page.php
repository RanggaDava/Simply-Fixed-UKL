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
        .page-container {
            max-width: 1120px;
            margin: 28px auto;
            padding: 0 18px;
        }
        .card {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
            padding: 24px;
            margin-bottom: 24px;
        }
        .hero-grid {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 24px;
        }
        h1, h2, h3 {
            margin-top: 0;
        }
        .hero-grid p {
            line-height: 1.7;
            margin-bottom: 18px;
        }
        .hero-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 40px 5%;
            gap: 30px;
        }
        .hero-text { flex: 1; }
        .hero-image { flex: 1; text-align: right; }
        .hero-image img { max-width: 100%; border-radius: 15px; }

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
            <a href="#history">History</a>
            <a href="#profile">Profile</a>
            <a href="#settings">Settings</a>
        </nav>
    </header>

    <main class="page-container">
        <div class="card hero-card">
            <div class="hero-grid">
                <div>
                    <h1>Temukan bengkel untuk kendaraan Anda</h1>
                    <p>Isi detail kendaraan, keluhan, dan lokasi Anda. Sistem ini akan membantu mencari bengkel terbaik untuk jenis kendaraan Anda.</p>
                    <p>Data kendaraan tersimpan akan otomatis muncul di bawah setelah login.</p>
                </div>
                <div class="info-box">
                    <?php if (!isset($_GET['mulai'])): ?>
    <div class="hero-wrapper">
        <div class="hero-text">
            <h1 style="font-family: 'Kanit', sans-serif;">Simply Fixed</h1>
            <p>Solusi cerdas untuk kendala kendaraan Anda. Klik tombol di bawah untuk mulai mencari bengkel yang tepat.</p>
            <a href="page.php?mulai=true" class="btn-start">Mulai Survey Sekarang</a>
        </div>
        <div class="hero-image">
            <img src="https://images.unsplash.com/photo-1486006396113-c7b3df928c9f?q=80&w=500" alt="Repair">
        </div>
    </div>

<?php else: ?>
    <div class="container" style="margin-top: 20px;">
        <div class="form-container" style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
            <h2>Form Survey Kendaraan</h2>
            
            <form method="get" action="search_results.php">
                <div class="form-group">
                    <label for="vehicle_type">Vehicle Type</label>
                    <input type="text" id="vehicle_type" name="vehicle_type" value="<?php echo htmlspecialchars($search_data['vehicle_type']); ?>" placeholder="Avanza, Vario, dll">
                </div>
                <div class="form-group">
                    <label for="problem">Masalah / Keluhan</label>
                    <textarea id="problem" name="problem" placeholder="Jelaskan masalah kendaraan Anda"><?php echo htmlspecialchars($search_data['problem']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="location">Lokasi Anda</label>
                    <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($search_data['location']); ?>" placeholder="Kota atau alamat singkat">
                </div>
                <div class="form-group">
                    <label for="service_date">Tanggal Rencana Servis</label>
                    <input type="date" id="service_date" name="service_date" value="<?php echo htmlspecialchars($search_data['service_date']); ?>">
                </div>
                <button class="btn-primary" type="submit">Cari Bengkel</button>
                <a href="page.php" style="margin-left: 10px; color: #666;">Batal</a>
            </form>
            </div>
    </div>
<?php endif; ?>
                </div>
            </div>
        </div>


