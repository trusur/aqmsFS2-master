<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateRangeParameters extends Migration
{
    public function up()
	{
        $Mparameter = $this->db->table('parameters');
        $parameters = [
            [
                'code' => 'no2',
                'range_max' => 9409,
            ],
            [
                'code' => 'o3',
                'range_max' => 9816,
            ],
            [
                'code' => 'co',
                'range_max' => 57280,
            ],
            [
                'code' => 'so2',
                'range_max' => 13100,
            ],
            [
                'code' => 'hc',
                'range_max' => 64582,
            ],
            [
                'code' => 'pm25',
                'range_max' => 100000,
            ],
            [
                'code' => 'pm10',
                'range_max' => 100000,
            ],
        ];
        foreach ($parameters as $parameter) {
            $Mparameter->where(['code' => $parameter['code']])->update([
                "range_min" => 0,
                "range_max" => $parameter['range_max']
            ]);
        }
	}

	public function down()
	{
        $Mparameter = $this->db->table('parameters');
        $parameters = [
            [
                'code' => 'no2',
            ],
            [
                'code' => 'o3',
            ],
            [
                'code' => 'co',
            ],
            [
                'code' => 'so2',
            ],
            [
                'code' => 'hc',
            ],
            [
                'code' => 'pm25',
            ],
            [
                'code' => 'pm10',
            ],
        ];
        foreach ($parameters as $parameter) {
            $Mparameter->where(['code' => $parameter['code']])->update([
                "range_min" => null,
                "range_max" => null
            ]);
        }
	}
}
