document.addEventListener("DOMContentLoaded", function () {
    console.log("Script.js berhasil dimuat!");

    // Cek apakah Firebase tersedia
    if (typeof firebase === "undefined") {
        console.error("Firebase belum dimuat! Pastikan SDK sudah dimasukkan sebelum script.js.");
        return;
    }

    // Konfigurasi Firebase
    const firebaseConfig = {
        apiKey: "AIzaSyDcO9Hg1VRRfpSPyLCpLTHaHIRYc6Wg_O4",
        authDomain: "swtm-53c68.firebaseapp.com",
        databaseURL: "https://swtm-53c68-default-rtdb.asia-southeast1.firebasedatabase.app",
        projectId: "swtm-53c68",
        storageBucket: "swtm-53c68.firebasestorage.app",
        messagingSenderId: "227349607822",
        appId: "1:227349607822:web:bac516946174fafd9b20df",
        measurementId: "G-FMPC8R1LLH"
    };

    // Inisialisasi Firebase
    firebase.initializeApp(firebaseConfig);
    const database = firebase.database();

    // Fungsi untuk mendapatkan base URL dari aplikasi
    function getBaseUrl() {
        return window.location.protocol + "//" + window.location.host;
    }
    
    // Simpan base URL untuk penggunaan global
    const baseUrl = getBaseUrl();

    // Ambil elemen dari DOM
    const updateButton = document.getElementById("updateControl");
    const modeAuto = document.getElementById("modeAuto");
    const solenoidManual = document.getElementById("solenoidManual");
    const ctx = document.getElementById("wateringChart").getContext("2d");

    if (!updateButton || !modeAuto || !solenoidManual || !ctx) {
        console.error("Salah satu elemen tidak ditemukan di DOM!");
        return;
    }

    // Tambahkan fitur: saat modeAuto aktif, solenoidManual dinonaktifkan
    modeAuto.addEventListener("change", function() {
        if (modeAuto.checked) {
            // Nonaktifkan mode manual
            solenoidManual.checked = false;
            solenoidManual.disabled = true;
        } else {
            // Aktifkan kembali mode manual
            solenoidManual.disabled = false;
        }
    });

    // Variabel untuk melacak status pengiriman email
    let emailSent = {
        siramStart: false,
        siramEnd: false,
        modeAutoOn: false,
        modeAutoOff: false
    };

    // Fungsi untuk mengirim email melalui API PHP
    function sendEmailToServer(subject, text) {
        console.log('Mengirim email dengan subjek:', subject);
        
        fetch(baseUrl + "/send-email", {
            method: "POST",
            headers: { 
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({ 
                subject: subject, 
                text: text 
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log("Email berhasil terkirim via PHP API");
            } else {
                console.error("Gagal mengirim email:", data.message || "Unknown error");
            }
            
            // Tampilkan pesan debug di console
            if (data.debug) {
                console.log('Debug email:', data.debug);
            }
        })
        .catch(error => console.error("Error fetch:", error));
    }

    // Fungsi untuk mengirim email saat mode auto diubah
    function sendModeAutoEmail(status) {
        if (!emailSent.modeAutoOn && status === true) {
            sendEmailToServer("Mode Auto Diaktifkan", "Mode Auto telah diaktifkan.");
            emailSent.modeAutoOn = true;
        } else if (!emailSent.modeAutoOff && status === false) {
            sendEmailToServer("Mode Auto Dinonaktifkan", "Mode Auto telah dinonaktifkan.");
            emailSent.modeAutoOff = true;
        }
    }

    // Fungsi untuk menyimpan data ke database history
    function saveHistoryData(moisture, rssi, modeAutoState, solenoidState) {
        console.log("Saving history data...");
        
        fetch(baseUrl + "/save-history-data", {
            method: "POST",
            headers: { 
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({ 
                soil_moisture: moisture,
                rssi: rssi,
                mode_auto: modeAutoState,
                solenoid_state: solenoidState
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log("Data history berhasil disimpan");
                
                // Refresh tabel history setelah menyimpan data baru
                if (document.getElementById('historyTableBody')) {
                    loadHistoryData();
                }
            } else {
                console.error("Gagal menyimpan data history:", data.message);
            }
        })
        .catch(error => console.error("Error fetch:", error));
    }

    // Inisialisasi JustGage untuk gauge bar kelembapan tanah
    const gauge = new JustGage({
        id: "gauge",
        value: 0,
        min: 0,
        max: 100,
        title: "Kelembaban Tanah",
        label: "%",
        customSectors: {
            percents: true,
            ranges: [
                { color: "#ff0000", lo: 0, hi: 30 },   // di bawah 30: merah
                { color: "#ffff00", lo: 30, hi: 60 },  // 30 sampai 60: kuning
                { color: "#00ff00", lo: 60, hi: 100 }  // 60 sampai 100: hijau
            ]
        }
    });

    // Inisialisasi Chart.js untuk grafik penyiraman
    const wateringChart = new Chart(ctx, {
        type: "line",
        data: {
            labels: [],
            datasets: [{
                label: "Kelembaban Tanah (%)",
                borderColor: "#007bff",
                backgroundColor: "rgba(0, 123, 255, 0.2)",
                data: [],
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: { title: { display: true, text: "Waktu" } },
                y: {
                    min: 0,
                    max: 100,
                    title: { display: true, text: "Persentase" }
                }
            }
        }
    });

    // Fungsi update kontrol menggunakan backend API
    updateButton.addEventListener("click", function () {
        console.log("Tombol Update Control diklik!");

        const autoMode = modeAuto.checked;
        const solenoidMode = solenoidManual.checked;

        // Gunakan API backend untuk update
        fetch(baseUrl + '/update-control', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                modeAuto: autoMode,
                solenoidManual: solenoidMode
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log("Data berhasil diperbarui melalui API backend!");
                alert("Update berhasil! Mode Auto: " + autoMode + " | Solenoid: " + solenoidMode);
            } else {
                console.error("Error update via API:", data.error);
                alert("Gagal update: " + (data.error || "Terjadi kesalahan"));
                
                // Fallback ke Firebase langsung jika API gagal
                updateDirectToFirebase(autoMode, solenoidMode);
            }
        })
        .catch(error => {
            console.error("Error fetch API:", error);
            
            // Fallback ke Firebase langsung jika API gagal
            updateDirectToFirebase(autoMode, solenoidMode);
        });
    });

    // Fungsi untuk update langsung ke Firebase sebagai fallback
    function updateDirectToFirebase(autoMode, solenoidMode) {
        database.ref("control").update({
            modeAuto: autoMode,
            solenoidManual: solenoidMode
        }).then(() => {
            console.log("Data berhasil diperbarui langsung ke Firebase!");
            alert("Update berhasil via Firebase langsung! Mode Auto: " + autoMode + " | Solenoid: " + solenoidMode);
        }).catch((error) => {
            console.error("Error update Firebase:", error);
            alert("Gagal update ke Firebase: " + error.message);
        });
    }

    // Menyimpan status solenoid sebelumnya untuk perbandingan
    let prevSolenoidState = false;
    let lastRecordTime = 0;
    
    // Sinkronisasi nilai dari Firebase ke UI setiap detik
    setInterval(() => {
        Promise.all([
            database.ref("sensor/soil_moisture").once("value"),
            database.ref("control/rssi").once("value"),
            database.ref("control/solenoid_state").once("value"),
            database.ref("control/siramStart").once("value"),
            database.ref("control/siramEnd").once("value"),
            database.ref("control/modeAuto").once("value")
        ])
        .then(([moistureSnapshot, rssiSnapshot, solenoidStateSnapshot, siramStartSnapshot, siramEndSnapshot, modeAutoSnapshot]) => {
            const now = new Date();
            const moisture = moistureSnapshot.exists() ? moistureSnapshot.val() : null;
            const rssi = rssiSnapshot.exists() ? rssiSnapshot.val() : null;
            const solenoidState = solenoidStateSnapshot.exists() ? solenoidStateSnapshot.val() : false;
            const modeAutoState = modeAutoSnapshot.exists() ? modeAutoSnapshot.val() : false;
            
            // Update gauge dan chart jika data kelembapan ada
            if (moisture !== null) {
                gauge.refresh(moisture);
                console.log("Kelembapan tanah diperbarui:", moisture);
    
                const timeString = now.toLocaleTimeString();
                if (wateringChart.data.labels.length >= 10) {
                    wateringChart.data.labels.shift();
                    wateringChart.data.datasets[0].data.shift();
                }
                wateringChart.data.labels.push(timeString);
                wateringChart.data.datasets[0].data.push(moisture);
                wateringChart.update();
            } else {
                console.warn("Data kelembapan tanah tidak ditemukan di Firebase!");
            }

            // Update info RSSI
            if (rssi !== null) {
                console.log("RSSI:", rssi);
            
                const rssiValueText = document.getElementById("rssiValueText");
                if (rssiValueText) {
                    rssiValueText.textContent = `${rssi} dBm`;
            
                    // Opsional: ubah warna tergantung sinyal
                    if (rssi >= -70) {
                        rssiValueText.style.color = "green";
                    } else if (rssi >= -85) {
                        rssiValueText.style.color = "orange";
                    } else {
                        rssiValueText.style.color = "red";
                    }
                }
            }
    
            // Update status solenoid
            if (solenoidState !== null) {
                console.log("Status Solenoid diperbarui:", solenoidState);
            
                // Update tampilan status solenoid di HTML
                const solenoidStatusText = document.getElementById("solenoidStatusText");
                if (solenoidStatusText) {
                    if (solenoidState === 1 || solenoidState === "1" || solenoidState === true) {
                        solenoidStatusText.textContent = "MENYALA"; // atau "Aktif" sesuai keinginanmu
                        solenoidStatusText.style.color = "green";
                    } else {
                        solenoidStatusText.textContent = "MATI"; // atau "Non-aktif"
                        solenoidStatusText.style.color = "red";
                    }
                }

                // Deteksi penyiraman otomatis dimulai atau selesai
                if (modeAuto.checked) {
                    if (!prevSolenoidState && solenoidState) {
                        console.log("Penyiraman otomatis dimulai!");
                        if (!emailSent.siramStart) {
                            sendEmailToServer("Penyiraman Otomatis Dimulai", "Penyiraman otomatis telah dimulai.");
                            emailSent.siramStart = true;
                        }
                    } else if (prevSolenoidState && !solenoidState) {
                        console.log("Penyiraman otomatis selesai!");
                        if (!emailSent.siramEnd) {
                            sendEmailToServer("Penyiraman Otomatis Selesai", "Penyiraman otomatis telah selesai.");
                            emailSent.siramEnd = true;
                        }
                    }
                    // RESET jika sudah selesai satu siklus
                    if (!solenoidState && emailSent.siramStart && emailSent.siramEnd) {
                        emailSent.siramStart = false;
                        emailSent.siramEnd = false;
                    }
                }

                // Simpan status solenoid untuk perbandingan berikutnya
                prevSolenoidState = solenoidState;
            } else {
                console.warn("Data status solenoid tidak ditemukan di Firebase!");
            }

            // Kirim email untuk mode auto jika berubah
            sendModeAutoEmail(modeAuto.checked);

            // Log informasi siramStart dan siramEnd jika tersedia
            if (siramStartSnapshot.exists()) {
                const siramStart = siramStartSnapshot.val();
                console.log("Siram Start diperbarui:", siramStart);
            }
    
            if (siramEndSnapshot.exists()) {
                const siramEnd = siramEndSnapshot.val();
                console.log("Siram End diperbarui:", siramEnd);
            }
            
            // Simpan data ke history setiap 5 menit
            const currentTime = now.getTime();
            if (currentTime - lastRecordTime >= 10000) { // 5 menit = 300000 ms
                saveHistoryData(moisture, rssi, modeAutoState, solenoidState);
                lastRecordTime = currentTime;
                console.log("Menyimpan data history pada:", now.toLocaleString());
            }
        })
        .catch((error) => {
            console.error("Terjadi kesalahan saat mengambil data dari Firebase:", error);
        });
    }, 1000); // setiap 1 detik

    // Initial fetch control values from Firebase
    database.ref("control").once("value")
        .then((snapshot) => {
            if (snapshot.exists()) {
                const data = snapshot.val();
                
                // Set toggle switches berdasarkan data dari Firebase
                if (data.modeAuto !== undefined) {
                    modeAuto.checked = data.modeAuto;
                    // Jika mode auto aktif, nonaktifkan solenoid manual
                    if (data.modeAuto) {
                        solenoidManual.disabled = true;
                    }
                }
                
                if (data.solenoidManual !== undefined) {
                    solenoidManual.checked = data.solenoidManual;
                }
                
                console.log("Kontrol awal diambil dari Firebase:", data);
            }
        })
        .catch((error) => {
            console.error("Error mengambil kontrol awal:", error);
        });

    // ====== Fungsi untuk bagian History ======

    // Inisialisasi timer untuk auto-refresh data history
    let historyRefreshTimer;
    
    function startHistoryAutoRefresh() {
        // Clear timer yang sudah ada (jika ada)
        if (historyRefreshTimer) {
            clearInterval(historyRefreshTimer);
        }
        
        // Set interval untuk refresh data setiap 1 menit
        historyRefreshTimer = setInterval(() => {
            console.log("Auto-refreshing history data...");
            if (document.getElementById('historyTableBody')) {
                loadHistoryData();
            } else {
                // Hentikan refresh jika elemen sudah tidak ada lagi
                clearInterval(historyRefreshTimer);
            }
        }, 60000); // 60000 ms = 1 menit
    }

    // Handler untuk filtering data history
    // Tombol filter
    const filterButton = document.getElementById('filterButton');
    if (filterButton) {
        filterButton.addEventListener('click', function() {
            loadHistoryData();
        });
    }
    
    // Tombol export
    const exportCsv = document.getElementById('exportCsv');
    if (exportCsv) {
        exportCsv.addEventListener('click', function() {
            exportHistoryData();
        });
    }
    
    // Header sorting
    const sortHeaders = document.querySelectorAll('.sort-header');
    if (sortHeaders) {
        sortHeaders.forEach(header => {
            header.addEventListener('click', function() {
                const column = this.getAttribute('data-sort');
                handleSortingClick(column);
            });
        });
    }
    
    // Set default tanggal jika belum dipilih
    const startDateInput = document.getElementById('start_date');
    if (startDateInput && !startDateInput.value) {
        const defaultDate = new Date();
        defaultDate.setDate(defaultDate.getDate() - 7);
        startDateInput.value = defaultDate.toISOString().split('T')[0];
    }
    
    const endDateInput = document.getElementById('end_date');
    if (endDateInput && !endDateInput.value) {
        const today = new Date();
        endDateInput.value = today.toISOString().split('T')[0];
    }

    // Load history data saat halaman dimuat
    if (document.getElementById('historyTableBody')) {
        loadHistoryData(); // Load data awal
        startHistoryAutoRefresh(); // Mulai auto-refresh
    }
});

// Fungsi untuk memuat data history via AJAX
function loadHistoryData() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    const sortBy = document.getElementById('sort_by').value;
    const sortDir = document.getElementById('sort_dir').value;
    const baseUrl = window.location.protocol + "//" + window.location.host;
    
    console.log("Loading history data...");
    
    // Tampilkan loading
    const tableBody = document.getElementById('historyTableBody');
    if (!tableBody) {
        console.error("Table body element not found!");
        return;
    }
    
    tableBody.innerHTML = '<tr><td colspan="5" class="no-data">Memuat data...</td></tr>';
    
    // Request data dari server
    const url = `${baseUrl}/get-history-data?start_date=${startDate}&end_date=${endDate}&sort_by=${sortBy}&sort_dir=${sortDir}`;
    console.log("Fetching from:", url);
    
    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => {
        console.log("Response status:", response.status);
        return response.json();
    })
    .then(data => {
        console.log("Data received:", data);
        
        // Validasi data
        if (!data || !data.success || !data.data || !Array.isArray(data.data)) {
            console.error("Invalid data format:", data);
            tableBody.innerHTML = '<tr><td colspan="5" class="no-data">Format data tidak valid</td></tr>';
            return;
        }
        
        // Cek jumlah data
        if (data.data.length === 0) {
            console.log("No data found");
            tableBody.innerHTML = '<tr><td colspan="5" class="no-data">Tidak ada data dalam rentang tanggal yang dipilih</td></tr>';
            return;
        }
        
        // Render data ke tabel
        console.log("Rendering", data.data.length, "rows");
        let html = '';
        
        data.data.forEach(row => {
            try {
                const timestamp = new Date(row.timestamp).toLocaleString();
                const moisture = row.soil_moisture !== null ? parseFloat(row.soil_moisture).toFixed(2) : 'N/A';
                const rssi = row.rssi !== null ? parseFloat(row.rssi).toFixed(2) : 'N/A';
                const modeAuto = row.mode_auto === 1 || row.mode_auto === true || row.mode_auto === '1'
                    ? '<span class="status-active">Aktif</span>' 
                    : '<span class="status-inactive">Non-aktif</span>';
                const solenoidState = row.solenoid_state === 1 || row.solenoid_state === true || row.solenoid_state === '1'
                    ? '<span class="status-active">Menyala</span>' 
                    : '<span class="status-inactive">Mati</span>';
                
                html += `
                    <tr>
                        <td>${timestamp}</td>
                        <td>${moisture}</td>
                        <td>${rssi}</td>
                        <td>${modeAuto}</td>
                        <td>${solenoidState}</td>
                    </tr>
                `;
            } catch (err) {
                console.error("Error processing row:", row, err);
            }
        });
        
        tableBody.innerHTML = html;
        
        // Update header sorting
        updateSortingClasses(sortBy, sortDir);
    })
    .catch(error => {
        console.error('Error loading history data:', error);
        tableBody.innerHTML = '<tr><td colspan="5" class="no-data">Error: ' + error.message + '</td></tr>';
    });
}

// Fungsi untuk export data ke CSV
function exportHistoryData() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    const sortBy = document.getElementById('sort_by').value;
    const sortDir = document.getElementById('sort_dir').value;
    const baseUrl = window.location.protocol + "//" + window.location.host;
    
    // Redirect ke endpoint export
    window.location.href = `${baseUrl}/export-history?start_date=${startDate}&end_date=${endDate}&sort_by=${sortBy}&sort_dir=${sortDir}`;
}

// Fungsi untuk menangani klik pada header sorting
function handleSortingClick(column) {
    const currentSortBy = document.getElementById('sort_by').value;
    const currentSortDir = document.getElementById('sort_dir').value;
    
    let newSortDir = 'asc';
    if (currentSortBy === column) {
        // Toggle arah sorting jika kolom yang sama
        newSortDir = currentSortDir === 'asc' ? 'desc' : 'asc';
    }
    
    // Update nilai form
    document.getElementById('sort_by').value = column;
    document.getElementById('sort_dir').value = newSortDir;
    
    // Load data
    loadHistoryData();
}

// Fungsi untuk update kelas sorting pada header
function updateSortingClasses(sortBy, sortDir) {
    const headers = document.querySelectorAll('.sort-header');
    headers.forEach(header => {
        const column = header.getAttribute('data-sort');
        header.classList.remove('sort-asc', 'sort-desc');
        
        if (column === sortBy) {
            header.classList.add(sortDir === 'asc' ? 'sort-asc' : 'sort-desc');
        }
    });
}