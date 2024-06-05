<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddColumnOnMeasurementLogs extends Migration
{
    public function up()
    {
        $this->forge->addColumn('measurement_logs', [
           'is_valid' => [
                'type' => 'tinyint',
                'default' => 1,
                'null' => true,
                'after' => 'is_averaged'
           ],
           "avg_id" => [
                "type" => "BIGINT",
                "default" => 0,
                "null" => true,
                "after" => "is_valid",
            ],
            "sub_avg_id" => [
                "type" => "BIGINT",
                "default" => 0,
                "null" => true,
                "after" => "avg_id",
            ],
           'remark' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'sub_avg_id',
           ],
           'is_sent_cloud' => [
                'type' => 'smallint',
                'constraint' => 2,
                'default' => 0
            ],
            'sent_cloud_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => null
            ],
           'time_group' => [
               'type' => 'DATETIME',
               'null' => true,
               'after' => 'remark'
           ],
           
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('measurement_logs', ['is_valid','remark','time_group','avg_id','sub_avg_id', 'is_sent_cloud','sent_cloud_at']);
    }
}
