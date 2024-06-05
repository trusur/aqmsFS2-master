<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterValueOnMeasurementsTable extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('measurements', [
            'value' => [
                'type' => 'DOUBLE',
                'null' => true
            ]
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('measurements', [
            'value' => [
                'type' => 'DOUBLE',
                'default' => 0
            ]
        ]);
    }
}
