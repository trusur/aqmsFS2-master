<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use DivisionByZeroError;
use Exception;

class Average30Min extends BaseCommand
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
    protected $name = 'command:average30min';

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
    protected $usage = 'command:average30min [arguments] [options]';

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
        $Mmeasurement = new \App\Models\m_measurement();
        $Mparameter = new \App\Models\m_parameter();
        $Mconfiguration = new \App\Models\m_configuration();

        $startAt = date("Y-m-d H:i:00", strtotime("-30 minutes"));
        $endAt = date("Y-m-d H:i:00");

        $interval = $Mconfiguration->where("name", "data_interval")->first()->content ?? 30;
        $hour = date('H');
        $minute = (date('i')>$interval)? $interval :'00';


        $parameters = $Mparameter->select("id,code,range_min,range_max,bakumutu")->where("p_type in ('gas','particulate')")->findAll();
        $data = [];
        /* Get All Parameters */
        foreach ($parameters as $parameter) {
            /* Get value from specific parameter */
            $data[$parameter->id] = [];
            $invalidData[$parameter->id] = [];
            $values = $Mmeasurement1Min
                ->select("id,value")
                ->where("parameter_id = {$parameter->id} AND time_group >= '{$startAt}' AND time_group <= '{$endAt}'")
                ->findAll();
            $isFlat = false;
            $flatCount = 0;
            foreach ($values as $i => $value) {
                $data[$parameter->id]['all'][] = $value->value;
                /*1. Validate Nol atau Minus */
                if($value->value <= 0){
                    $Mmeasurement1Min->update($value->id, ['is_valid' => 12]);
                    $invalidData[$parameter->id]['id'][] = $value->id;
                    $invalidData[$parameter->id]['value'][] = $value->value;
                    $invalidData[$parameter->id]['code'][] = 12;
                    continue;
                }
                /*2. Validate with range */

                /*3. Validate with baku mutu */
                if($value->value > (2*$parameter->bakumutu)){
                    $Mmeasurement1Min->update($value->id, ['is_valid' => 13]);
                    $invalidData[$parameter->id]['id'][] = $value->id;
                    $invalidData[$parameter->id]['value'][] = $value->value;
                    $invalidData[$parameter->id]['code'][] = 13;
                }

                /*4. Validate flat data */
                if(!$isFlat && $i > 0 && $values[$i-1]->value == $values[$i]->value && $flatCount <= 10){
                    $flatCount++;
                    if($flatCount >= 10){
                        $isFlat = true;
                    }
                }
                if($isFlat){
                    $Mmeasurement1Min->update($value->id, ['is_valid' => 14]);
                    $invalidData[$parameter->id]['id'][] = $value->value;
                    $invalidData[$parameter->id]['value'][] = $value->value;
                    $invalidData[$parameter->id]['code'][] = 14;
                    continue;
                }
                /* Add value to array */
                $Mmeasurement1Min->update($value->id, ['is_valid' => 15]);
                $data[$parameter->id]['valid'][] = $value->value;
            }
            $totalData = count($values);
            $totalInvalid = count(array_map(function($arr){
                return $arr['value'] ?? [];
            }, $invalidData[$parameter->id]));
            try{
                $percentageValid = round(($totalData - $totalInvalid) / $totalData * 100, 2);
            }catch(DivisionByZeroError | Exception $e){
                $percentageValid = 0;
            }
            foreach ($data as $valueArr) {
                try{
                    if(empty($valueArr['all'])){
                        continue;
                    }
                    if($percentageValid >= 80){
                        $avg = array_sum($valueArr['valid']) / count($valueArr['valid']);
                    }else{
                        if(empty($valueArr['valid'])){
                            // Tidak ada data valid
                            $avg = array_sum($valueArr['all']) / count($valueArr['all']);
                        }else{
                            // Data Valid tidak lebih dari 80%
                            $percentageDiff = 80 - $percentageValid;
                            $sliceArray = ceil(count($invalidData[$parameter->id]['id'])*$percentageDiff/100);
                            usort($valueArr['valid'], function($a, $b){
                                return $a - $b;
                            });
                            $dataArr = $this->array_percentage($invalidData[$parameter->id]['value'], $percentageDiff);
                            $combined = $valueArr['valid'] + $dataArr;
                            $avg = array_sum($combined) / count($combined);
                            for ($i=0; $i < $sliceArray; $i++) { 
                                $id = $invalidData[$parameter->id]['id'][$i];
                                $code = $invalidData[$parameter->id]['code'][$i];
                                $Mmeasurement1Min->update($id, ['is_valid' => $code]);
                            }                            
                        }
                    }
                }catch(DivisionByZeroError | Exception $e){
                    $avg = null;
                    CLI::error("Error Average 30 min : ".$e->getMessage());
                    log_message("error","Error Average 30 min : ".$e->getMessage());
                }
                $measurement = [
                    "parameter_id" => $parameter->id,
                    "value" => $avg,
                    "sensor_value" => $avg,
                    "is_valid" => $this->isValid($parameter->code, $avg),
                    "total_data" => $totalData,
                    "total_valid" => ($totalData - $totalInvalid),
                    "time_group" => date("Y-m-d $hour:$minute:00"),
                ];
                $isExist = $Mmeasurement->where("parameter_id = {$parameter->id} AND time_group = '{$measurement['time_group']}'")->first();
                if($isExist){
                    $Mmeasurement->update($isExist->id, $measurement);
                }else{
                    $Mmeasurement->insert($measurement);
                }
            }
        }
        /*
        DATA PER DETIK DAN PER MENIT
        11 = data valid
        12 = data tidak valid karena abnormal (nol / minnus)
        13 = data tidak valid karena melebihi batas pembacaan maksimum range sensor
        14 = data tidak valid karena flat
        15 = data valid dan di rata - ratakan
        16 = data tidak valid karena abnormal (nol / minus) dan di rata - ratakan
        17 = data tidak valid karena melebihi batas pembacaan maksimum range sensor dan di rata - ratakan
        18 = data tidak valid karena flat dan di rata - ratakan
        */
    }

    public function get_percentile($percentile, $array) {
        sort($array);
        $index = ($percentile/100) * count($array);
        if (floor($index) == $index) {
             $result = ($array[$index-1] + $array[$index])/2;
        }
        else {
            $result = $array[floor($index)];
        }
        return $result;
    }

    public function array_percentage($array, $percentage) 
    {
        $count = count($array);
        $result = array_slice($array, 0, ceil($count*$percentage/100));
        return $result;
    }

    public function isValid($parameterCode, $value, $historyValues = []){
        if($value <= 0){
            return 12;
        }
        /* Check Flat Data */
        $flatCount = 0;
        $isFlat = false;
        $i=0;
        if(!$isFlat && $i > 0 && $historyValues){
            if($historyValues[$i-1] == $historyValues[$i]){
                $flatCount++;
            }
        }
        if($flatCount >= 10){
           return 14;
        }

        return 11;
    }
}
