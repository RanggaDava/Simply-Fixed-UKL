<?php
session_start();
include '../Connect/connect.php';

// Proteksi halaman: Hanya admin yang boleh masuk
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header('Location: ../Login/Login.php');
    exit();
}

// Ambil data reservasi/survey dari database
$query = "SELECT r.id_reservation, u.nama_lengkap, v.brand, v.vehicle_type, r.problem, r.service_date, r.status 
          FROM reservations r
          JOIN user u ON r.id_user = u.id_user
          JOIN vehicles v ON r.id_vehicle = v.id_vehicle
          ORDER BY r.service_date DESC";
// Handle workshop registration form submission
$workshopMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_workshop'])) {
    $nama_bengkel = mysqli_real_escape_string($conn, $_POST['nama_bengkel']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $spesialisasi = mysqli_real_escape_string($conn, $_POST['spesialisasi']);
    $rating = floatval($_POST['rating']);
    $insert = "INSERT INTO workshops (nama_bengkel, alamat, spesialisasi, rating) VALUES ('$nama_bengkel', '$alamat', '$spesialisasi', $rating)";
    if (mysqli_query($conn, $insert)) {
        $workshopMsg = '<span style="color:green">Bengkel berhasil didaftarkan!</span>';
    } else {
        $workshopMsg = '<span style="color:red">Gagal mendaftar bengkel: ' . mysqli_error($conn) . '</span>';
    }
}
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Simply Fixed</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;500&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f7ff; margin: 0; padding: 0; }
        .layout { display: flex; min-height: 100vh; }
        .sidebar {
            width: 220px;
            background: #2607b1;
            color: #fff;
            padding: 30px 0 0 0;
            min-height: 100vh;
        }
        .sidebar h3 { font-family: 'Kanit', sans-serif; text-align: center; margin-bottom: 30px; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar ul li { margin: 20px 0; }
        .sidebar ul li a {
            color: #fff;
            text-decoration: none;
            padding: 10px 30px;
            display: block;
            border-left: 4px solid transparent;
            transition: background 0.2s, border 0.2s;
        }x
        .sidebar ul li a.active, .sidebar ul li a:hover {
            background: #1a056e;
        }
        .main-content {
            flex: 1;
            padding: 40px 30px;
            background: #f4f7ff;
        }
        .admin-container { 
            max-width: 1100px; 
            margin: auto; 
            background: white; 
            padding: 30px; 
            border-radius: 15px; 
            box-shadow: 0 5px 20px #000000; }

        h2 { 
            font-family: 'Kanit', 
            sans-serif; 
            color: #2607b1; 
            border-bottom: 2px solid #f0f0f0; 
            padding-bottom: 10px; }

        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; }

        th { 
            background-color: #2607b1; 
            color: white; padding: 12px; 
            text-align: left; }

        td { padding: 12px; 
        border-bottom: 1px solid #eeeeee; 
        font-size: 14px; }
        
        tr:hover { 
            background-color: #f9f9fb; }

        .status-badge { 
            padding: 5px 10px; 
            border-radius: 20px; 
            font-size: 12px; 
            font-weight: bold; }

        .status-menunggu { 
            background: #fff3cd; 
            color: #856404; }

        .status-proses { 
            background: #cce5ff; 
            color: #004085; }

        .status-selesai { 
            background: #d4edda; 
            color: #155724; }

        .btn-action { 
            padding: 6px 12px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            text-decoration: none; 
            font-size: 12px; }

        .btn-edit { 
            background: #2607b1; 
            color: white; }

        .logout { 
            float: right; 
            color: #ff4d4d; 
            text-decoration: none; 
            font-weight: bold; }

        .form-section { 
            max-width: 500px; 
            margin: 30px auto; 
            background: #f9f9fb; 
            padding: 25px 30px; 
            border-radius: 10px; #2607b112
            box-shadow: 0 2px 8px #2607b112; }

        .form-section label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: 600; }

        .form-section input, .form-section textarea { 
            width: 100%; 
            padding: 8px; 
            margin-bottom: 15px; 
            border-radius: 5px; 
            border: 1px solid #ccc; }

        .form-section button { 
            background: #2607b1; 
            color: #fff; 
            border: none; 
            padding: 10px 20px; 
            border-radius: 5px; 
            font-size: 15px; 
            cursor: pointer; }

        .form-section button:hover { background: #1a056e; }

    </style>
</head>
<body>


<div class="layout">
    <nav class="sidebar">
        <h3>Admin Panel</h3>
        <ul>
            <li><a href="#" id="nav-reservasi" class="active" onclick="showSection('reservasi'); return false;">Manajemen Reservasi</a></li>
            <li><a href="#" id="nav-workshop" onclick="showSection('workshop'); return false;">Daftar Bengkel</a></li>
        </ul>
        <a href="../Login/logout.php" class="logout" style="margin-left:30px;">Log Out</a>
    </nav>
    <div class="main-content">
        <div id="section-reservasi">
            <div class="admin-container">
                <h2>Manajemen Reservasi & Survey</h2>
                <p>Selamat datang, Admin <strong><?php echo $_SESSION['nama_lengkap']; ?></strong>. Berikut adalah daftar masuk dari user.</p>
                <table>
                    <thead>
                        <tr>
                            <th>Pelanggan</th>
                            <th>Kendaraan</th>
                            <th>Keluhan User</th>
                            <th>Tanggal Servis</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td><?php echo htmlspecialchars($row['brand'] . " " . $row['vehicle_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['problem']); ?></td>
                            <td><?php echo date('d M Y', strtotime($row['service_date'])); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td>
                                <a href="edit_status.php?id=<?php echo $row['id_reservation']; ?>" class="btn-action btn-edit">Update Status</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="section-workshop" style="display:none;">
            <div class="admin-container">
                <h2>Daftarkan Bengkel Baru</h2>
                <?php if ($workshopMsg) echo $workshopMsg; ?>
                <form class="form-section" method="post" action="">
                    <input type="hidden" name="register_workshop" value="1">
                    <label for="nama_bengkel">Nama Bengkel</label>
                    <input type="text" id="nama_bengkel" name="nama_bengkel" required>
                    <label for="alamat">Alamat</label>
                    <textarea id="alamat" name="alamat" required></textarea>
                    <label for="spesialisasi">Spesialisasi</label>
                    <input type="text" id="spesialisasi" name="spesialisasi" required>
                    <label for="rating">Rating (0.0 - 5.0)</label>
                    <input type="number" id="rating" name="rating" min="0" max="5" step="0.1" value="0" required>
                    <button type="submit">Daftarkan Bengkel</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
function showSection(section) {
    document.getElementById('section-reservasi').style.display = (section === 'reservasi') ? '' : 'none';
    document.getElementById('section-workshop').style.display = (section === 'workshop') ? '' : 'none';
    document.getElementById('nav-reservasi').classList.toggle('active', section === 'reservasi');
    document.getElementById('nav-workshop').classList.toggle('active', section === 'workshop');
}
</script>

</body>
</html>
   

