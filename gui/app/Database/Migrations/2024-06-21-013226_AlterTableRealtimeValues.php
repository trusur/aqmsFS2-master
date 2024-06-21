<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterTableRealtimeValues extends Migration
{
    public function up()
    {
        $this->forge->addColumn('realtime_values', [
            'raw' => [
                'type' => 'double',
                'null' => true,
                'after' => 'measured'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('realtime_values', 'raw');
    }
}
