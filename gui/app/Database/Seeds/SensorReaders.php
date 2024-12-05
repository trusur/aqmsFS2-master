<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SensorReaders extends Seeder
{
	public function run()
	{
		$this->db->query("TRUNCATE TABLE sensor_readers");
		$data = [
			['driver' => 'mainboard_efs2.alpha.py', 'sensor_code' => '/dev/ttyMAINBOARD', 'baud_rate' => '9600', 'pins' => null],
			['driver' => 'hc.usb.py', 'sensor_code' => '/dev/ttyUSBHC', 'baud_rate' => '9600', 'pins' => null],
		];
		$this->db->table('sensor_readers')->insertBatch($data);
	}
}
