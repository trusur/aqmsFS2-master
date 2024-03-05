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
						->select("id,value,is_valid")
						->where("parameter_id = {$parameter->id} AND xtimestamp >= '{$startAt}' AND xtimestamp < '{$endAt}' and is_valid = 11")
						->findAll();
					if($minData >= 75){
						$is_valid = 11;
						$tvalue = 0;
						foreach ($valuesValid as $i => $valueValid) {
							$tvalue += $valueValid->value;
							$MmeasurementLog->set(['is_averaged' => 1, 'is_valid' => 15])->where('id', $valueValid->id)->update();
						}
						$avgvalue = round($tvalue / count($valuesValid), 2);
					}else{
						$is_valid = 19;
						
						//valid
						$tvalueValid = 0;
						if(!empty($valuesValid)){
							foreach ($valuesValid as $i => $valueV) {
								$tvalueValid += $valueV->value;
								$MmeasurementLog->set(['is_averaged' => 1, 'is_valid' => 15])->where('id', $valueV->id)->update();
							}
							$avgvalue = round($tvalueValid / count($valuesValid), 2);
						}else{
							$avgvalue = null;
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
    }
}
