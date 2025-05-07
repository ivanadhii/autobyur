<?php

namespace App\Controllers;

class SystemCheck extends BaseController
{
    /**
     * Halaman system check untuk mendiagnosis masalah Firebase
     */
    public function index()
    {
        $data = [];
        
        // Cek waktu server
        $serverTime = time();
        $googleTime = $this->getGoogleTime();
        $timeDiff = abs($serverTime - $googleTime);
        
        $data['serverTime'] = date('Y-m-d H:i:s', $serverTime);
        $data['googleTime'] = date('Y-m-d H:i:s', $googleTime);
        $data['timeDiff'] = $timeDiff;
        $data['timeOk'] = $timeDiff < 60; // Perbedaan kurang dari 1 menit
        
        // Cek versi cURL
        $curlVersion = curl_version();
        $data['curlVersion'] = $curlVersion['version'];
        $data['curlOk'] = version_compare($curlVersion['version'], '7.67.0', '>=');
        
        // Cek kredensial Firebase
        $credentialsPath = ROOTPATH . 'service-account.json';
        $data['credentialsExist'] = file_exists($credentialsPath);
        
        if ($data['credentialsExist']) {
            $credentials = json_decode(file_get_contents($credentialsPath), true);
            $data['credentialsValid'] = !empty($credentials['type']) && 
                                       !empty($credentials['project_id']) && 
                                       !empty($credentials['private_key']);
            $data['projectId'] = $credentials['project_id'] ?? 'unknown';
        } else {
            $data['credentialsValid'] = false;
            $data['projectId'] = 'unknown';
        }
        
        // Test koneksi Firebase
        helper('firebase');
        $firebase = firebase_instance();
        $data['firebaseConnected'] = $firebase->isConnected();
        $data['firebaseError'] = $firebase->getError();
        
        return view('system_check', $data);
    }
    
    /**
     * Ambil waktu dari server Google untuk sinkronisasi
     */
    private function getGoogleTime()
    {
        try {
            // Tambahkan backslash di depan curl_init agar menggunakan fungsi global
            $ch = \curl_init('https://www.google.com');
            \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            \curl_setopt($ch, CURLOPT_HEADER, true);
            \curl_setopt($ch, CURLOPT_NOBODY, true);
            \curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = \curl_exec($ch);
            $headerSize = \curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            \curl_close($ch);
            
            $headers = substr($response, 0, $headerSize);
            preg_match('/^Date: (.*)$/m', $headers, $matches);
            
            if (isset($matches[1])) {
                return strtotime(trim($matches[1]));
            }
        } catch (\Exception $e) {
            log_message('error', 'Error getting Google time: ' . $e->getMessage());
        }
        
        return time(); // Fallback ke waktu server
    }

    /**
     * Fix waktu server (untuk server Linux)
     */
    public function fixTime()
    {
        if (PHP_OS_FAMILY === 'Linux') {
            try {
                // Coba sinkronisasi waktu menggunakan ntpdate
                $output = [];
                $result = -1;
                
                exec('which ntpdate', $output, $result);
                if ($result === 0) {
                    exec('sudo ntpdate time.google.com 2>&1', $output, $result);
                    $message = implode("\n", $output);
                    
                    if ($result === 0) {
                        return $this->response->setJSON([
                            'success' => true,
                            'message' => 'Waktu server berhasil disinkronkan',
                            'output' => $message
                        ]);
                    } else {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Gagal menyinkronkan waktu server',
                            'output' => $message
                        ]);
                    }
                } else {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'ntpdate tidak terinstal di server ini',
                        'output' => 'Silakan instal ntpdate: sudo apt-get install ntpdate'
                    ]);
                }
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ]);
            }
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Function ini hanya berfungsi di server Linux'
            ]);
        }
    }
}