<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SensorReaders extends Migration
{
	public function up()
	{
		$this->forge->addField([
			'id'			=> ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
			'driver'		=> ['type' => 'VARCHAR', 'constraint' => 50],
			'sensor_code'	=> ['type' => 'VARCHAR', 'constraint' => 30],
			'baud_rate'		=> ['type' => 'VARCHAR', 'constraint' => 100],
			'pins'			=> ['type' => 'VARCHAR', 'constraint' => 200],
			'xtimestamp timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()'
		]);
		$this->forge->addKey('id', TRUE);
		$this->forge->createTable('sensor_readers', TRUE);
	}

	public function down()
	{
		$this->forge->dropTable('sensor_readers');
	}
}
