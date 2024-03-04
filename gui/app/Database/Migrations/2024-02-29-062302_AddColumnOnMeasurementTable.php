<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddColumnOnMeasurementTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('measurements', [
            "avg_id" => [
                "type" => "BIGINT",
                "default" => 0,
                "null" => true,
                "after" => "sensor_value",
            ],
            "total_data" => [
                "type" => "INT",
                "default" => 0,
                "null" => true,
                "after" => "avg_id",
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
        $this->forge->dropColumn('measurements', ['avg_id', 'total_data','total_valid','is_valid']);
    }
}
