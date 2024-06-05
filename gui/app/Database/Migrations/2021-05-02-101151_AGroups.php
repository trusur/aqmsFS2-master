<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AGroups extends Migration
{
	public function up()
	{
		$this->forge->addField([
			'id'			=> ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
			'name'			=> ['type' => 'VARCHAR', 'constraint' => 50],
			'menu_ids'		=> ['type' => 'TEXT', 'default' => ''],
			'privileges'	=> ['type' => 'TEXT', 'default' => ''],
			'xtimestamp timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()'
		]);
		$this->forge->addKey('id', TRUE);
		$this->forge->createTable('a_groups', TRUE);
	}

	public function down()
	{
		$this->forge->dropTable('a_groups');
	}
}
