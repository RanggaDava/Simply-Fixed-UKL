<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simply Fixed - Solusi Servis Kendaraan Modern</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@500;700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }
        .hero {
            background-image: url('https://www.transparenttextures.com/patterns/cubes.png'), linear-gradient(135deg, #2607b1 0%, #4a00e0 100%);
            background-color: #2607b1;
            height: 600px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
            padding: 0 20px;
        }
        .hero h1 {
            font-family: 'Kanit', sans-serif;
            font-size: 4rem;
            margin-bottom: 10px;
            letter-spacing: -1px;
        }
        .hero p {
            font-size: 1.2rem;
            font-weight: 300;
            margin-bottom: 40px;
            opacity: 0.9;
        }
        .btn-start {
            padding: 15px 40px;
            background-color: transparent;
            color: white;
            border: 2px solid white;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1.1rem;
        }
        .btn-start:hover {
            background-color: white;
            color: #2607b1;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        .content {
            background-color: #1a1a1a;
            color: white;
            padding: 80px 10%;
            text-align: center;
        }
        .content h2 {
            font-family: 'Kanit', sans-serif;
            font-size: 2rem;
            margin-bottom: 60px;
            font-weight: 400;
            opacity: 0.8;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
        }

        .feature-item {
            padding: 20px;
        }

        .feature-item img {
            width: 60px;
            margin-bottom: 20px;
            filter: invert(1); /* Membuat ikon putih */
        }

        .feature-item h3 {
            font-size: 1.2rem;
            margin-bottom: 15px;
            color: #fff;
        }

        .feature-item p {
            font-size: 0.95rem;
            color: #aaa;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .hero h1 { font-size: 2.5rem; }
            .hero p { font-size: 1rem; }
        }
    </style>
</head>
<body>

    <section class="hero">
        <h1>Simply Fixed</h1>
        <p>Solusi manajemen servis kendaraan yang cerdas, cepat, dan transparan.</p>
        
        <a href="../Login/Login.php" class="btn-start">Mulai Sekarang</a>
    </section>

    <section class="content">
        <h2>Merawat kendaraan kini jauh lebih mudah.</h2>
        
        <div class="features">
            <div class="feature-item">
                <img src="https://cdn-icons-png.flaticon.com/512/1087/1087080.png" alt="Icon">
                <h3>Survey Mandiri</h3>
                <p>Identifikasi masalah kendaraan Anda melalui fitur survey kami sebelum datang ke bengkel.</p>
            </div>

            <div class="feature-item">
                <img src="https://cdn-icons-png.flaticon.com/512/3067/3067451.png" alt="Icon">
                <h3>Pantau Progres</h3>
                <p>Lihat status perbaikan secara real-time dari panel riwayat tanpa harus bolak-balik bertanya.</p>
            </div>

            <div class="feature-item">
                <img src="https://cdn-icons-png.flaticon.com/512/950/950714.png" alt="Icon">
                <h3>Terintegrasi</h3>
                <p>Data kendaraan dan riwayat servis tersimpan aman untuk memudahkan perawatan jangka panjang.</p>
            </div>
        </div>
    </section>

</body>
</html>