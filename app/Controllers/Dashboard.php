<?php

namespace App\Controllers;

use App\Libraries\HistoryHandler;
use CodeIgniter\HTTP\ResponseInterface;

class Dashboard extends BaseController
{
    protected $firebase;
    protected $historyHandler;

    public function __construct()
    {
        helper('firebase');
        $this->firebase = firebase_instance();
        $this->historyHandler = new HistoryHandler();
    }

    public function index()
    {
        // Cek koneksi Firebase
        if (!$this->firebase->isConnected()) {
            $error = $this->firebase->getError();
            return view('errors/firebase_error', ['error' => $error]);
        }

        // Ambil parameter filter
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-d', strtotime('-7 days'));
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-d');
        $sortBy = $this->request->getGet('sort_by') ?? 'timestamp';
        $sortDir = $this->request->getGet('sort_dir') ?? 'desc';
        
        // Ambil data history
        $history = $this->historyHandler->getHistory($startDate, $endDate, $sortBy, $sortDir, 50);

        $data = [
            'title' => 'SWAT Monitoring - Kementerian PUPR',
            'baseUrl' => base_url(),
            'history' => $history,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir
        ];

        return view('dashboard', $data);
    }

    /**
     * API untuk update control
     */
    public function updateControl()
    {
        // Cek apakah request AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Direct access not allowed']);
        }

        // Cek koneksi Firebase
        if (!$this->firebase->isConnected()) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'error' => 'Firebase connection error: ' . $this->firebase->getError()
            ]);
        }

        // Ambil data dari request
        $json = $this->request->getJSON(true);
        if (empty($json)) {
            $json = $this->request->getPost();
        }

        $modeAuto = (isset($json['modeAuto']) && ($json['modeAuto'] === true || $json['modeAuto'] === 'true')) ? true : false;
        $solenoidManual = (isset($json['solenoidManual']) && ($json['solenoidManual'] === true || $json['solenoidManual'] === 'true')) ? true : false;

        // Update ke Firebase
        $result = firebase_update('control', [
            'modeAuto' => $modeAuto,
            'solenoidManual' => $solenoidManual,
        ]);

        // Return response
        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Data berhasil diupdate',
                'data' => [
                    'modeAuto' => $modeAuto,
                    'solenoidManual' => $solenoidManual,
                ]
            ]);
        } else {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'error' => 'Failed to update Firebase'
            ]);
        }
    }

    /**
     * API untuk mendapatkan data history
     */
    public function getHistoryData()
    {
        try {
            // Ambil parameter filter
            $startDate = $this->request->getGet('start_date') ?? date('Y-m-d', strtotime('-7 days'));
            $endDate = $this->request->getGet('end_date') ?? date('Y-m-d');
            $sortBy = $this->request->getGet('sort_by') ?? 'timestamp';
            $sortDir = $this->request->getGet('sort_dir') ?? 'desc';
            
            // Log for debugging
            log_message('info', "getHistoryData: $startDate to $endDate, sort by $sortBy $sortDir");
            
            // Ambil data history
            $history = $this->historyHandler->getHistory($startDate, $endDate, $sortBy, $sortDir, 50);
            
            // Log data count
            log_message('info', "getHistoryData: Found " . count($history) . " records");
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $history,
                'count' => count($history)
            ]);
        } catch (\Exception $e) {
            log_message('error', "getHistoryData error: " . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Endpoint untuk menyimpan data baru dari Firebase
     */
    public function saveHistoryData()
    {
        // Ambil data dari request
        $json = $this->request->getJSON(true);
        if (empty($json)) {
            $json = $this->request->getPost();
        }
        
        // Log data yang diterima
        log_message('info', 'saveHistoryData: ' . json_encode($json));
        
        try {
            // Simpan ke file data
            $result = $this->historyHandler->saveData([
                'soil_moisture' => $json['soil_moisture'] ?? null,
                'rssi' => $json['rssi'] ?? null,
                'mode_auto' => $json['mode_auto'] ?? 0,
                'solenoid_state' => $json['solenoid_state'] ?? 0
            ]);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Data berhasil disimpan',
                'id' => $result
            ]);
        } catch (\Exception $e) {
            log_message('error', 'saveHistoryData error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Export data history ke CSV
     */
    public function exportHistoryCsv()
    {
        // Ambil parameter filter
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-d', strtotime('-7 days'));
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-d');
        $sortBy = $this->request->getGet('sort_by') ?? 'timestamp';
        $sortDir = $this->request->getGet('sort_dir') ?? 'desc';
        
        // Debug mode
        $debug = $this->request->getGet('debug') === 'true';
        
        try {
            // Ambil data
            $history = $this->historyHandler->getHistory($startDate, $endDate, $sortBy, $sortDir, 0);
            
            if ($debug) {
                // Return data sebagai JSON untuk debugging
                return $this->response->setJSON([
                    'success' => true,
                    'data' => $history,
                    'count' => count($history),
                    'params' => [
                        'startDate' => $startDate,
                        'endDate' => $endDate,
                        'sortBy' => $sortBy,
                        'sortDir' => $sortDir
                    ]
                ]);
            }
            
            // Siapkan header CSV
            $filename = 'swat_history_' . date('Ymd') . '.csv';
            
            // Memori buffer untuk CSV
            $output = fopen('php://temp', 'w+');
            
            // Header CSV
            fputcsv($output, ['Timestamp', 'Kelembaban Tanah (%)', 'RSSI (dBm)', 'Mode Auto', 'Status Solenoid']);
            
            // Tulis data
            foreach ($history as $row) {
                fputcsv($output, [
                    $row['timestamp'],
                    $row['soil_moisture'],
                    $row['rssi'],
                    $row['mode_auto'] ? 'Aktif' : 'Non-aktif',
                    $row['solenoid_state'] ? 'Menyala' : 'Mati'
                ]);
            }
            
            // Reset pointer dan baca isi
            rewind($output);
            $csv = stream_get_contents($output);
            fclose($output);
            
            // Set headers dan kirim
            return $this->response
                ->setHeader('Content-Type', 'text/csv')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setBody($csv);
                
        } catch (\Exception $e) {
            log_message('error', 'exportHistoryCsv error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}