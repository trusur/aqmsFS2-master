<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterMeasurements extends Migration
{
	public function up()
	{
		$this->forge->dropTable('measurements');
		$this->forge->addField([
			'id'				=> ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
			'time_group'		=> ['type' => 'DATETIME'],
			'parameter_id'		=> ['type' => 'INT', 'default' => 0],
			'value'				=> ['type' => 'DOUBLE', 'default' => 0],
			'sensor_value'		=> ['type' => 'DOUBLE', 'default' => 0],
			'is_sent_cloud'		=> ['type' => 'tinyint', 'default' => 0],
			'sent_cloud_at'		=> ['type' => 'DATETIME'],
			'is_sent_klhk'		=> ['type' => 'tinyint', 'default' => 0],
			'sent_klhk_at'		=> ['type' => 'DATETIME'],
			'xtimestamp timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()'
		]);
		$this->forge->addKey('id', TRUE);
		$this->forge->addKey('time_group');
		$this->forge->addKey('parameter_id');
		$this->forge->addKey('is_sent_cloud');
		$this->forge->addKey('is_sent_klhk');
		$this->forge->createTable('measurements', TRUE);
	}

	public function down()
	{
		$this->forge->dropTable('measurements');
	}
}
