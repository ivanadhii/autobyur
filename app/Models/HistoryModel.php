<?php
// app/Models/HistoryModel.php

namespace App\Models;

use CodeIgniter\Model;

class HistoryModel extends Model
{
    protected $table      = 'history_data';
    protected $primaryKey = 'id';
    
    protected $useAutoIncrement = true;
    
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'timestamp', 'soil_moisture', 'rssi', 
        'mode_auto', 'solenoid_state'
    ];
    
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    
    /**
     * Ambil data history dengan filter tanggal
     */
    public function getHistory($startDate = null, $endDate = null, $sortBy = 'timestamp', $sortDir = 'desc', $limit = 50)
    {
        $builder = $this->builder();
        
        // Filter tanggal jika disediakan (perhatikan format SQLite)
        if ($startDate) {
            $builder->where("date(timestamp) >= date('$startDate')");
        }
        
        if ($endDate) {
            $builder->where("date(timestamp) <= date('$endDate')");
        }
        
        // Validasi kolom sort
        $allowedColumns = ['timestamp', 'soil_moisture', 'rssi', 'mode_auto', 'solenoid_state'];
        if (!in_array($sortBy, $allowedColumns)) {
            $sortBy = 'timestamp';
        }
        
        // Validasi arah sort
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'desc';
        }
        
        // Sorting
        $builder->orderBy($sortBy, $sortDir);
        
        // Limit jumlah data
        if ($limit > 0) {
            $builder->limit($limit);
        }
        
        return $builder->get()->getResultArray();
    }
    
    /**
     * Simpan data baru dari Firebase
     */
    public function saveFromFirebase($data)
    {
        $record = [
            'timestamp' => date('Y-m-d H:i:s'),
            'soil_moisture' => $data['soil_moisture'] ?? null,
            'rssi' => $data['rssi'] ?? null,
            'mode_auto' => $data['mode_auto'] ?? 0,
            'solenoid_state' => $data['solenoid_state'] ?? 0
        ];
        
        return $this->insert($record);
    }
}