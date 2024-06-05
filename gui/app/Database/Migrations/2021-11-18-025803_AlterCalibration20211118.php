<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterCalibration20211118 extends Migration
{
	public function up()
	{
		$this->forge->dropTable('calibrations');
		$this->forge->addField([
			'id'				=> ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
			'calibrator_name'	=> ['type' => 'VARCHAR', 'constraint' => 255],
			'started_at'		=> ['type' => 'VARCHAR', 'constraint' => 20],
			'finished_at'		=> ['type' => 'VARCHAR', 'constraint' => 20],
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
	}

	public function down()
	{
		$this->forge->dropTable('calibrations');
	}
}
