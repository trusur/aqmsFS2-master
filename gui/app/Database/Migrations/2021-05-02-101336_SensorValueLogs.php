<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SensorValueLogs extends Migration
{
	public function up()
	{
		$this->forge->addField([
			'id'				=> ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
			'sensor_value_id'	=> ['type' => 'INT', 'default' => null,'null' => true],
			'value'				=> ['type' => 'VARCHAR', 'default' => null, 'null' => true, 'constraint' => 255],
			'xtimestamp timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()'
		]);
		$this->forge->addKey('id', TRUE);
		$this->forge->addKey('sensor_value_id');
		$this->forge->createTable('sensor_value_logs', TRUE);
	}

	public function down()
	{
		$this->forge->dropTable('sensor_value_logs');
	}
}
