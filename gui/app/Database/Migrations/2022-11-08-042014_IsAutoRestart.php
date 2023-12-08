<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class IsAutoRestart extends Migration
{
	public function up()
	{
		$data = [
			['name' => 'is_auto_restart', 'content' => '0'],
		];
		$this->db->table('configurations')->insertBatch($data);
	}

	public function down()
	{
		$this->db->table('configurations')->where(["name" => "is_auto_restart"])->delete();
	}
}
