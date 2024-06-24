<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddColumnOnMeasruement1Min extends Migration
{
    public function up()
    {
        $this->forge->addColumn('measurement_1mins', [
            'sensor_value' => [
                'type' => 'DOUBLE',
                'null' => true,
                'after' => 'value',
                'default' => null
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('measurement_1mins', 'sensor_value');
    }
}
