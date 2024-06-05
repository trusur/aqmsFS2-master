<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Datalogs extends Seeder
{
	public function run()
	{
		for ($x = 1; $x <= 5; $x++) {
			for ($i = 1; $i <= 30; $i++) {
				$data = [
					['parameter_id' => $i, 'value' => rand(1, 80), 'sensor_value' => rand(1, 200), 'is_averaged' => 0],
				];
				$this->db->table('measurement_logs')->insertBatch($data);
			}
		}

		for ($x = 1; $x <= 5; $x++) {
			for ($i = 1; $i <= 30; $i++) {
				$data = [
					['time_group' => '2021-05-06 08:00:00', 'parameter_id' => 1, 'value' => rand(1, 200), 'sensor_value' => rand(1, 200), 'is_sent_cloud' => 0, 'is_sent_klhk' => 0],
				];
				$this->db->table('measurements')->insertBatch($data);
			}
		}
	}
}
