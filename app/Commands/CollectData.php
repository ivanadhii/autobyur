<?php
// app/Commands/CollectData.php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\HistoryModel;

class CollectData extends BaseCommand
{
    protected $group       = 'SWAT';
    protected $name        = 'swat:collect';
    protected $description = 'Mengumpulkan data dari Firebase dan menyimpannya ke database.';
    
    public function run(array $params)
    {
        helper('firebase');
        $model = new HistoryModel();
        
        CLI::write('Mengumpulkan data dari Firebase...', 'yellow');
        
        try {
            // Ambil data dari Firebase
            $sensor = firebase_get('sensor');
            $control = firebase_get('control');
            
            if (!$sensor || !$control) {
                CLI::error('Gagal mendapatkan data dari Firebase.');
                return;
            }
            
            // Gabungkan data
            $data = [
                'soil_moisture' => $sensor['soil_moisture'] ?? null,
                'rssi' => $control['rssi'] ?? null,
                'mode_auto' => $control['modeAuto'] ?? 0,
                'solenoid_state' => $control['solenoid_state'] ?? 0
            ];
            
            // Simpan ke database
            $result = $model->saveFromFirebase($data);
            
            if ($result) {
                CLI::write('Data berhasil disimpan.', 'green');
                CLI::write('Kelembaban Tanah: ' . ($data['soil_moisture'] ?? 'N/A'));
                CLI::write('RSSI: ' . ($data['rssi'] ?? 'N/A') . ' dBm');
                CLI::write('Mode Auto: ' . ($data['mode_auto'] ? 'Aktif' : 'Non-aktif'));
                CLI::write('Status Solenoid: ' . ($data['solenoid_state'] ? 'Menyala' : 'Mati'));
            } else {
                CLI::error('Gagal menyimpan data.');
            }
        } catch (\Exception $e) {
            CLI::error('Error: ' . $e->getMessage());
        }
    }
}