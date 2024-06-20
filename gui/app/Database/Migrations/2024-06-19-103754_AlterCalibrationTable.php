<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterCalibrationTable extends Migration
{
    public function up()
    {
        $this->forge->dropTable('calibrations', true);
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'parameter_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'calibration_type' => [
                'type' => 'smallint',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'default' => 0, //0 = Zero , 1 = Span
            ],
            'target_value' => [
                'type' => 'DOUBLE',
                'null' => true,
            ],
            'notes' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'value_before' => [
                'type' => 'DOUBLE',
                'null' => true,
            ],
            'value_after' => [
                'type' => 'DOUBLE',
                'null' => true,
            ],
            'start_calibration' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
            ],            
            'end_calibration' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
            ],  
            'xtimestamp timestamp NOT NULL'
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('parameter_id');
        $this->forge->createTable('calibrations', true);
    }

    public function down()
    {
        $this->forge->dropTable('calibrations', true);
    }
}
