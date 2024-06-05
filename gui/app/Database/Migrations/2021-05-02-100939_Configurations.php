<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Configurations extends Migration
{
	public function up()
	{
		$this->forge->addField([
			'id'				=> ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
			'name'				=> ['type' => 'VARCHAR', 'constraint' => 50],
			'content'			=> ['type' => 'VARCHAR', 'constraint' => 200]
		]);
		$this->forge->addKey('id', TRUE);
		$this->forge->addKey('name');
		$this->forge->createTable('configurations', TRUE);
	}

	public function down()
	{
		$this->forge->dropTable('configurations');
	}
}
