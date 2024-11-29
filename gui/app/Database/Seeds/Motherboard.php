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
                "is_enable" => 0,
                "is_priority" => 0,
                "command" => "getData,PMOPC,#",
                "prefix_return" => "END_PM_OPC;"
            ],
            [
                "sensorname" => "Gas Semeatech - Single",
                "type"      => "read",
                "is_enable" => 0,
                "is_priority" => 0,
                "command" => "getData,semeatech,[devID],#",
                "prefix_return" => "END_SEMEATECH;"
            ],
            [
                "sensorname" => "Gas Semeatech - Batch",
                "type"      => "read",
                "is_enable" => 0,
                "is_priority" => 0,
                "command" => "getData,semeatech,batch,1,4,#",
                "prefix_return" => "END_SEMEATECH_BATCH;",
                "prefix_return_batch" => "END_SEMEATECH_DATA;"
            ],
            [
                "sensorname" => "HC Senovol",
                "type"      => "read",
                "is_enable" => 0,
                "is_priority" => 0,
                "command" => "getData,senovol,[AnalogInPin],[PIDValue],[AREF],#",
                "prefix_return" => "END_SENOVOL;"
            ],
            [
                "sensorname" => "HC Semeatech",
                "type"      => "read",
                "is_enable" => 0,
                "is_priority" => 0,
                "command" => "getData,4ECM,[devID],#",
                "prefix_return" => "END_SENOVOL;"
            ],
            [
                "sensorname" => "Weather Rika RK900-011",
                "type"      => "read",
                "is_enable" => 1,
                "is_priority" => 0,
                "command" => "getData,RIKA,11,#",
                "prefix_return" => "END_RIKA_WS_11;"
            ],
            [
                "sensorname" => "Read SMART PUMP",
                "type"      => "pompa",
                "is_enable" => 1,
                "is_priority" => 0,
                "command" => "getData,SmartPump,#",
                "prefix_return" => "END_SMART_PUMP;"
            ],

           
        ];
        $this->db->table('motherboard')->insertBatch($data);
    }
}
