<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MeasurementLogsMigration extends Migration
{
    public function up()
    {
        // Membuat tabel measurement_logs
        $this->forge->addField([
            'id'                => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'parameter_id'      => ['type' => 'INT', 'default' => 0],
            'value'             => ['type' => 'DOUBLE', 'default' => 0],
            'sensor_value'      => ['type' => 'DOUBLE', 'default' => 0],
            'is_averaged'       => ['type' => 'tinyint', 'default' => 0],
            'xtimestamp'        => ['type' => 'timestamp', 'null' => true], // xtimestamp yang telah diubah
            'is_valid'          => ['type' => 'tinyint', 'default' => 1, 'null' => true, 'after' => 'is_averaged'],
            'avg_id'            => ['type' => 'BIGINT', 'default' => 0, 'null' => true, 'after' => 'is_valid'],
            'sub_avg_id'        => ['type' => 'BIGINT', 'default' => 0, 'null' => true, 'after' => 'avg_id'],
            'remark'            => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'sub_avg_id'],
            'is_sent_cloud'     => ['type' => 'smallint', 'constraint' => 2, 'default' => 0],
            'sent_cloud_at'     => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'time_group'        => ['type' => 'DATETIME', 'null' => true, 'after' => 'remark'],
        ]);

        // Menambahkan primary key dan index
        $this->forge->addKey('id', true);
        $this->forge->addKey('parameter_id');
        $this->forge->addKey('is_averaged');
        
        // Menambahkan UNIQUE KEY pada time_group, xtimestamp, parameter_id
        $this->forge->addUniqueKey(['time_group', 'xtimestamp', 'parameter_id'], 'time_group_unique');

        // Membuat tabel measurement_logs
        $this->forge->createTable('measurement_logs', true);
    }

    public function down()
    {
        // Menghapus tabel measurement_logs
        $this->forge->dropTable('measurement_logs');
    }
}
