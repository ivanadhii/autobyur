<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Check - SWAT Monitoring</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #0066cc;
        }
        .check-item {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .actions {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        pre {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>System Check for Firebase Connection</h1>
        
        <div class="check-item <?= $timeOk ? 'success' : 'error' ?>">
            <h3>Server Time Check</h3>
            <p>Server time: <?= $serverTime ?></p>
            <p>Google time: <?= $googleTime ?></p>
            <p>Time difference: <?= $timeDiff ?> seconds</p>
            <?php if (!$timeOk): ?>
                <p><strong>Action needed:</strong> Waktu server Anda berbeda lebih dari 1 menit dari waktu Google. 
                Firebase membutuhkan waktu yang sinkron untuk autentikasi.</p>
                <?php if (PHP_OS_FAMILY === 'Linux'): ?>
                    <button id="fixTimeBtn">Sinkronkan Waktu Server</button>
                    <div id="fixTimeResult"></div>
                <?php else: ?>
                    <p>Untuk sinkronisasi waktu di Windows:</p>
                    <ol>
                        <li>Buka Settings &gt; Time &amp; Language</li>
                        <li>Klik "Sync Now" atau aktifkan "Set time automatically"</li>
                    </ol>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <div class="check-item <?= $curlOk ? 'success' : 'error' ?>">
            <h3>cURL Version Check</h3>
            <p>Current cURL version: <?= $curlVersion ?></p>
            <?php if (!$curlOk): ?>
                <p><strong>Action needed:</strong> Versi cURL Anda di bawah 7.67.0, yang dapat menyebabkan masalah dengan Firebase.
                Harap update instalasi cURL Anda.</p>
                <p>Di server Linux, gunakan perintah:</p>
                <pre>sudo apt-get update
sudo apt-get install curl</pre>
            <?php endif; ?>
        </div>
        
        <div class="check-item <?= $credentialsExist ? ($credentialsValid ? 'success' : 'warning') : 'error' ?>">
            <h3>Firebase Credentials Check</h3>
            <?php if (!$credentialsExist): ?>
                <p><strong>Error:</strong> File service-account.json tidak ditemukan di lokasi yang diharapkan.</p>
            <?php elseif (!$credentialsValid): ?>
                <p><strong>Warning:</strong> File kredensial Firebase ada tetapi tampaknya tidak valid atau tidak lengkap.</p>
            <?php else: ?>
                <p>File kredensial Firebase ditemukan dan valid.</p>
                <p>Project ID: <?= $projectId ?></p>
            <?php endif; ?>
        </div>
        
        <div class="check-item <?= $firebaseConnected ? 'success' : 'error' ?>">
            <h3>Firebase Connection Test</h3>
            <?php if ($firebaseConnected): ?>
                <p>Koneksi ke Firebase berhasil!</p>
            <?php else: ?>
                <p><strong>Error:</strong> Gagal terhubung ke Firebase.</p>
                <?php if (!empty($firebaseError)): ?>
                    <p>Error message: <?= $firebaseError ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <div class="actions">
            <h3>Next Steps</h3>
            <?php if ($timeOk && $curlOk && $credentialsExist && $credentialsValid && $firebaseConnected): ?>
                <p>Semua pemeriksaan sistem berhasil! Anda siap untuk melanjutkan.</p>
                <p><a href="<?= base_url() ?>">Buka Dashboard</a></p>
            <?php else: ?>
                <p>Harap selesaikan masalah yang disorot di atas sebelum melanjutkan.</p>
                
                <?php if (!$credentialsExist || !$credentialsValid): ?>
                    <div class="check-item warning">
                        <h4>Cara Memperbaiki Kredensial Firebase:</h4>
                        <ol>
                            <li>Buka <a href="https://console.firebase.google.com/" target="_blank">Firebase Console</a></li>
                            <li>Pilih proyek "swtm-53c68"</li>
                            <li>Klik ikon "Project settings" (ikon roda gigi)</li>
                            <li>Buka tab "Service accounts"</li>
                            <li>Klik "Generate new private key"</li>
                            <li>Download file JSON dan simpan sebagai "service-account.json" di root project</li>
                        </ol>
                    </div>
                <?php endif; ?>
                
                <p><a href="<?= base_url('system-check') ?>">Jalankan Pemeriksaan Lagi</a></p>
            <?php endif; ?>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const fixTimeBtn = document.getElementById('fixTimeBtn');
        if (fixTimeBtn) {
            fixTimeBtn.addEventListener('click', function() {
                const resultDiv = document.getElementById('fixTimeResult');
                resultDiv.innerHTML = 'Menyinkronkan waktu...';
                
                fetch('<?= base_url('system-check/fix-time') ?>')
                    .then(response => response.json())
                    .then(data => {
                        resultDiv.innerHTML = `<p>${data.message}</p>`;
                        if (data.output) {
                            resultDiv.innerHTML += `<pre>${data.output}</pre>`;
                        }
                        
                        if (data.success) {
                            resultDiv.innerHTML += '<p>Halaman akan dimuat ulang dalam 3 detik...</p>';
                            setTimeout(() => {
                                window.location.reload();
                            }, 3000);
                        }
                    })
                    .catch(error => {
                        resultDiv.innerHTML = `<p>Error: ${error.message}</p>`;
                    });
            });
        }
    });
    </script>
</body>
</html>