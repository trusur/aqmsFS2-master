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
    protected $name = 'command:avg30min';

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
    protected $usage = 'command:avg30min';

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
        $exec_start =  microtime(true);
        $Mmeasurement1Min = new \App\Models\m_measurement_1min();
        $Mmeasurement = new \App\Models\m_measurement();
        $MmeasurementLog = new \App\Models\m_measurement_log();
        $Mparameter = new \App\Models\m_parameter();

        $hour = date("H");
        $minute = date("i");
        $interval = get_config("data_interval");
        if(($minute % $interval) != 0){
            CLI::write("[Avg30Min] - The minute must be a multiple of {$interval}mins", 'yellow');
            return 0;
        }
        if(date("s") != "00"){
            CLI::write("[Avg30Min] - The second must be 00", 'yellow');
            return 0;
        }
        $startAt = date("Y-m-d H:i:00", strtotime("-{$interval} minutes"));
        $endAt = date("Y-m-d H:$minute:00");
        $parameters = $Mparameter->select("id,code,range_min,range_max,bakumutu")->where("p_type in ('gas','particulate') and is_view = 1")->findAll();
        $data = [];
        /* Get All Parameters */
		$avgid = date('ymdHis');
        foreach ($parameters as $parameter) {
           try{
				/* Get value from specific parameter */
				$data[$parameter->id] = [];
				$values = $Mmeasurement1Min
					->select("id,value,sensor_value,parameter_id,is_valid,time_group")
					->where("parameter_id = {$parameter->id} AND time_group >= '{$startAt}' AND time_group < '{$endAt}'")
					->findAll();
                $valuesValid = array_filter($values, function ($value) {
                    return $value->is_valid == 11 && $value->value > 0;
                });
				CLI::write("[$startAt - $endAt] Checking data {$parameter->code} : ".count($values), 'yellow');
				if(!empty($values)){
					$vvalue = count($valuesValid);
					if($vvalue > 0){
						$minData = ($vvalue * 100 / count($values));
					}else{
						$minData = 0;
					}

					if($minData >= 75){
						$is_valid = 11;
						$tvalue = 0;
						$tSvalue = 0;
						foreach ($valuesValid as $valueValid) {
							$tvalue += $valueValid->value;
                            $tSvalue += $valueValid->sensor_value;
							$Mmeasurement1Min->set(['is_averaged' => 1, 'is_valid' => 15])->where('id', $valueValid->id)->update();
						}
						$avgvalue = round($tvalue / count($valuesValid), 2);
                        $avgSensorValue  = round($tvalue / count($valuesValid), 5);
					}else{
						$is_valid = 19;
						//valid
						$tSvalue = 0;
						$tvalueValid = 0;
						if(!empty($valuesValid)){
							foreach ($valuesValid as $valueV) {
								$tvalueValid += $valueV->value;
                                $tSvalue += $valueV->sensor_value;
								$Mmeasurement1Min->set(['is_averaged' => 1, 'is_valid' => 15])->where('id', $valueV->id)->update();
							}
							$avgvalue = round($tvalueValid / count($valuesValid), 2);
                            $avgSensorValue  = round($tSvalue / count($valuesValid), 5);
						}else{
                            $_values = array_filter($values, function ($value) {
                                return $value->value > 0 && $value->sensor_value > 0;
                            });
							$avgvalue = round(array_sum(array_column($_values, 'value')) / count($_values),5);
							$avgSensorValue = round(array_sum(array_column($_values, 'sensor_value')) / count($_values),5);
						}
					}   
					$measurement = [
							"parameter_id" => $parameter->id,
							"value" => @$avgvalue,
                            "sensor_value" => @$avgSensorValue,
							"total_valid" => $vvalue,
							"total_data" => count($values),
							"is_valid" => $is_valid,
							"avg_id" => $avgid,
							"time_group" => $endAt,
						];
                    //check duplicate value
                    $getLastData = $Mmeasurement->where("parameter_id = '{$parameter->id}' and time_group = '{$endAt}'")->orderby('id', 'desc')->first();
					
                    if(empty($getLastData)){
                        try{
                            $Mmeasurement->insert($measurement);
                            foreach ($values as $value) {
                                $Mmeasurement1Min->set(['sub_avg_id' => $avgid])->where('id', $value->id)->update();
                            }
                        }catch(Exception $e){
                            CLI::error("Insert Error : ".$e->getMessage());
                        }
					}
				}
		   }catch(DivisionByZeroError | Exception $e){
				CLI::error($e->getMessage());
				log_message("error","AVG 30 MIN : ".$e->getMessage());
		   }
        }
        /* Meteorology */
        $meteorologies = $Mparameter->where("p_type = 'weather' and is_view = 1")->findAll();
        foreach ($meteorologies as $parameter) {
            try{
                $value = $MmeasurementLog->where("parameter_id = {$parameter->id}")->orderBy('id', 'desc')->first();
                if(!$value){
                    continue;
                }
                $measurement = [
                    "parameter_id" => $parameter->id,
                    "value" => round($value->value,3),
                    "sensor_value" => round($value->value,3),
                    "is_valid" => 1,
                    "total_data" => 1,
                    "total_valid" => 1,
                    "time_group" => date("Y-m-d $hour:$minute:00"),
                ];
                $isExist = $Mmeasurement->where("parameter_id = {$parameter->id} AND time_group = '{$measurement['time_group']}'")->first();
                if($isExist){
                    $Mmeasurement->update($isExist->id, $measurement);
                }else{
                    $Mmeasurement->insert($measurement);
                }
            }catch(Exception $e){
                CLI::error("Weather 30 Min:".$e->getMessage());
                log_message("error","Weather 30 Min:".$e->getMessage());
            }
        }
        /* Particulate Flow */
        $pmFlows = $Mparameter->where("p_type = 'particulate_flow' and is_view = 1")->findAll();
        foreach ($pmFlows as $parameter) {
            try{
                $value = $MmeasurementLog->select("avg(value) as value")
                    ->where("parameter_id = {$parameter->id} AND time_group >= '{$startAt}' AND time_group <= '{$endAt}'")
                    ->first();
                if(!$value){
                    continue;
                }
                $measurement = [
                    "parameter_id" => $parameter->id,
                    "value" => round($value->value,0),
                    "sensor_value" => round($value->value,0),
                    "is_valid" => 1,
                    "total_data" => 1,
                    "total_valid" => 1,
                    "time_group" => date("Y-m-d $hour:$minute:00"),
                ];
                $isExist = $Mmeasurement->where("parameter_id = {$parameter->id} AND time_group = '{$measurement['time_group']}'")->first();
                if($isExist){
                    $Mmeasurement->update($isExist->id, $measurement);
                }else{
                    $Mmeasurement->insert($measurement);
                }
            }catch(Exception $e){
                CLI::error("Particulate Flow 30 Min:".$e->getMessage());
                log_message("error","Particulate Flow 30 Min:".$e->getMessage());
            }
        }
        //delete data weather
        $MmeasurementLog->where('is_valid', 1)->delete();
        
        $exec_end  = microtime(true);
        $exec_time = $exec_end - $exec_start;

        CLI::write("Average 30 Min Done. Execution Time : {$exec_time}","green");
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