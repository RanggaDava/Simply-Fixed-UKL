<?php
include '../Connect/connect.php';    

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? mysqli_real_escape_string($conn, $_POST['username']) : '';
    $nama_lengkap = isset($_POST['nama_lengkap']) ? mysqli_real_escape_string($conn, $_POST['nama_lengkap']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';
    $role = isset($_POST['role']) ? $_POST['role'] : 'user';

    $allowed_roles = ['user', 'admin'];
    if (!in_array($role, $allowed_roles, true)) {
        $role = 'user';
    }

    if (empty($username) || empty($nama_lengkap) || empty($password) || empty($password_confirm)) {
        $error_message = 'Semua field harus diisi!';
    } else if ($password !== $password_confirm) {
        $error_message = 'Password dan konfirmasi password tidak cocok!';
    } else if (strlen($password) < 6) {
        $error_message = 'Password minimal 6 karakter!';
    } else {
        // Cek apakah username sudah terdaftar
        $stmt = $conn->prepare("SELECT id_user FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error_message = 'Username sudah terdaftar!';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user baru dengan role sesuai pilihan
            $insert_stmt = $conn->prepare("INSERT INTO user (username, nama_lengkap, password, role) VALUES (?, ?, ?, ?)");
            $insert_stmt->bind_param("ssss", $username, $nama_lengkap, $hashed_password, $role);
            
            if ($insert_stmt->execute()) {
                $success_message = 'Akun berhasil dibuat! Silahkan login.';
            } else {
                $error_message = 'Gagal membuat akun. Silahkan coba lagi.';
            }
            $insert_stmt->close();
        }
        $stmt->close();
    }
}

?>

<!DOCTYPE html>
    <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Simply Fixed - Register</title>
        </head>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f3f4f6;
                margin: 0;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .container {
                background-color: #ffffff;
                border: 1px solid #ddd;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
                width: 100%;
                max-width: 420px;
                padding: 24px;
            }
            h2 {
                text-align: center;
                margin-top: 0;
            }
            label {
                display: block;
                margin-bottom: 5px;
                font-weight: 600;
            }
            input[type="text"], input[type="password"], select {
                width: 100%;
                padding: 10px;
                margin-bottom: 12px;
                border: 1px solid #ccc;
                border-radius: 4px;
                box-sizing: border-box;
            }
            input[type="submit"] {
                background-color: #2607b1;
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                width: 100%;
            }
            .message {
                padding: 10px;
                margin-bottom: 15px;
                border-radius: 4px;
            }
            .success {
                background-color: #d4edda;
                color: #155724;
                border: 1px solid #c3e6cb;
            }
            .error {
                background-color: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }
            .link-row {
                margin-top: 16px;
                text-align: center;
            }
            .link-row a {
                color: #2607b1;
                text-decoration: none;
            }
            .link-row a:hover {
                text-decoration: underline;
            }

            .role {
                margin-bottom: 12px;
                height: 24px;
                width: 36px;
            }
        </style>
        <body>
            <div class="container">
                <h2>Register</h2>
                <?php if (!empty($success_message)) : ?>
                    <p class="message success"><?php echo $success_message; ?></p>
                <?php endif; ?>
                <?php if (!empty($error_message)) : ?>
                    <p class="message error"><?php echo $error_message; ?></p>
                <?php endif; ?>
                <form method="POST" action="">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>

                    <label for="nama_lengkap">Nama Lengkap:</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" required>

                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>

                    <label for="role">Daftar sebagai:</label>
                    <select id="role" name="role">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>

                    <label for="password_confirm">Konfirmasi Password:</label>
                    <input type="password" id="password_confirm" name="password_confirm" required>

                    <input type="submit" value="Register">
                </form>
                <div class="link-row">
                    <p>Sudah punya akun? <a href="../Login/Login.php">Login di sini</a></p>
                </div>
            </div>
        </body>
    </html>