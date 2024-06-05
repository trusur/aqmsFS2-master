<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MeasurementHistories extends Migration
{
	public function up()
	{
		$this->forge->addField([
			'id'				=> ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
			'parameter_id'		=> ['type' => 'INT', 'default' => 0],
			'value'				=> ['type' => 'DOUBLE', 'default' => 0],
			'sensor_value'		=> ['type' => 'DOUBLE', 'default' => 0],
			'is_averaged'		=> ['type' => 'tinyint', 'default' => 0],
			'xtimestamp timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()'
		]);
		$this->forge->addKey('id', TRUE);
		$this->forge->addKey('parameter_id');
		$this->forge->addKey('is_averaged');
		$this->forge->createTable('measurement_histories', TRUE);
	}

	public function down()
	{
		$this->forge->dropTable('measurement_histories');
	}
}
