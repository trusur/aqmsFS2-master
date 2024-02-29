<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use DivisionByZeroError;
use Exception;

class Average1Min extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'CodeIgniter';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'command:average1min';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = '';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'command:average1min';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $Mmeasurement1Min = new \App\Models\m_measurement_1min();
        $MmeasurementLog = new \App\Models\m_measurement_log();
        $Mparameter = new \App\Models\m_parameter();
        $Mconfiguration = new \App\Models\m_configuration();

        $startAt = date("Y-m-d H:i:00", strtotime("-1 minutes"));
        $endAt = date("Y-m-d H:i:00");

        $interval = $Mconfiguration->where("name", "data_interval")->first()->content ?? 30;


        $parameters = $Mparameter->select("id,name,range_min,range_max,bakumutu")->where("p_type in ('gas','particulate')")->findAll();
        $data = [];
        $invalidID = [];
        /* Get All Parameters */
        foreach ($parameters as $parameter) {
            /* Get value from specific parameter */
            $data[$parameter->id] = [];
            $values = $MmeasurementLog
                ->select("id,value")
                ->where("parameter_id = {$parameter->id} AND time_group >= '{$startAt}' AND time_group <= '{$endAt}'")
                ->findAll();
            $isFlat = false;
            $flatCount = 0;
            foreach ($values as $i => $value) {
                /*1. Validate Nol atau Minus */
                if($value->value <= 0){
                    $invalidID[] = $value->id;
                    continue;
                }
                /*2. Validate with range */

                /*3. Validate with baku mutu */
                if($value->value > (2*$parameter->bakumutu)){
                    $invalidID[] = $value->id;
                }

                /*4. Validate flat data */
                if(!$isFlat && $i > 0 && $values[$i-1]->value == $values[$i]->value && $flatCount <= 10){
                    $flatCount++;
                    if($flatCount >= 10){
                        $isFlat = true;
                    }
                }
                if($isFlat){
                    continue;
                }

                /* Add value to array */
                $data[$parameter->id][] = $value->value;
            }
            foreach ($data as $valueArr) {
                try{
                    $avgFilter = array_sum($valueArr) / count($valueArr);
                }catch(DivisionByZeroError | Exception $e){
                    $avgFilter = null;
                }
                $measurement1min = [
                    "parameter_id" => $parameter->id,
                    "value" => $avgFilter,
                    "total_valid" => count($valueArr),
                    "total_data" => count($values),
                    "is_averaged" => 0,
                    "time_group" => $endAt,
                ];
                $Mmeasurement1Min->insert($measurement1min);
            }
        }
        $MmeasurementLog->whereIn("id", $invalidID)->update(["is_valid" => 0]);
    }
}
