<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SensorReaders extends Seeder
{
	public function run()
	{
		$this->db->query("TRUNCATE TABLE sensor_readers");
		$data = [
			['driver' => 'labjack_reader.py', 'sensor_code' => 'ANY', 'baud_rate' => '', 'pins' => '0,1,2,3'],
			['driver' => 'hc.py', 'sensor_code' => '/dev/ttyUSB0', 'baud_rate' => '9600', 'pins' => ''],
			['driver' => 'metone_integrated.py', 'sensor_code' => '/dev/ttyUSB0', 'baud_rate' => '9600', 'pins' => ''],
			['driver' => 'metone_82840.py', 'sensor_code' => '', 'baud_rate' => '9600', 'pins' => ''],
			['driver' => 'metone_82840.py', 'sensor_code' => '', 'baud_rate' => '9600', 'pins' => ''],
			['driver' => 'vantagepro2.py', 'sensor_code' => '/dev/ttyUSB5', 'baud_rate' => '19200', 'pins' => ''],
			['driver' => 'pwmpump_controller.py', 'sensor_code' => '/dev/ttyUSB6', 'baud_rate' => '9600', 'pins' => ''],
			['driver' => 'pressure_sensor.py', 'sensor_code' => '', 'baud_rate' => '9600', 'pins' => ''],
		];
		$this->db->table('sensor_readers')->insertBatch($data);
	}
}
