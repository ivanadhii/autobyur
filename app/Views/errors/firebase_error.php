<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error Firebase - SWAT Monitoring</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .error-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #dc3545;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .solutions {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>Error Koneksi Firebase</h1>
        
        <div class="error-message">
            <h3>Detail Error:</h3>
            <p><?= $error ?? 'Unknown error' ?></p>
        </div>
        
        <div class="solutions">
            <h3>Kemungkinan solusi:</h3>
            <ol>
                <li>Jalankan <a href="<?= base_url('system-check') ?>">System Check</a> untuk mendiagnosa masalah</li>
                <li>Pastikan file kredensial service-account.json tersedia dan valid</li>
                <li>Periksa sinkronisasi waktu server dengan waktu Google</li>
                <li>Verifikasi konfigurasi Firebase di Config/Firebase.php</li>
                <li>Pastikan versi cURL yang digunakan sudah versi 7.67 atau lebih baru</li>
                <li>Cek koneksi internet server</li>
            </ol>
        </div>
        
        <p><a href="<?= base_url() ?>">Coba Kembali ke Dashboard</a></p>
    </div>
</body>
</html>