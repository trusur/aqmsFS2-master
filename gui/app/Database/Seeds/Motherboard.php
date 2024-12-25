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
                "is_enable" => 1,
                "is_priority" => 0,
                "command" => "getData,PMOPC,#",
                "prefix_return" => "END_PM_OPC;",
                "prefix_return_batch" => null
            ],
            [
                "sensorname" => "Gas Semeatech - Single",
                "type"      => "read",
                "is_enable" => 0,
                "is_priority" => 0,
                "command" => "getData,semeatech,[devID],#",
                "prefix_return" => "END_SEMEATECH;",
                "prefix_return_batch" => null
            ],
            [
                "sensorname" => "Gas Semeatech - Batch ",
                "type"      => "read",
                // "p_type"    => "gas",
                "is_enable" => 1,
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
                "prefix_return" => "END_SENOVOL;",
                "prefix_return_batch" => null
            ],
            [
                "sensorname" => "HC Semeatech",
                "type"      => "read",
                // "p_type"    => "gas_hc_semeatech",
                "is_enable" => 0,
                "is_priority" => 0,
                "command" => "getData,4ECM,[devID],#",
                "prefix_return" => "END_SENOVOL;",
                "prefix_return_batch" => null
            ],
            [
                "sensorname" => "Weather Riika RK900-011",
                "type"      => "read",
                "is_enable" => 1,
                "is_priority" => 0,
                "command" => "getData,RIKA,11,#",
                "prefix_return" => "END_RIKA_WS_11;",
                "prefix_return_batch" => null
            ],
            [
                "sensorname" => "Read SMART PUMP",
                "type"      => "read_pump",
                "is_enable" => 0,
                "is_priority" => 0,
                "command" => "getData,SmartPump,#",
                "prefix_return" => "END_SMART_PUMP;",
                "prefix_return_batch" => null
            ],
            [
                "sensorname" => "Set Pump Speed",
                "type"      => "set_pump_speed",
                "is_enable" => 1,
                "is_priority" => 0,
                "command" => "setData,SmartPump,PWM,value#",
                "prefix_return" => "END_SMART_PUMP;",
                "prefix_return_batch" => null
            ],
            [
                "sensorname" => "Set Pump Interval",
                "type"      => "set_pump_interval",
                "is_enable" => 1,
                "is_priority" => 0,
                "command" => "setData,SmartPump,Timer,value,#",
                "prefix_return" => "END_SMART_PUMP;",
                "prefix_return_batch" => null
            ],
            [
                "sensorname" => "Switch Pump",
                "type"      => "switch_pump",
                "is_enable" => 1,
                "is_priority" => 0,
                "command" => "setData,SmartPump,Togle,#",
                "prefix_return" => "END_SMART_PUMP;",
                "prefix_return_batch" => null
            ],
            [
                "sensorname" => "Mode Pump",
                "type"      => "mode_pump",
                "is_enable" => 1,
                "is_priority" => 0,
                "command" => "setData,SmartPump,Mode,value#",
                "prefix_return" => "END_SMART_PUMP;",
                "prefix_return_batch" => null
            ],
            [
                "sensorname" => "Zero Calibration",
                "type"      => "zero",
                "is_enable" => 0,
                "is_priority" => 0,
                "command" => "perlu disesuaikan",
                "prefix_return" => "perlu disesuaikan",
                "prefix_return_batch" => null
            ],
            [
                "sensorname" => "Span Calibration",
                "type"      => "span",
                "is_enable" => 0,
                "is_priority" => 0,
                "command" => "perlu disesuaikan",
                "prefix_return" => "perlu disesuaikan",
                "prefix_return_batch" => null
            ],
            [
                "sensorname" => "Check Calibration",
                "type"      => "read_calibration",
                "is_enable" => 0,
                "is_priority" => 0,
                "command" => "perlu disesuaikan",
                "prefix_return" => "perlu disesuaikan",
                "prefix_return_batch" => null
            ],
           
        ];
        $this->db->table('motherboard')->insertBatch($data);
    }
}
