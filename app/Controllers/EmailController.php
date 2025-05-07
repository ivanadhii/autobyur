<?php

namespace App\Controllers;

use App\Libraries\EmailService;
use CodeIgniter\API\ResponseTrait;

class EmailController extends BaseController
{
    use ResponseTrait;
    
    protected $emailService;
    
    public function __construct()
    {
        $this->emailService = new EmailService();
    }
    
    /**
     * Endpoint untuk mengirim email
     */
    public function send()
    {
        // Cek apakah request AJAX
        if (!$this->request->isAJAX()) {
            return $this->failUnauthorized('Direct access not allowed');
        }
        
        // Ambil data dari request
        $json = $this->request->getJSON(true);
        if (empty($json)) {
            $json = $this->request->getPost();
        }
        
        // Validasi input
        if (empty($json['subject']) || empty($json['text'])) {
            return $this->fail('Missing required fields: subject, text');
        }
        
        // Kirim email dengan penerima default sesuai dengan server.js
        $result = $this->emailService->send(
            'nhexania@gmail.com', // Penerima email default sesuai server.js
            $json['subject'],
            $json['text']
        );
        
        // Dapatkan pesan debug
        $debugMessage = $this->emailService->getDebugMessage();
        
        // Return response dengan debug message
        if ($result) {
            return $this->respond([
                'success' => true,
                'message' => 'Email berhasil dikirim',
                'debug' => $debugMessage
            ]);
        } else {
            return $this->fail([
                'success' => false,
                'message' => 'Gagal mengirim email',
                'debug' => $debugMessage
            ]);
        }
    }
    
    /**
     * Endpoint untuk testing email
     */
    public function test()
    {
        // Kirim email test
        $result = $this->emailService->send(
            'nhexania@gmail.com',
            'Test Email dari SWAT Monitoring - ' . date('Y-m-d H:i:s'),
            'Ini adalah test email dari sistem SWAT Monitoring pada ' . date('Y-m-d H:i:s')
        );
        
        // Dapatkan pesan debug
        $debugMessage = $this->emailService->getDebugMessage();
        
        // Tampilkan hasil dan debug message
        return $this->respond([
            'success' => $result,
            'message' => $result ? 'Email test berhasil dikirim' : 'Gagal mengirim email test',
            'debug' => $debugMessage
        ]);
    }
}