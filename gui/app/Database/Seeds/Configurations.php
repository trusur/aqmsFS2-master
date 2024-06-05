<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Configurations extends Seeder
{
	public function run()
	{
		$this->db->query("TRUNCATE TABLE configurations");
		$data = [
			['name' => 'aqms_code', 'content' => 'AQMS_FS2'],
			['name' => 'id_stasiun', 'content' => 'AQMS_FS2'],
			['name' => 'nama_stasiun', 'content' => 'AQMS_FS2'],
			['name' => 'address', 'content' => 'CIBUBUR'],
			['name' => 'city', 'content' => 'JAKARTA'],
			['name' => 'province', 'content' => 'DKI JAKARTA'],
			['name' => 'latitude', 'content' => '0'],
			['name' => 'longitude', 'content' => '0'],
			['name' => 'pump_interval', 'content' => '360'],
			['name' => 'pump_state', 'content' => '1'],
			['name' => 'pump_last', 'content' => ''],
			['name' => 'pump_speed', 'content' => '80'],
			['name' => 'selenoid_state', 'content' => 'q'],
			['name' => 'selenoid_names', 'content' => ''],
			['name' => 'selenoid_commands', 'content' => 'q;w;e;r'],
			['name' => 'purge_state', 'content' => 'o'],
			['name' => 'data_interval', 'content' => '30'],
			['name' => 'graph_interval', 'content' => '0'],
			['name' => 'is_sampling', 'content' => '0'],
			['name' => 'sampler_operator_name', 'content' => ''],
			['name' => 'id_sampling', 'content' => ''],
			['name' => 'start_sampling', 'content' => '0'],
		];
		$this->db->table('configurations')->insertBatch($data);
	}
}
