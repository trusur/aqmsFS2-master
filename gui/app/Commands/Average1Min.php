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
    protected $name = 'command:avg1min';

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
    protected $usage = 'command:avg1min';

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
        $logSent = new \App\Models\m_log_sent();
		

        $startAt = date("Y-m-d H:i:00", strtotime("-1 minutes"));
        $endAt = date("Y-m-d H:i:00");
	

        $parameters = $Mparameter->select("id,code,range_min,range_max,bakumutu")->where("p_type in ('gas','particulate') and is_view = 1")->findAll();
        $data = [];
        /* Get All Parameters */
		$avgid = date('ymdHis');
        foreach ($parameters as $parameter) {
           try{
				/* Get value from specific parameter */
				$data[$parameter->id] = [];
				$values = $MmeasurementLog
					->select("id,value,is_valid")
					->where("parameter_id = {$parameter->id} AND xtimestamp >= '{$startAt}' AND xtimestamp < '{$endAt}'")
					->findAll();
				CLI::write("[$startAt - $endAt] Checking data {$parameter->code} : ".count($values), 'yellow');
				if(!empty($values)){
					$vvalue = 0;
					foreach ($values as $i => $value) {
						if($value->is_valid == 11){
							$vvalue += 1;
						}
					}
					if($vvalue > 0){
						$minData = ($vvalue * 100 / count($values));
					}else{
						$minData = 0;
					}
					$valuesValid = $MmeasurementLog
						->where("parameter_id = {$parameter->id} AND xtimestamp >= '{$startAt}' AND xtimestamp < '{$endAt}' and is_valid = 11")
						->findAll();
					if($minData >= 75){
						$is_valid = 11;
						$tvalue = 0;
						foreach ($valuesValid as $i => $valueValid) {
							$tvalue += $valueValid->value;
							//$MmeasurementLog->set(['is_averaged' => 1, 'is_valid' => 15])->where('id', $valueValid->id)->update();
							//insert into log sent
							$logSent->insert(
							[
								'parameter_id' => $valueValid->parameter_id,
								'value' => $valueValid->value,
								'sensor_value' => $valueValid->sensor_value,
								'is_averaged' => 1,
								'is_valid' => 15,
								'sub_avg_id' => $avgid,
								'time_group' => $valueValid->time_group,
								'xtimestamp' => date('Y-m-d H:i:s'),
							]
							);
								
							//delete
							$MmeasurementLog->where('id', $valueValid->id)->delete();
						}
						$avgvalue = round($tvalue / count($valuesValid), 2);
					}else{
						$is_valid = 19;
						
						//valid
						$tvalueValid = 0;
						if(!empty($valuesValid)){
							foreach ($valuesValid as $i => $valueV) {
								$tvalueValid += $valueV->value;
								//$MmeasurementLog->set(['is_averaged' => 1, 'is_valid' => 15])->where('id', $valueV->id)->update();
								//insert into log sent
								$logSent->insert(
								[
									'parameter_id' => $valueV->parameter_id,
									'value' => $valueV->value,
									'sensor_value' => $valueV->sensor_value,
									'is_averaged' => 1,
									'is_valid' => 15,
									'sub_avg_id' => $avgid,
									'time_group' => $valueV->time_group,
									'xtimestamp' => date('Y-m-d H:i:s'),
								]
								);
								
								//delete
								$MmeasurementLog->where('id', $valueV->id)->delete();
							}
							$avgvalue = round($tvalueValid / count($valuesValid), 2);
						}else{
							$avgvalue = null;
						}
					}
						
					$valuesNotValid = $MmeasurementLog
					->where("parameter_id = {$parameter->id} AND xtimestamp >= '{$startAt}' AND xtimestamp < '{$endAt}' and is_valid != 11")
					->findAll();
					if(!empty($valuesNotValid)){
						foreach ($valuesNotValid as $i => $valueNV) {
							//insert into log sent
							$logSent->insert(
							[
								'parameter_id' => $valueNV->parameter_id,
								'value' => $valueNV->value,
								'sensor_value' => $valueNV->sensor_value,
								'is_valid' => $valueNV->is_valid,
								'sub_avg_id' => $avgid,
								'time_group' => $valueNV->time_group,
								'xtimestamp' => date('Y-m-d H:i:s'),
							]
							);
							
							//delete
							$MmeasurementLog->where('id', $valueNV->id)->delete();
						}
					}
					$measurement1min = [
							"parameter_id" => $parameter->id,
							"value" => @$avgvalue,
							"total_valid" => $vvalue,
							"total_data" => count($values),
							"is_averaged" => 0,
							"is_valid" => $is_valid,
							"avg_id" => $avgid,
							"time_group" => $endAt,
						];
					//check duplicate value
					$getLastData = $Mmeasurement1Min->where("parameter_id = '{$parameter->id}' and time_group = '{$endAt}'")->orderby('id', 'desc')->first();
					if(empty($getLastData)){
						$Mmeasurement1Min->insert($measurement1min);
						foreach ($values as $value) {
							$MmeasurementLog->set(['sub_avg_id' => $avgid])->where('id', $value->id)->update();
						}
					}
				}
		   }catch(Exception $e){
				CLI::error($e->getMessage());
				log_message("error","AVG 1 MIN : ".$e->getMessage());
		   }
        }
		$parameterFlow = $Mparameter->select("id,code")->where("p_type = 'particulate_flow' and is_view = 1")->findAll();
		foreach ($parameterFlow as $parameter) {
			try{
				$values = $MmeasurementLog
				->select("id,value,parameter_id")
				->where("parameter_id = {$parameter->id} AND xtimestamp >= '{$startAt}' AND xtimestamp < '{$endAt}'")
				->findAll();
				foreach ($values as $value) {
					$logSent->insert([
						'parameter_id' => $value->parameter_id,
						'value' => $value->value,
						'sensor_value' => $value->sensor_value,
						'is_averaged' => 1,
						'is_valid' => 1,
						'sub_avg_id' => $avgid,
						'time_group' => $value->time_group,
						'xtimestamp' => date('Y-m-d H:i:s'),
					]);
					//delete
					$MmeasurementLog->where('id', $value->id)->delete();
				}
			}catch(Exception $e){
				CLI::error($e->getMessage());
				log_message("error","AVG 1 MIN : ".$e->getMessage());
			}
		}
    }
}
