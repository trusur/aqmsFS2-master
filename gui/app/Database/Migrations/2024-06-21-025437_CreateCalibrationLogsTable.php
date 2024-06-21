<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCalibrationLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'calibration_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'parameter_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true
            ],
            'value' => [
                'type' => 'double',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'datetime',
                'null' => true
            ],
            'xtimestamp timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()'
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('calibration_id');
        $this->forge->createTable('calibration_logs',true);
    }

    public function down()
    {
        $this->forge->dropTable('calibration_logs');
    }
}
