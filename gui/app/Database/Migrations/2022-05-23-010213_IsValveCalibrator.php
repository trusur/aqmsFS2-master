<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class IsValveCalibrator extends Migration
{
	public function up()
	{
		$data = [
			['name' => 'is_valve_calibrator', 'content' => '1'],
		];
		$this->db->table('configurations')->insertBatch($data);
	}

	public function down()
	{
		$this->db->table('configurations')->where(["code" => "is_valve_calibrator"])->delete();
	}
}
