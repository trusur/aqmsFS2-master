<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterMeasurements20210506 extends Migration
{
	public function up()
	{
		$this->forge->dropTable('measurements');
		$this->forge->addField([
			'id'				=> ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
			'time_group'		=> ['type' => 'DATETIME', 'default' => null,'null' => true],
			'parameter_id'		=> ['type' => 'INT', 'default' => null,'null' => true],
			'value'				=> ['type' => 'DOUBLE', 'default' => null,'null' => true],
			'sensor_value'		=> ['type' => 'DOUBLE', 'default' => null,'null' => true],
			'is_sent_cloud'		=> ['type' => 'tinyint', 'default' => 0,'null' => true],
			'sent_cloud_at'		=> ['type' => 'DATETIME', 'default' => null,'null' => true],
			'is_sent_klhk'		=> ['type' => 'tinyint', 'default' => 0,'null' => true],
			'sent_klhk_at'		=> ['type' => 'DATETIME', 'default' => null,'null' => true],
			'xtimestamp timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()'
		]);
		$this->forge->addKey('id', TRUE);
		$this->forge->addUniqueKey(['time_group', 'parameter_id']);
		$this->forge->addKey('is_sent_cloud');
		$this->forge->addKey('is_sent_klhk');
		$this->forge->createTable('measurements', TRUE);
	}

	public function down()
	{
		$this->forge->dropTable('measurements');
	}
}
