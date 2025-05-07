<?php
// app/Config/Email.php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Email extends BaseConfig
{
    public $fromEmail = 'nhexania@gmail.com';
    public $fromName = 'SWAT Monitoring System';
    public $protocol = 'smtp';
    public $SMTPHost = 'smtp.gmail.com'; // Perbaiki: smtp.gmail.com, bukan smtp.google.com
    public $SMTPUser = 'nhexania@gmail.com'; // Perbaiki: gunakan email Gmail Anda, bukan 'apikey'
    public $SMTPPass = 'fuzsezmfetnwjezl'; // App password Gmail yang Anda berikan
    public $SMTPPort = 587;
    public $SMTPCrypto = 'tls';
    public $mailType = 'html';
    public $charset = 'UTF-8';
    public $wordWrap = true;
    
    // Tambahkan ini untuk memastikan koneksi berjalan dengan benar
    public $SMTPTimeout = 60;
    public $SMTPKeepAlive = false;
}