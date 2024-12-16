<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTableLogSent extends Migration
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
            'parameter_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'value' => [
                'type' => 'DOUBLE',
                'null' => true,
            ],
            'sensor_value' => [
                'type' => 'DOUBLE',
                'null' => true,
            ],
            'is_averaged' => [
                'type' => 'smallint',
                'constraint' => 2,
                'default' => 0
            ],
            'is_valid' => [
                'type' => 'smallint',
                'null' => true,
                'constraint' => 2,
                'default' => 0
            ],
            'sub_avg_id' => [
                'type' => 'bigint',
                'null' => true,
                'default' => null
            ],
            'is_sent_cloud' => [
                'type' => 'smallint',
                'null' => true,
                'constraint' => 2,
                'default' => 0
            ],            
            'sent_cloud_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
            ],            
            'time_group' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
            ],
            'xtimestamp timestamp NOT NULL'
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('parameter_id');
        $this->forge->addUniqueKey(['parameter_id','sub_avg_id' ], 'sub_avg_id_unique');
        $this->forge->createTable('log_sent', true);
    }

    public function down()
    {
        $this->forge->dropTable('log_sent', true);
    }
}
