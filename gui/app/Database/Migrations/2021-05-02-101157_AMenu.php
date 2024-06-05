<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AMenu extends Migration
{
	public function up()
	{
		$this->forge->addField([
			'id'			=> ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
			'seqno'			=> ['type' => 'INT', 'default' => 0],
			'parent_id'		=> ['type' => 'INT', 'default' => 0],
			'name_id'		=> ['type' => 'VARCHAR', 'constraint' => 100, 'default' => ''],
			'name_en'		=> ['type' => 'VARCHAR', 'constraint' => 100, 'default' => ''],
			'url'			=> ['type' => 'VARCHAR', 'constraint' => 255, 'default' => ''],
			'icon'			=> ['type' => 'VARCHAR', 'constraint' => 100, 'default' => ''],
			'xtimestamp timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()'
		]);
		$this->forge->addKey('id', TRUE);
		$this->forge->addKey('parent_id');
		$this->forge->createTable('a_menu', TRUE);
	}

	public function down()
	{
		$this->forge->dropTable('a_menu');
	}
}
