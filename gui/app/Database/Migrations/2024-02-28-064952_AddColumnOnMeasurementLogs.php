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
           'remark' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'is_valid',
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
        $this->forge->dropColumn('measurement_logs', ['is_valid','remark','time_group']);
    }
}
