<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AUsers extends Migration
{
	public function up()
	{
		$this->forge->addField([
			'id'		=> ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
			'group_id'	=> ['type' => 'INT', 'default' => 1, 'null' => true],
			'email'		=> ['type' => 'VARCHAR', 'constraint' => 100, 'default' => 0],
			'password'	=> ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'default' => null],
			'name'		=> ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'default' => null],
			'xtimestamp timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()'
		]);
		$this->forge->addKey('id', TRUE);
		$this->forge->addKey('group_id');
		$this->forge->createTable('a_users', TRUE);
	}

	public function down()
	{
		$this->forge->dropTable('a_users');
	}
}
