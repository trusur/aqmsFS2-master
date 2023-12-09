<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SerialPorts extends Migration
{
	public function up()
	{
		$this->forge->addField([
			'id'			=> ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
			'port'			=> ['type' => 'VARCHAR', 'constraint' => 20],
			'description'	=> ['type' => 'VARCHAR', 'constraint' => 100],
			'is_used'		=> ['type' => 'smallint', 'default' => 0],
			'xtimestamp timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()'
		]);
		$this->forge->addKey('id', TRUE);
		$this->forge->createTable('serial_ports', TRUE);
	}

	public function down()
	{
		$this->forge->dropTable('serial_ports');
	}
}
