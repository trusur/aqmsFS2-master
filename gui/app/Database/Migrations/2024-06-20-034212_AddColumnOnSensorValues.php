<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddColumnOnSensorValues extends Migration
{
    public function up()
    {
        $this->forge->addColumn('sensor_values', [
            'updated_at' => [
                'after' => 'value',
                'type' => 'timestamp',
                'null' => true
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('sensor_values', 'updated_at');
    }
}
