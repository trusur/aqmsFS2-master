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
                "sensorname" => "PM OPC N3",
                "is_enable" => 1,
                "is_priority" => 0,
                "command" => "data.pm.opc#",
                "prefix_return" => "END_PM_OPC;"
            ],
            [
                "sensorname" => "PM 2.5 Metone",
                "is_enable" => 0,
                "is_priority" => 0,
                "command" => "data.pm.1#",
                "prefix_return" => "END_PM1"
            ],
            [
                "sensorname" => "PM 10 Metone",
                "is_enable" => 0,
                "is_priority" => 0,
                "command" => "data.pm.2#",
                "prefix_return" => "END_PM2"
            ],
            [
                "sensorname" => "PM Nova SDS011/SDS098",
                "is_enable" => 0,
                "is_priority" => 0,
                "command" => "data.pm.2#",
                "prefix_return" => "END_PM_NOVA"
            ],
            [
                "sensorname" => "PM Bravo / Sensirion",
                "is_enable" => 0,
                "is_priority" => 0,
                "command" => "data.particlecounter#",
                "prefix_return" => "END_PARTICLECOUNTER"
            ],
            [
                "sensorname" => "4 Gas Semeatech",
                "is_enable" => 1,
                "is_priority" => 0,
                "command" => "data.semeatech.4#",
                "prefix_return" => "SEMEATECH FINISH;"
            ],
            [
                "sensorname" => "1 Gas Senovol",
                "is_enable" => 1,
                "is_priority" => 0,
                "command" => "data.senovol#",
                "prefix_return" => "END_SENOVOL;"
            ],
            [
                "sensorname" => "Flow Winsen",
                "is_enable" => 0,
                "is_priority" => 0,
                "command" => "data.winsen.flow#",
                "prefix_return" => "END_WINSEN_FLOW"
            ],
            [
                "sensorname" => "Rika Weather Station",
                "is_enable" => 1,
                "is_priority" => 0,
                "command" => "data.rika.ws#",
                "prefix_return" => "END_WEATHER_STATION"
            ],
            [
                "sensorname" => "Sentec Solar Radiation",
                "is_enable" => 0,
                "is_priority" => 0,
                "command" => "data.sentec#",
                "prefix_return" => "END_SENTEC"
            ],
		];
		$this->db->table('motherboard')->insertBatch($data);
    }
}
