<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddColumnIsExecutedOnCalibrationsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('calibrations', [
            'is_executed' => [
                'type' => 'SMALLINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'value_after'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('calibrations', 'is_executed');
    }
}
