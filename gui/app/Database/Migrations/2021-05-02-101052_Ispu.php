<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Ispu extends Migration
{
	public function up()
	{
		$this->forge->addField([
			'id'				=> ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
			'ispu_at'			=> ['type' => 'DATETIME'],
			'parameter_id'		=> ['type' => 'INT', 'default' => 0],
			'value'				=> ['type' => 'DOUBLE', 'default' => 0],
			'ispu'				=> ['type' => 'int', 'default' => 0],
			'xtimestamp timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()'
		]);
		$this->forge->addKey('id', TRUE);
		$this->forge->addKey('ispu_at');
		$this->forge->addKey('parameter_id');
		$this->forge->createTable('ispu', TRUE);
	}

	public function down()
	{
		$this->forge->dropTable('ispu');
	}
}
