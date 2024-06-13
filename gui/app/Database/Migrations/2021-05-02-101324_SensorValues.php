<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SensorValues extends Migration
{
	public function up()
	{
		$this->forge->addField([
			'id'				=> ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
			'sensor_reader_id'	=> ['type' => 'INT', 'default' => null, 'null' => true],
			'pin'				=> ['type' => 'INT', 'default' => null, 'null' => true],
			'value'				=> ['type' => 'VARCHAR', 'default' => null, 'null' => true, 'constraint' => 255],
			'xtimestamp timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()'
		]);
		$this->forge->addKey('id', TRUE);
		$this->forge->addKey('sensor_reader_id');
		$this->forge->addKey('pin');
		$this->forge->createTable('sensor_values', TRUE);
	}

	public function down()
	{
		$this->forge->dropTable('sensor_values');
	}
}
