<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddColumnOnMeasurementTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('measurements', [
            "total_data" => [
                "type" => "INT",
                "default" => 0,
                "null" => true,
                "after" => "ppm_value",
            ],
            "total_valid" => [
                "type" => "INT",
                "default" => 0,
                "null" => true,
                "after" => "total_data",
            ],
            "is_valid" => [
                "type" => "SMALLINT",
                "default" => 0,
                "null" => true,
                "after" => "total_valid",
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('measurements', ['total_data','total_valid','is_valid']);
    }
}
