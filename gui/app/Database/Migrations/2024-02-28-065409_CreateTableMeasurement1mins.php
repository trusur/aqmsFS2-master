<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTableMeasurement1mins extends Migration
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
            'total_valid' => [
                'type' => 'INT',
                'default' => 0,
                'null' => true
            ],
            'total_data' => [
                'type' => 'INT',
                'default' => 0,
                'null' => true
            ],
            'is_averaged' => [
                'type' => 'smallint',
                'constraint' => 2,
                'default' => 0
            ],
            'is_valid' => [
                'type' => 'smallint',
                'constraint' => 2,
                'default' => 0
            ],
            'time_group' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => null
            ],
            'xtimestamp timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()'
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('parameter_id');
        $this->forge->createTable('measurement_1mins', true);
    }

    public function down()
    {
        $this->forge->dropTable('measurement_1mins', true);
    }
}
