<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;
use Config\Email as EmailConfig;

class EmailTest extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'email:test';
    protected $description = 'Tes pengiriman email.';
    protected $usage       = 'email:test [email_penerima]';
    protected $arguments   = [
        'email_penerima' => 'Alamat email penerima untuk tes',
    ];

    public function run(array $params)
    {
        $email = $params[0] ?? 'nhexania@gmail.com';
        
        CLI::write('Menguji pengiriman email ke: ' . $email, 'yellow');
        
        // Informasi konfigurasi
        $config = new EmailConfig();
        CLI::write('Protokol Email: ' . $config->protocol, 'green');
        CLI::write('SMTP Host: ' . $config->SMTPHost, 'green');
        CLI::write('SMTP Port: ' . $config->SMTPPort, 'green');
        CLI::write('SMTP User: ' . $config->SMTPUser, 'green');
        CLI::write('SMTP Pass: ' . str_repeat('*', strlen($config->SMTPPass)), 'green');
        
        // Kirim email
        CLI::write('Mencoba mengirim email...', 'yellow');
        
        try {
            $emailService = Services::email();
            $emailService->setTo($email);
            $emailService->setFrom($config->fromEmail, $config->fromName);
            $emailService->setSubject('Test Email dari CLI - ' . date('Y-m-d H:i:s'));
            $emailService->setMessage('Ini adalah test email dari command line pada ' . date('Y-m-d H:i:s'));
            
            $result = $emailService->send(false);
            
            if ($result) {
                CLI::write('Email berhasil dikirim!', 'green');
                return EXIT_SUCCESS;
            } else {
                CLI::error('Gagal mengirim email!');
                CLI::write('Debug Info:', 'yellow');
                CLI::write($emailService->printDebugger(['headers', 'subject', 'body']), 'red');
                return EXIT_ERROR;
            }
        } catch (\Exception $e) {
            CLI::error('Error: ' . $e->getMessage());
            return EXIT_ERROR;
        }
    }
}