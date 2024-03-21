<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterMeasurement1Min extends Migration
{
    public function up()
    {
        $this->forge->addColumn('measurement_1mins', [
            'ews_sent' => ['type' => 'smallint', 'default' => 0, 'null' => true,'after' => 'is_valid'],
            'ews_sent_at' => ['type' => 'timestamp', 'default' => null, 'null' => true, 'after' => 'ews_sent'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('measurement_1mins', ['ews_sent', 'ews_sent_at']);
    }
}
