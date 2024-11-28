<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Motherboard extends Seeder
{
    public function run()
    {
        $this->db->query("TRUNCATE TABLE motherboard");
        $data = [
            [
                "sensorname" => "PM 2.5 & PM 10",
                "type"      => "read",
                // "p_type"    => "particulate",
                "is_enable" => 0,
                "is_priority" => 0,
                "command" => "getData,pm_opc,#",
                "prefix_return" => "END_PM_OPC;"
            ],
            [
                "sensorname" => "Sensor Gas Semeatech Series 7 ( Single ) ",
                "type"      => "read",
                // "p_type"    => "gas",
                "is_enable" => 0,
                "is_priority" => 0,
                "command" => "getData,semeatech,[devID],#",
                "prefix_return" => "END_SEMEATECH;"
            ],
            [
                "sensorname" => "Sensor Gas Semeatech Series 7 ( Batch ) ",
                "type"      => "read",
                // "p_type"    => "gas",
                "is_enable" => 0,
                "is_priority" => 0,
                "command" => "getData,semeatech,batch,1,4,#",
                "prefix_return" => "END_SEMEATECH_BATCH;",
                "prefix_return_batch" => "END_SEMEATECH_DATA;"
            ],
            [
                "sensorname" => "Gas HC Sensor Senovol",
                "type"      => "read",
                // "p_type"    => "gas_hc_senovol",
                "is_enable" => 0,
                "is_priority" => 0,
                "command" => "getData,senovol,[AnalogInPin],[PIDValue],[AREF],#",
                "prefix_return" => "END_SENOVOL;"
            ],
            [
                "sensorname" => "Gas HC Sensor Semeatech",
                "type"      => "read",
                // "p_type"    => "gas_hc_semeatech",
                "is_enable" => 0,
                "is_priority" => 0,
                "command" => "getData,4ECM,[devID],#",
                "prefix_return" => "END_SENOVOL;"
            ],
            [
                "sensorname" => "Sensor Weather Station RIika RK900-011",
                "type"      => "read",
                // "p_type"    => "weather",
                "is_enable" => 1,
                "is_priority" => 0,
                "command" => "getData,RK900-011,#",
                "prefix_return" => "END_RK900-011;"
            ],

           
        ];
        $this->db->table('motherboard')->insertBatch($data);
    }
}
