<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Motherboard extends Migration
{
	public function up()
	{
		$this->forge->addField([
			'id'				=> ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
			'sensorname'		=> ['type' => 'VARCHAR', 'constraint' => 50],
			'is_enable'			=> ['type' => 'tinyint', 'default' => 0],
			'is_priority'		=> ['type' => 'tinyint', 'default' => 0],
			'command'			=> ['type' => 'VARCHAR', 'constraint' => 255],
			'prefix_return'		=> ['type' => 'VARCHAR', 'constraint' => 255],
			'xtimestamp timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()'
		]);
		$this->forge->addKey('id', TRUE);
		$this->forge->addKey('is_enable');
		$this->forge->addKey('is_priority');
		$this->forge->createTable('motherboard', TRUE);
		$data = [
			['sensorname' => 'MEMBRASENS PPM', 	'is_enable' => '1', 'is_priority' => '1', 'command' => 'data.membrasens.ppm', 'prefix_return' => 'END_MEMBRASENS_PPM'],
			['sensorname' => 'MEMBRASENS TEMP', 'is_enable' => '1', 'is_priority' => '0', 'command' => 'data.membrasens.temp', 'prefix_return' => 'END_MEMBRASENS_TEMP'],
			['sensorname' => 'SEMEATECH', 		'is_enable' => '1', 'is_priority' => '1', 'command' => 'data.semeatech.5', 'prefix_return' => 'SEMEATECH FINISH;'],
			['sensorname' => 'METONE 1', 		'is_enable' => '1', 'is_priority' => '1', 'command' => 'data.pm.1', 'prefix_return' => 'END_PM1'],
			['sensorname' => 'METONE 2', 		'is_enable' => '1', 'is_priority' => '1', 'command' => 'data.pm.2', 'prefix_return' => 'END_PM2'],
			['sensorname' => 'VOLTAGE CURRENT', 'is_enable' => '1', 'is_priority' => '0', 'command' => 'data.ina219', 'prefix_return' => 'END_INA219'],
			['sensorname' => 'PRESSURE BME', 	'is_enable' => '1', 'is_priority' => '0', 'command' => 'data.bme', 'prefix_return' => 'END_BME'],
			['sensorname' => 'PRESSURE', 		'is_enable' => '1', 'is_priority' => '0', 'command' => 'data.pressure', 'prefix_return' => 'END_PRESSURE'],
			['sensorname' => 'PUMP', 			'is_enable' => '1', 'is_priority' => '0', 'command' => 'data.pump', 'prefix_return' => 'END_PUMP'],
			['sensorname' => 'SENTEC', 			'is_enable' => '0', 'is_priority' => '0', 'command' => 'data.sentec', 'prefix_return' => 'END_SENTEC'],
		];
		$this->db->table('motherboard')->insertBatch($data);
	}

	public function down()
	{
		$this->forge->dropTable('motherboard');
	}
}
