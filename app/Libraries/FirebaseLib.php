<?php

namespace App\Libraries;

use Kreait\Firebase\Factory;
use CodeIgniter\Config\Services;
use Config\Firebase as FirebaseConfig;
use Exception;

class FirebaseLib
{
    /**
     * Firebase database instance
     */
    protected $database;
    
    /**
     * Firebase config 
     */
    protected $config;
    
    /**
     * Error message
     */
    protected $error;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->config = new FirebaseConfig();
        
        try {
            // Log time info untuk debugging
            log_message('info', 'Server time: ' . date('Y-m-d H:i:s'));
            
            // Validasi file kredensial
            if (!file_exists($this->config->credentialsPath)) {
                throw new Exception('Firebase credentials file tidak ditemukan: ' . $this->config->credentialsPath);
            }
            
            // Inisialisasi Firebase
            $factory = (new Factory())
                ->withServiceAccount($this->config->credentialsPath)
                ->withDatabaseUri($this->config->databaseURL);
            
            $this->database = $factory->createDatabase();
            log_message('info', 'Firebase berhasil diinisialisasi');
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            log_message('error', 'Firebase error: ' . $e->getMessage());
        }
    }
    
    /**
     * Cek apakah koneksi berhasil
     */
    public function isConnected()
    {
        return ($this->database !== null);
    }
    
    /**
     * Dapatkan pesan error
     */
    public function getError()
    {
        return $this->error;
    }
    
    /**
     * Dapatkan database instance
     */
    public function getDatabase()
    {
        return $this->database;
    }
    
    /**
     * Ambil data dari Firebase
     */
    public function getData($path)
    {
        try {
            if (!$this->isConnected()) {
                throw new Exception('Firebase tidak terhubung');
            }
            
            $reference = $this->database->getReference($path);
            return $reference->getValue();
        } catch (Exception $e) {
            log_message('error', "Firebase getData error ($path): " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Set data ke Firebase
     */
    public function setData($path, $data)
    {
        try {
            if (!$this->isConnected()) {
                throw new Exception('Firebase tidak terhubung');
            }
            
            $reference = $this->database->getReference($path);
            $reference->set($data);
            return true;
        } catch (Exception $e) {
            log_message('error', "Firebase setData error ($path): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update data di Firebase
     */
    public function updateData($path, $data)
    {
        try {
            if (!$this->isConnected()) {
                throw new Exception('Firebase tidak terhubung');
            }
            
            $reference = $this->database->getReference($path);
            $reference->update($data);
            return true;
        } catch (Exception $e) {
            log_message('error', "Firebase updateData error ($path): " . $e->getMessage());
            return false;
        }
    }
}