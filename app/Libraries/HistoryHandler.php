<?php

namespace App\Libraries;

class HistoryHandler
{
    protected $historyFile;
    
    public function __construct()
    {
        $this->historyFile = WRITEPATH . 'history/data.json';
        
        // Buat direktori jika belum ada
        if (!is_dir(dirname($this->historyFile))) {
            mkdir(dirname($this->historyFile), 0777, true);
        }
        
        // Buat file jika belum ada
        if (!file_exists($this->historyFile)) {
            file_put_contents($this->historyFile, json_encode([]));
            chmod($this->historyFile, 0666);
        }
    }
    
    /**
     * Ambil data history
     */
    public function getHistory($startDate = null, $endDate = null, $sortBy = 'timestamp', $sortDir = 'desc', $limit = 50)
    {
        // Log input parameters
        log_message('debug', "getHistory called with: startDate=$startDate, endDate=$endDate, sortBy=$sortBy, sortDir=$sortDir, limit=$limit");
        
        // Check if file exists
        if (!file_exists($this->historyFile)) {
            log_message('info', "History file not found, creating empty file");
            file_put_contents($this->historyFile, json_encode([]));
            chmod($this->historyFile, 0666);
            return [];
        }
        
        // Read file with validation
        $jsonData = file_get_contents($this->historyFile);
        log_message('debug', "Raw JSON data length: " . strlen($jsonData));
        
        if (empty($jsonData)) {
            log_message('info', "Empty JSON data, returning empty array");
            return [];
        }
        
        // Decode JSON
        $data = json_decode($jsonData, true);
        if (!is_array($data)) {
            log_message('error', "Invalid JSON data: " . json_last_error_msg());
            return [];
        }
        
        log_message('info', "Loaded " . count($data) . " records from file");
        
        // Filter by date
        if ($startDate) {
            $startTimestamp = strtotime($startDate);
            $data = array_filter($data, function($item) use ($startTimestamp) {
                return strtotime(substr($item['timestamp'], 0, 10)) >= $startTimestamp;
            });
        }
        
        if ($endDate) {
            $endTimestamp = strtotime($endDate) + 86400; // Include the whole end day
            $data = array_filter($data, function($item) use ($endTimestamp) {
                return strtotime(substr($item['timestamp'], 0, 10)) <= $endTimestamp;
            });
        }
        
        // Sort data
        $sortFunction = function($a, $b) use ($sortBy, $sortDir) {
            // Handle null values
            $aValue = isset($a[$sortBy]) ? $a[$sortBy] : null;
            $bValue = isset($b[$sortBy]) ? $b[$sortBy] : null;
            
            // If both null, they're equal
            if ($aValue === null && $bValue === null) return 0;
            
            // Null values go last in ascending, first in descending
            if ($aValue === null) return ($sortDir === 'asc') ? 1 : -1;
            if ($bValue === null) return ($sortDir === 'asc') ? -1 : 1;
            
            // Special handling for timestamp
            if ($sortBy === 'timestamp') {
                $aTime = strtotime($aValue);
                $bTime = strtotime($bValue);
                $result = ($aTime <=> $bTime);
            } else {
                $result = ($aValue <=> $bValue);
            }
            
            return ($sortDir === 'asc') ? $result : -$result;
        };
        
        usort($data, $sortFunction);
        
        // Apply limit
        if ($limit > 0 && count($data) > $limit) {
            $data = array_slice($data, 0, $limit);
        }
        
        // Return result array with reset keys
        return array_values($data);
    }
    
    /**
     * Simpan data baru
     */
    public function saveData($data)
    {
        // Log for debugging
        log_message('info', 'Saving history data: ' . json_encode($data));
        
        // Baca data yang sudah ada
        $filePath = $this->historyFile;
        
        // Periksa dan buat file jika belum ada
        if (!file_exists($filePath)) {
            log_message('info', 'Creating new history file');
            if (!is_dir(dirname($filePath))) {
                mkdir(dirname($filePath), 0777, true);
            }
            file_put_contents($filePath, json_encode([]));
            chmod($filePath, 0666);
        }
        
        // Baca data
        $jsonData = file_get_contents($filePath);
        $history = json_decode($jsonData, true);
        
        // Validasi format data
        if (!is_array($history)) {
            log_message('warning', 'Invalid history data format, resetting to empty array');
            $history = [];
        }
        
        // Buat ID unik untuk data baru
        $id = uniqid();
        
        // Tambahkan data baru
        $newData = [
            'id' => $id,
            'timestamp' => date('Y-m-d H:i:s'),
            'soil_moisture' => $data['soil_moisture'],
            'rssi' => $data['rssi'],
            'mode_auto' => $data['mode_auto'] ? 1 : 0,
            'solenoid_state' => $data['solenoid_state'] ? 1 : 0
        ];
        
        // Tambahkan di awal array (untuk mempermudah sorting desc by timestamp)
        array_unshift($history, $newData);
        
        // Batasi jumlah data (opsional - maksimal 1000 data)
        if (count($history) > 1000) {
            $history = array_slice($history, 0, 1000);
        }
        
        // Simpan kembali ke file
        $result = file_put_contents($filePath, json_encode($history));
        
        if ($result === false) {
            log_message('error', 'Failed to write history data to file');
            throw new \Exception('Failed to save history data');
        }
        
        log_message('info', 'History data saved successfully with ID: ' . $id);
        return $id;
    }
}