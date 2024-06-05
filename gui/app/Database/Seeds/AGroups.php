<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AGroups extends Seeder
{
	public function run()
	{
		$this->db->query("DELETE from a_groups");
		$data = [
			['id' => '1', 'name' => 'Administrator', 'menu_ids' => '1,2,3,4,5,', 'privileges' => '15,15,15,15,15,'],
			['id' => '2', 'name' => 'Operator', 'menu_ids' => '1,4,5,', 'privileges' => '15,15,15,']
		];
		$this->db->table('a_groups')->insertBatch($data);
	}
}
