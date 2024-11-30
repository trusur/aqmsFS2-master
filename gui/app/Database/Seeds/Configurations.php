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
			['name' => 'latitude', 'content' => null],
			['name' => 'longitude', 'content' => null],
			['name' => 'pump_interval', 'content' => '21600'],
			['name' => 'pump_state', 'content' => ''],
			['name' => 'pump_last', 'content' => ''],
			['name' => 'pump_speed', 'content' => '100'],
			['name' => 'pump_has_trigger_change', 'content' => ''],
			['name' => 'pump_switch', 'content' => ''],
			// ['name' => 'is_calibration', 'content' => '0'],
			// ['name' => 'type_calibration', 'content' => ''],
			['name' => 'data_interval', 'content' => '30'],
			['name' => 'graph_interval', 'content' => '0'],
			['name' => 'is_sampling', 'content' => '0'],
			['name' => 'sampler_operator_name', 'content' => ''],
			['name' => 'id_sampling', 'content' => ''],
			['name' => 'start_sampling', 'content' => '0'],
			['name' => 'is_sentto_klhk', 'content' => '1'],
			['name' => 'klhk_api_server', 'content' => 'ispu.menlhk.go.id'],
			['name' => 'klhk_api_username', 'content' => 'pt_trusur_unggul_teknusa'],
			['name' => 'klhk_api_password', 'content' => 'c6eXK8EUpbuCoaki'],
			['name' => 'klhk_api_key', 'content' => ''],
			['name' => 'is_sentto_trusur', 'content' => '1'],
			['name' => 'trusur_api_server', 'content' => 'api.trusur.tech'],
			['name' => 'trusur_api_username', 'content' => 'KLHK-2019'],
			['name' => 'trusur_api_password', 'content' => 'Project2016-2019'],
			['name' => 'trusur_api_key', 'content' => 'VHJ1c3VyVW5nZ3VsVGVrbnVzYV9wVA=='],
		];
		$this->db->table('configurations')->insertBatch($data);
	}
}
