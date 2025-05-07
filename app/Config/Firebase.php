<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Firebase extends BaseConfig
{
    /**
     * Path ke file service-account.json
     */
    public $credentialsPath;

    /**
     * URL database Firebase
     */
    public $databaseURL;

    public function __construct()
    {
        $this->credentialsPath = ROOTPATH . 'service-account.json';
        $this->databaseURL = 'https://swtm-53c68-default-rtdb.asia-southeast1.firebasedatabase.app';
    }
}