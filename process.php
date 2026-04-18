<?php
session_start();

// 1. Cek apakah file koneksi benar-benar ada di folder tersebut
// Jika file connect.php ada di folder 'Connect' yang sejajar dengan folder file ini:
$path_koneksi = '../Connect/connect.php';

if (file_exists($path_koneksi)) {
    include($path_koneksi);
} else {
    die("Error: File connect.php tidak ditemukan di path: $path_koneksi");
}

// 2. Cek apakah variabel koneksi ($conn) sudah terbentuk dari file connect.php
if (!isset($conn) || !$conn) {
    die("Error: Variabel koneksi \$conn tidak ditemukan atau gagal terhubung ke database.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pastikan user login
    if (!isset($_SESSION['id_user'])) {
        die("Error: Anda harus login terlebih dahulu.");
    }

    $id_user = $_SESSION['id_user'];
    
    // Ambil data dan bersihkan (Gunakan $conn yang sudah dipastikan ada)
    $brand    = mysqli_real_escape_string($conn, $_POST['brand']);
    $type     = mysqli_real_escape_string($conn, $_POST['vehicle_type']);
    $plate    = mysqli_real_escape_string($conn, $_POST['license_plate']);
    $category = mysqli_real_escape_string($conn, $_POST['vehicle_category']);
    
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $date     = mysqli_real_escape_string($conn, $_POST['service_date']);
    $problem  = mysqli_real_escape_string($conn, $_POST['problem']);

    // Proses Insert ke database seperti kode sebelumnya...
    $query_veh = "INSERT INTO vehicles (id_user, license_plate, brand, vehicle_type, vehicle_category) 
                  VALUES ('$id_user', '$plate', '$brand', '$type', '$category')";
    
    if (mysqli_query($conn, $query_veh)) {
        $id_vehicle = mysqli_insert_id($conn); 
        $query_res = "INSERT INTO reservations (id_user, id_vehicle, service_date, problem, status, location, id_workshop) 
              VALUES ('$id_user', '$id_vehicle', '$date', '$problem', 'Pending', '$location', NULL)";
        
        if (mysqli_query($conn, $query_res)) {
            echo "<script>alert('Berhasil!'); window.location='page.php';</script>";
        } else {
            echo "Error Reservasi: " . mysqli_error($conn);
        }
    } else {
        echo "Error Kendaraan: " . mysqli_error($conn);
    }
}
?>