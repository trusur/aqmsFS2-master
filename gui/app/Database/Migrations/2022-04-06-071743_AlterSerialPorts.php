<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterSerialPorts extends Migration
{
	public function up()
	{
		$this->forge->dropTable('serial_ports');
		$this->forge->addField([
			'id'			=> ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
			'port'			=> ['type' => 'VARCHAR', 'constraint' => 20],
			'id_product'	=> ['type' => 'VARCHAR', 'constraint' => 100],
			'id_vendor'		=> ['type' => 'VARCHAR', 'constraint' => 100],
			'serial'		=> ['type' => 'VARCHAR', 'constraint' => 100],
			'description'	=> ['type' => 'TEXT'],
			'is_used'		=> ['type' => 'tinyint', 'default' => 0],
			'xtimestamp timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()'
		]);
		$this->forge->addKey('id', TRUE);
		$this->forge->addKey('port');
		$this->forge->createTable('serial_ports', TRUE);
	}

	public function down()
	{
		$this->forge->dropTable('serial_ports');
	}
}
