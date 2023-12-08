<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class IsPsuRestarting extends Migration
{
	public function up()
	{
		$data = [
			['name' => 'is_psu_restarting', 'content' => '0'],
		];
		$this->db->table('configurations')->insertBatch($data);
	}

	public function down()
	{
		$this->db->table('configurations')->where(["name" => "is_psu_restarting"])->delete();
	}
}
