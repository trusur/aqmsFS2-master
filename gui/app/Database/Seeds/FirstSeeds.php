<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class FirstSeeds extends Seeder
{
	public function run()
	{
		$this->call('AGroups');
		$this->call('AMenu');
		$this->call('AUsers');
		$this->call('Configurations');
		$this->call('Parameters');
		$this->call('SensorReaders');
	}
}
