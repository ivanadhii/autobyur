<?php

namespace App\Libraries;

use CodeIgniter\Config\Services;
use Config\Email as EmailConfig;

class EmailService
{
    protected $email;
    protected $debugMessage;
    
    public function __construct()
    {
        $config = new EmailConfig();
        $this->email = Services::email($config);
        $this->debugMessage = '';
    }
    
    /**
     * Kirim email
     *
     * @param string $to Alamat email penerima (default: nhexania@gmail.com)
     * @param string $subject Subjek email
     * @param string $message Isi pesan email
     * @return bool Berhasil atau tidak
     */
    public function send($to = 'nhexania@gmail.com', $subject, $message)
    {
        try {
            // Setup email
            $this->email->setTo($to);
            $this->email->setSubject($subject);
            $this->email->setMessage($message);
            
            // Coba kirim
            $result = $this->email->send(false); // false agar tidak langsung exit
            
            // Simpan debug message
            $this->debugMessage = $this->email->printDebugger(['headers', 'subject', 'body']);
            
            if ($result) {
                log_message('info', 'Email berhasil dikirim ke: ' . $to);
                return true;
            } else {
                log_message('error', 'Gagal mengirim email: ' . $this->debugMessage);
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Error mengirim email: ' . $e->getMessage());
            $this->debugMessage = $e->getMessage() . "\n" . $e->getTraceAsString();
            return false;
        }
    }
    
    /**
     * Dapatkan pesan debug dari proses pengiriman email
     * 
     * @return string
     */
    public function getDebugMessage()
    {
        return $this->debugMessage;
    }
}