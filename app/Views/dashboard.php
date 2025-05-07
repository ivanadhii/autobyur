<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SWAT Monitoring - Kementerian PUPR</title>

    <link rel="stylesheet" href="<?= base_url('css/styles.css'); ?>">

    <script src="https://www.gstatic.com/firebasejs/10.8.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.8.1/firebase-database-compat.js"></script>
    <script src="<?= base_url('js/script.js'); ?>" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/raphael@2.3.0/raphael.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/justgage@1.4.0/justgage.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">
            <img src="<?= base_url('images/Lkemenpu.jpeg'); ?>" alt="Logo PUPR" class="logo">
            <span class="brand-text">SWAT Monitoring - Kementerian PU</span>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="main-content">
            <div class="dashboard-header">
                <h1>Dashboard Control</h1>
            </div>
            
            <!-- Top Row: Gauge, Chart, and Control -->
            <div class="top-row">
                <!-- Gauge Panel -->
                <div class="panel gauge-panel">
                    <h2 class="panel-title">Kelembaban Tanah</h2>
                    <div class="panel-content">
                        <div id="gauge"></div>
                    </div>
                </div>
                
                <!-- Chart Panel -->
                <div class="panel chart-panel">
                    <h2 class="panel-title">Grafik Kelembaban Tanah</h2>
                    <div class="panel-content">
                        <div class="chart-container">
                            <canvas id="wateringChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Control Panel -->
                <div class="panel control-panel">
                    <h2 class="panel-title">Kontrol</h2>
                    <div class="panel-content">
                        <div class="controls">
                            <div class="control-group">
                                <label class="switch">
                                    <input type="checkbox" id="modeAuto">
                                    <span class="slider round"></span>
                                </label>
                                <p>Mode Auto</p>
                            </div>

                            <div class="control-group">
                                <label class="switch">
                                    <input type="checkbox" id="solenoidManual">
                                    <span class="slider round"></span>
                                </label>
                                <p>ON/OFF Solenoid</p>
                            </div>

                            <button id="updateControl" class="control-btn">Update Control</button>

                            <div class="status-group">
                                <div id="solenoidStatusIndicator" class="status-indicator">
                                    <span class="status-label">Status Solenoid:</span> 
                                    <span id="solenoidStatusText">Membaca...</span>
                                </div>

                                <div id="rssiIndicator" class="status-indicator">
                                    <span class="status-label">Nilai RSSI:</span> 
                                    <span id="rssiValueText">Membaca...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bottom Row: History Data -->
            <div class="bottom-row">
                <!-- History Panel -->
                <div class="panel history-panel">
                    <div class="history-header">
                        <h2 class="panel-title">History Data</h2>
                        
                        <!-- Filter Bar -->
                        <div class="filter-bar">
                            <form id="historyFilterForm">
                                <div class="filter-group">
                                    <label for="start_date">Dari:</label>
                                    <input type="date" id="start_date" name="start_date" value="<?= $startDate ?? date('Y-m-d', strtotime('-7 days')) ?>">
                                    
                                    <label for="end_date">Sampai:</label>
                                    <input type="date" id="end_date" name="end_date" value="<?= $endDate ?? date('Y-m-d') ?>">
                                </div>
                                
                                <div class="filter-group">
                                    <label for="sort_by">Urutkan:</label>
                                    <select id="sort_by" name="sort_by">
                                        <option value="timestamp" <?= ($sortBy ?? 'timestamp') == 'timestamp' ? 'selected' : '' ?>>Waktu</option>
                                        <option value="soil_moisture" <?= ($sortBy ?? '') == 'soil_moisture' ? 'selected' : '' ?>>Kelembaban Tanah</option>
                                        <option value="rssi" <?= ($sortBy ?? '') == 'rssi' ? 'selected' : '' ?>>RSSI</option>
                                    </select>
                                    
                                    <select id="sort_dir" name="sort_dir">
                                        <option value="desc" <?= ($sortDir ?? 'desc') == 'desc' ? 'selected' : '' ?>>Menurun</option>
                                        <option value="asc" <?= ($sortDir ?? '') == 'asc' ? 'selected' : '' ?>>Menaik</option>
                                    </select>
                                    
                                    <button type="button" id="filterButton">Filter</button>
                                </div>
                            </form>
                            
                            <button id="exportCsv" class="export-btn">Export CSV</button>
                        </div>
                    </div>
                    
                    <!-- Data Table with Scrollable Container -->
                    <div class="table-container">
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th class="sort-header" data-sort="timestamp">Waktu</th>
                                    <th class="sort-header" data-sort="soil_moisture">Kelembaban (%)</th>
                                    <th class="sort-header" data-sort="rssi">RSSI (dBm)</th>
                                    <th class="sort-header" data-sort="mode_auto">Mode Auto</th>
                                    <th class="sort-header" data-sort="solenoid_state">Solenoid</th>
                                </tr>
                            </thead>
                            <tbody id="historyTableBody">
                                <!-- Data akan diisi oleh JavaScript -->
                                <tr><td colspan="5" class="no-data">Memuat data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Variabel untuk base URL
        const baseUrl = '<?= base_url(); ?>';
    </script>
</body>
</html>