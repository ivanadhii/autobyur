<!-- app/Views/history.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>

    <link rel="stylesheet" href="<?= base_url('css/styles.css'); ?>">
    <style>
        .filter-bar {
            background-color: #f5f5f5;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }
        
        .filter-bar label {
            margin-right: 5px;
        }
        
        .filter-bar input,
        .filter-bar select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .filter-bar button {
            padding: 8px 15px;
            background-color: #035397;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .filter-bar button:hover {
            background-color: #023e7d;
        }
        
        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .history-table th,
        .history-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        
        .history-table th {
            background-color: #f5f5f5;
            position: relative;
            cursor: pointer;
        }
        
        .history-table th:hover {
            background-color: #e9e9e9;
        }
        
        .history-table th::after {
            content: "";
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
        }
        
        .history-table th.sort-asc::after {
            content: "▲";
        }
        
        .history-table th.sort-desc::after {
            content: "▼";
        }
        
        .history-table tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }
        
        .history-table tbody tr:hover {
            background-color: #f0f0f0;
        }
        
        .action-bar {
            margin-bottom: 20px;
        }
        
        .export-btn {
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .export-btn:hover {
            background-color: #45a049;
        }
        
        .no-data {
            text-align: center;
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-top: 20px;
        }
        
        .nav-tabs {
            display: flex;
            list-style: none;
            padding: 0;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .nav-tabs li {
            margin-right: 5px;
        }
        
        .nav-tabs a {
            display: block;
            padding: 10px 15px;
            text-decoration: none;
            color: #333;
            border: 1px solid transparent;
            border-bottom: none;
            border-radius: 4px 4px 0 0;
        }
        
        .nav-tabs a.active {
            border-color: #ddd;
            background-color: white;
            border-bottom: 1px solid white;
            margin-bottom: -1px;
        }
        
        .nav-tabs a:hover:not(.active) {
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">
            <img src="<?= base_url('images/pup2.png'); ?>" alt="Logo PUPR" class="logo">
            <span class="brand-text">SWAT Monitoring - Kementerian PU</span>
        </div>
    </nav>

    <div class="container">
        <ul class="nav-tabs">
            <li><a href="<?= base_url(); ?>">Dashboard</a></li>
            <li><a href="<?= base_url('history'); ?>" class="active">History</a></li>
        </ul>
        
        <h1>Data History</h1>
        
        <!-- Filter Bar -->
        <div class="filter-bar">
            <form action="<?= base_url('history'); ?>" method="get">
                <label for="start_date">Dari:</label>
                <input type="date" id="start_date" name="start_date" value="<?= $startDate ?>">
                
                <label for="end_date">Sampai:</label>
                <input type="date" id="end_date" name="end_date" value="<?= $endDate ?>">
                
                <label for="sort_by">Urutkan:</label>
                <select id="sort_by" name="sort_by">
                    <option value="timestamp" <?= ($sortBy == 'timestamp') ? 'selected' : '' ?>>Waktu</option>
                    <option value="soil_moisture" <?= ($sortBy == 'soil_moisture') ? 'selected' : '' ?>>Kelembaban Tanah</option>
                    <option value="rssi" <?= ($sortBy == 'rssi') ? 'selected' : '' ?>>RSSI</option>
                </select>
                
                <select id="sort_dir" name="sort_dir">
                    <option value="desc" <?= ($sortDir == 'desc') ? 'selected' : '' ?>>Menurun</option>
                    <option value="asc" <?= ($sortDir == 'asc') ? 'selected' : '' ?>>Menaik</option>
                </select>
                
                <button type="submit">Filter</button>
            </form>
        </div>
        
        <!-- Action Bar -->
        <div class="action-bar">
            <a href="<?= base_url('history/export') ?>?start_date=<?= $startDate ?>&end_date=<?= $endDate ?>&sort_by=<?= $sortBy ?>&sort_dir=<?= $sortDir ?>" class="export-btn">Export ke CSV</a>
        </div>
        
        <!-- Data Table -->
        <?php if (!empty($history)): ?>
            <table class="history-table">
                <thead>
                    <tr>
                        <th class="<?= ($sortBy == 'timestamp') ? 'sort-'.$sortDir : '' ?>">
                            <a href="<?= base_url('history') ?>?start_date=<?= $startDate ?>&end_date=<?= $endDate ?>&sort_by=timestamp&sort_dir=<?= ($sortBy == 'timestamp' && $sortDir == 'asc') ? 'desc' : 'asc' ?>">
                                Waktu
                            </a>
                        </th>
                        <th class="<?= ($sortBy == 'soil_moisture') ? 'sort-'.$sortDir : '' ?>">
                            <a href="<?= base_url('history') ?>?start_date=<?= $startDate ?>&end_date=<?= $endDate ?>&sort_by=soil_moisture&sort_dir=<?= ($sortBy == 'soil_moisture' && $sortDir == 'asc') ? 'desc' : 'asc' ?>">
                                Kelembaban Tanah (%)
                            </a>
                        </th>
                        <th class="<?= ($sortBy == 'rssi') ? 'sort-'.$sortDir : '' ?>">
                            <a href="<?= base_url('history') ?>?start_date=<?= $startDate ?>&end_date=<?= $endDate ?>&sort_by=rssi&sort_dir=<?= ($sortBy == 'rssi' && $sortDir == 'asc') ? 'desc' : 'asc' ?>">
                                RSSI (dBm)
                            </a>
                        </th>
                        <th class="<?= ($sortBy == 'mode_auto') ? 'sort-'.$sortDir : '' ?>">
                            <a href="<?= base_url('history') ?>?start_date=<?= $startDate ?>&end_date=<?= $endDate ?>&sort_by=mode_auto&sort_dir=<?= ($sortBy == 'mode_auto' && $sortDir == 'asc') ? 'desc' : 'asc' ?>">
                                Mode Auto
                            </a>
                        </th>
                        <th class="<?= ($sortBy == 'solenoid_state') ? 'sort-'.$sortDir : '' ?>">
                            <a href="<?= base_url('history') ?>?start_date=<?= $startDate ?>&end_date=<?= $endDate ?>&sort_by=solenoid_state&sort_dir=<?= ($sortBy == 'solenoid_state' && $sortDir == 'asc') ? 'desc' : 'asc' ?>">
                                Status Solenoid
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $row): ?>
                        <tr>
                            <td><?= date('d-m-Y H:i:s', strtotime($row['timestamp'])) ?></td>
                            <td><?= $row['soil_moisture'] !== null ? number_format($row['soil_moisture'], 2) : 'N/A' ?></td>
                            <td><?= $row['rssi'] !== null ? number_format($row['rssi'], 2) : 'N/A' ?></td>
                            <td><?= $row['mode_auto'] ? '<span style="color: green;">Aktif</span>' : '<span style="color: red;">Non-aktif</span>' ?></td>
                            <td><?= $row['solenoid_state'] ? '<span style="color: green;">Menyala</span>' : '<span style="color: red;">Mati</span>' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">
                <p>Tidak ada data dalam rentang tanggal yang dipilih.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Jika tidak ada tanggal awal yang dipilih, set default ke 7 hari yang lalu
            const startDateInput = document.getElementById('start_date');
            if (!startDateInput.value) {
                const defaultDate = new Date();
                defaultDate.setDate(defaultDate.getDate() - 7);
                startDateInput.value = defaultDate.toISOString().split('T')[0];
            }
            
            // Jika tidak ada tanggal akhir yang dipilih, set default ke hari ini
            const endDateInput = document.getElementById('end_date');
            if (!endDateInput.value) {
                const today = new Date();
                endDateInput.value = today.toISOString().split('T')[0];
            }
        });
    </script>
</body>
</html>