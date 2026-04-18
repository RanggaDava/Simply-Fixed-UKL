<?php
include '../Connect/connect.php';
session_start();

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? mysqli_real_escape_string($conn, $_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($username) || empty($password)) {
        $error_message = 'Username dan password harus diisi!';
    } else {
        // cek tabel user dan role
        $stmt = $conn->prepare("SELECT id_user, nama_lengkap, password, role FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($db_id, $db_nama, $db_password, $db_role);
            $stmt->fetch();
            if (password_verify($password, $db_password)) {
                $_SESSION['id_user'] = $db_id;
                $_SESSION['nama_lengkap'] = $db_nama;
                $_SESSION['role'] = $db_role;

                if (strtolower($db_role) === 'admin') {
                    header('Location: ../Admin/admin.php');
                } else {
                    header('Location: ../MainPage/page.php');
                }
                exit();
            } else {
                $error_message = 'Password salah!';
            }
        } else {
            $error_message = 'User tidak ditemukan!';
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
            <title>Simply Fixed - Log In</title>
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
                border: 1px solid #ccc  ;
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
        </style>
        <body>
            <div class="container">
                <h2>Login</h2>
                <?php if (!empty($error_message)) : ?>
                    <p style="color: red;"><?php echo $error_message; ?></p>
                <?php endif; ?>
                <form method="POST" action="">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>

                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>

                    <label for="role">Role:</label>
                    <select id="role" name="role" required>
                        <option value="admin">User</option>
                        <option value="user">Admin</option>
                    </select>

                    <input type="submit" value="Login">
                </form>
                <div class="link-row">
                    <p>Belum punya akun? <a href="../Signin/signin.php">Daftar di sini</a></p>
                </div>
            </div>
        </body>
    </html>

