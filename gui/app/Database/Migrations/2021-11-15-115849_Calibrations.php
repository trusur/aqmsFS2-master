<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Calibrations extends Migration
{
	public function up()
	{
		$this->forge->addField([
			'id'				=> ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
			'calibrator_name'	=> ['type' => 'VARCHAR', 'constraint' => 255],
			'started_at'		=> ['type' => 'DATETIME'],
			'finished_at'		=> ['type' => 'DATETIME'],
			'sensor_reader_id'	=> ['type' => 'int', 'default' => 0],
			'pin'				=> ['type' => 'int', 'default' => 0],
			'value'				=> ['type' => 'TEXT'],
			'xtimestamp timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()'
		]);
		$this->forge->addKey('id', TRUE);
		$this->forge->addKey('calibrator_name');
		$this->forge->addKey('started_at');
		$this->forge->addKey('sensor_reader_id');
		$this->forge->addKey('pin');
		$this->forge->createTable('calibrations', TRUE);

		$data = [
			['name' => 'zerocal_schedule', 'content' => '00:00:00'],
			['name' => 'zerocal_duration', 'content' => '360'],
			['name' => 'is_zerocal', 'content' => '0'],
			['name' => 'calibrator_name', 'content' => ''],
			['name' => 'zerocal_started_at', 'content' => ''],
			['name' => 'zerocal_finished_at', 'content' => ''],
		];
		$this->db->table('configurations')->insertBatch($data);
	}

	public function down()
	{
		$this->forge->dropTable('calibrations');
	}
}
