<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddColumnOnParametersTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('parameters', [
            'range_min' => [
                'type' => 'double',
                'null' => true,
                'after' => 'concentration2',
                'comment' => 'Range Min Sensor'
            ],
            'range_max' => [
                'type' => 'double',
                'null' => true,
                'after' => 'range_min',
                'comment' => 'Range Max Sensor'
            ],
            'bakumutu' => [
                'type' => 'double',
                'null' => true,
                'after' => 'range_max',
                'comment' => 'Baku Mutu 24 Jam KLHK'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('parameters', ['range_min','range_max','bakumutu']);
    }
}
