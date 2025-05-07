<?php
// app/Controllers/History.php

namespace App\Controllers;

use App\Models\HistoryModel;
use CodeIgniter\I18n\Time;

class History extends BaseController
{
    protected $historyModel;
    
    public function __construct()
    {
        helper('firebase');
        $this->historyModel = new HistoryModel();
    }
    
    /**
     * Tampilkan halaman history
     */
    public function index()
    {
        // Ambil parameter filter
        $startDate = $this->request->getGet('start_date') ?? null;
        $endDate = $this->request->getGet('end_date') ?? null;
        $sortBy = $this->request->getGet('sort_by') ?? 'timestamp';
        $sortDir = $this->request->getGet('sort_dir') ?? 'desc';
        
        // Validasi kolom sort
        $allowedColumns = ['timestamp', 'soil_moisture', 'rssi', 'mode_auto', 'solenoid_state'];
        if (!in_array($sortBy, $allowedColumns)) {
            $sortBy = 'timestamp';
        }
        
        // Validasi arah sort
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'desc';
        }
        
        // Jika tidak ada tanggal, set default ke 7 hari terakhir
        if (!$startDate) {
            $startDate = date('Y-m-d', strtotime('-7 days'));
        }
        
        if (!$endDate) {
            $endDate = date('Y-m-d');
        }
        
        // Ambil data history
        $data = [
            'title' => 'SWAT Monitoring - History Data',
            'history' => $this->historyModel->getHistory($startDate, $endDate, $sortBy, $sortDir),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir
        ];
        
        return view('history', $data);
    }
    
    /**
     * Endpoint untuk menyimpan data baru dari Firebase
     */
    public function saveData()
    {
        // Cek apakah request AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Direct access not allowed']);
        }
        
        // Ambil data dari Firebase
        try {
            $sensor = firebase_get('sensor');
            $control = firebase_get('control');
            
            // Gabungkan data
            $data = [
                'soil_moisture' => $sensor['soil_moisture'] ?? null,
                'rssi' => $control['rssi'] ?? null,
                'mode_auto' => $control['modeAuto'] ?? 0,
                'solenoid_state' => $control['solenoid_state'] ?? 0
            ];
            
            // Simpan ke database
            $result = $this->historyModel->saveFromFirebase($data);
            
            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Data berhasil disimpan',
                    'data' => $data
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal menyimpan data'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Export data history ke CSV
     */
    public function export()
    {
        // Ambil parameter filter
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-d', strtotime('-7 days'));
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-d');
        $sortBy = $this->request->getGet('sort_by') ?? 'timestamp';
        $sortDir = $this->request->getGet('sort_dir') ?? 'desc';
        
        // Ambil data
        $data = $this->historyModel->getHistory($startDate, $endDate, $sortBy, $sortDir);
        
        // Siapkan header CSV
        $filename = 'swat_history_' . date('Ymd') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Buat output CSV
        $output = fopen('php://output', 'w');
        
        // Tulis header
        fputcsv($output, ['Timestamp', 'Kelembaban Tanah (%)', 'RSSI (dBm)', 'Mode Auto', 'Status Solenoid']);
        
        // Tulis data
        foreach ($data as $row) {
            fputcsv($output, [
                $row['timestamp'],
                $row['soil_moisture'],
                $row['rssi'],
                $row['mode_auto'] ? 'Aktif' : 'Non-aktif',
                $row['solenoid_state'] ? 'Menyala' : 'Mati'
            ]);
        }
        
        fclose($output);
        exit;
    }
}