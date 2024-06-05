<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AUsers extends Seeder
{
	public function run()
	{
		$this->db->query("TRUNCATE TABLE a_users");
		$data = [
			['id' => '1', 'group_id' => '0', 'email' => 'superuser@aqms', 'password' => '$argon2i$v=19$m=65536,t=4,p=1$R1FSbEMwYWZRWlJKMEwuTg$Wdl4gb5ugJWwGuFdqpjYdqLrSLRCfKAadUxA3LV1tTw', 'name' => 'Superuser'],
			['id' => '2', 'group_id' => '1', 'email' => 'admin@aqms', 'password' => '$argon2i$v=19$m=65536,t=4,p=1$R1FSbEMwYWZRWlJKMEwuTg$Wdl4gb5ugJWwGuFdqpjYdqLrSLRCfKAadUxA3LV1tTw', 'name' => 'Adminstrator'],
			['id' => '3', 'group_id' => '2', 'email' => 'operator@aqms', 'password' => '$argon2i$v=19$m=65536,t=4,p=1$R1FSbEMwYWZRWlJKMEwuTg$Wdl4gb5ugJWwGuFdqpjYdqLrSLRCfKAadUxA3LV1tTw', 'name' => 'Operator'],
		];
		$this->db->table('a_users')->insertBatch($data);
	}
}
