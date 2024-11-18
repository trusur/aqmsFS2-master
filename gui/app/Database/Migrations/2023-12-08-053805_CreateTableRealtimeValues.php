<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTableRealtimeValues extends Migration
{
	public function up()
	{
		$this->forge->addField([
			'id' => [
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true
			],
			'parameter_id' => [
				'type' => 'INT',
				'constraint' => 11,
				'null' => true,
				'default' => null
			],
			'measured' => [
				'type' => 'DOUBLE',
				'null' => true,
				'default' => 0
			],
			'ppb_value' => [
				'type' => 'DOUBLE',
				'null' => true,
				'default' => 0
			],
			'xtimestamp timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()'
		]);
		$this->forge->addKey('id', true);
		$this->forge->createTable('realtime_values');
	}

	public function down()
	{
		$this->forge->dropTable('realtime_values');
	}
}
