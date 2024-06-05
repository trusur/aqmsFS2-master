<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterXtimestampMeasurementLogs extends Migration
{
    public function up()
    {
        //Alter xtimestamp field on table measurement_logs to set not using current_timestmap()
        $this->forge->modifyColumn('measurement_logs', [
            'xtimestamp' => [
                'type' => 'timestamp',
                'null' => true,
            ]
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('measurement_logs', [
            'xtimestamp timestamp default current_timestamp on update current_timestamp'
        ]);
    }
}
