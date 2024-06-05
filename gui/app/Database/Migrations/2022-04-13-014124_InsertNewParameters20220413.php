<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InsertNewParameters20220413 extends Migration
{
	public function up()
	{
		$data = [
			['code' => 'co2', 'caption_id' => 'CO<sub>2</sub>', 'caption_en' => 'CO<sub>2</sub>', 'default_unit' => 'µg/m<sup>3', 'molecular_mass' => '44.01', 'formula' => 'round((explode(";",$sensor[9][0])[0]) * 44010 / 24.45,3)', 'is_view' => '1', 'p_type' => 'gas', 'is_graph' => '1',],
			['code' => 'o2', 'caption_id' => 'O<sub>2</sub>', 'caption_en' => 'O<sub>2</sub>', 'default_unit' => 'µg/m<sup>3', 'molecular_mass' => '15.99', 'formula' => 'round((explode(";",$sensor[10][0])[0]) * 15990 / 24.45,3)', 'is_view' => '1', 'p_type' => 'gas', 'is_graph' => '1',],
			['code' => 'no', 'caption_id' => 'NO', 'caption_en' => 'NO', 'default_unit' => 'µg/m<sup>3', 'molecular_mass' => '30.0061', 'formula' => 'round((explode(";",$sensor[10][0])[0]) * 30006.1 / 24.45,3)', 'is_view' => '1', 'p_type' => 'gas', 'is_graph' => '1',],
		];
		$this->db->table('parameters')->insertBatch($data);
	}

	public function down()
	{
		$this->db->table('parameters')->where(["code" => "co2"])->delete();
		$this->db->table('parameters')->where(["code" => "o2"])->delete();
		$this->db->table('parameters')->where(["code" => "no"])->delete();
	}
}
