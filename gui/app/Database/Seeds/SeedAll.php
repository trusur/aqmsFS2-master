<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SeedAll extends Seeder
{
	public function run()
	{
		$this->call('AUsers');
		$this->call('Configurations');
		$this->call('Parameters');
		$this->call('SensorReaders');
		$this->call('Motherboard');
		$this->call('DeviceId');
	}
}
