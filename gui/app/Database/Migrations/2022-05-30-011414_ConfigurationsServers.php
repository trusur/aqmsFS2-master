<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ConfigurationsServers extends Migration
{
	public function up()
	{
		$data = [
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
			['name' => 'iot_path', 'content' => '/iot/iot/'],
		];
		$this->db->table('configurations')->insertBatch($data);
	}

	public function down()
	{

		$this->db->table('configurations')->where(["name" => "is_sentto_klhk"])->delete();
		$this->db->table('configurations')->where(["name" => "klhk_api_server"])->delete();
		$this->db->table('configurations')->where(["name" => "klhk_api_username"])->delete();
		$this->db->table('configurations')->where(["name" => "klhk_api_password"])->delete();
		$this->db->table('configurations')->where(["name" => "klhk_api_key"])->delete();
		$this->db->table('configurations')->where(["name" => "is_sentto_trusur"])->delete();
		$this->db->table('configurations')->where(["name" => "trusur_api_server"])->delete();
		$this->db->table('configurations')->where(["name" => "trusur_api_username"])->delete();
		$this->db->table('configurations')->where(["name" => "trusur_api_password"])->delete();
		$this->db->table('configurations')->where(["name" => "trusur_api_key"])->delete();
		$this->db->table('configurations')->where(["name" => "iot_path"])->delete();
	}
}
