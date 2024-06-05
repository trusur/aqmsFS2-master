<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AMenu extends Seeder
{
	public function run()
	{
		$this->db->query("TRUNCATE TABLE a_menu");
		$data = [
			['id' => '1', 'seqno' => '1', 'parent_id' => '0', 'name_id' => 'Beranda', 'name_en' => 'Home', 'url' => '/'],
			['id' => '2', 'seqno' => '2', 'parent_id' => '0', 'name_id' => 'Konfigurasi', 'name_en' => 'Configuration', 'url' => 'configuration'],
			['id' => '3', 'seqno' => '3', 'parent_id' => '0', 'name_id' => 'Parameter', 'name_en' => 'Parameters', 'url' => 'parameter'],
			['id' => '4', 'seqno' => '4', 'parent_id' => '0', 'name_id' => 'Kalibrasi', 'name_en' => 'Calibrations', 'url' => 'calibration'],
			['id' => '5', 'seqno' => '5', 'parent_id' => '0', 'name_id' => 'Ekspor', 'name_en' => 'Export', 'url' => 'export'],
		];
		$this->db->table('a_menu')->insertBatch($data);
	}
}
