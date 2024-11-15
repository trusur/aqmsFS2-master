<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DeviceId extends Migration
{
	public function up()
	{
		$this->forge->addField([
			'id'				=> ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
			'parameter'			=> ['type' => 'VARCHAR', 'constraint' => 50],
			'device_id'			=> ['type' => 'VARCHAR', 'constraint' => 50],
			'xtimestamp timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()'
		]);
		$this->forge->addKey('id', TRUE);
		$this->forge->addUniqueKey('device_id');
		$this->forge->createTable('device_id', TRUE);
	}

	public function down()
	{
		$this->forge->dropTable('motherboard');
	}
}
