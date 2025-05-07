<?php
// app/Database/Migrations/2025-05-07-000000_create_history_table.php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHistoryTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INTEGER',
                'auto_increment' => true,
            ],
            'timestamp' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'soil_moisture' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'rssi' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'mode_auto' => [
                'type' => 'INTEGER',
                'constraint' => 1,
                'default' => 0,
            ],
            'solenoid_state' => [
                'type' => 'INTEGER',
                'constraint' => 1,
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addKey(['timestamp']);
        
        $this->forge->createTable('history_data');
    }

    public function down()
    {
        $this->forge->dropTable('history_data');
    }
}