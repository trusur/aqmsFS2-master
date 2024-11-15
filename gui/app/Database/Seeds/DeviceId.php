<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DeviceId extends Seeder
{
    public function run()
    {
        $this->db->query("TRUNCATE TABLE device_id");
        $data = [
            [
                "parameter" => "SO2",
                "device_id" => 1,
            ],
            [
                "parameter" => "N02",
                "device_id" => 2,
            ],
            [
                "parameter" => "O3",
                "device_id" => 3,
            ],
            [
                "parameter" => "CO",
                "device_id" => 4,
            ] 
        ];
        $this->db->table('device_id')->insertBatch($data);
    }
}
