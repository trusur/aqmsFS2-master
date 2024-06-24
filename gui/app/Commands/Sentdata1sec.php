<?php

namespace App\Commands;

use App\Models\m_configuration;
use App\Models\m_measurement;
use App\Models\m_measurement_1min;
use App\Models\m_measurement_log;
use App\Models\m_log_sent;
use App\Models\m_copy_measurement_log;
use App\Models\m_parameter;
use App\Models\m_sensor_value;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Exception;

class Sentdata1sec extends BaseCommand
{
	/**
	 * The Command's Group
	 *
	 * @var string
	 */
	protected $group = 'CodeIgniter';
	protected $parameters;
	protected $sensor_values;
	protected $measurement_logs;
	protected $measurements;
	protected $configurations;
	protected $lastPutData;
	protected $logSent;

	public function __construct()
	{
		$this->parameters =  new m_parameter();
		$this->sensor_values =  new m_sensor_value();
		$this->measurement_logs =  new m_measurement_log();
		$this->configurations =  new m_configuration();
		$this->measurements =  new m_measurement_log();
		$this->logSent =  new m_log_sent();
		$this->lastPutData = "0000-00-00 00:00";
	}
	/**
	 * The Command's Name
	 *
	 * @var string
	 */
	protected $name = 'command:sentdata1sec';

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
	protected $usage = 'command:sentdata1sec';

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
		$is_sentto_trusur = (int) get_config("is_sentto_trusur", 1);
		if($is_sentto_trusur == 0) return;
		// if(date("s") != "00") return;

		$trusur_api_server = get_config("trusur_api_server");
		$lastSent = $this->getLastSent();
		if ($lastSent) {
			$is_exist = false;
			enabled_group_by();
			$time_groups = $this->logSent->select("time_group")->where("is_sent_cloud = 0 and time_group >= '{$lastSent}'")->groupBy("time_group")->findAll(1000);
			$idStation = get_config("id_stasiun");
			$timeGroup = [];
			$arr = [];
			foreach ($time_groups as $key => $time_group) {
				$timeGroup[] = $time_group->time_group;
				$arr[$key]["id_stasiun"] = $idStation;
				$arr[$key]["waktu"] = $time_group->time_group;
				$measurements = $this->logSent->where(["time_group" => $time_group->time_group, "is_sent_cloud" => 0])->orderBy("id")->findAll(500);
				foreach ($measurements as $measurement) {
					$parameter = $this->parameters->select("code,p_type")->where(["id" => $measurement->parameter_id])->first();
					if(empty($parameter)) continue;

					$arr[$key][$parameter->code] = $measurement->value;
					if($measurement->sub_avg_id){
						$arr[$key]["sub_avg_id"] = $measurement->sub_avg_id;
					}
					if ($parameter->p_type == "particulate" || $parameter->p_type == "gas") {
						$arr[$key]["stat_{$parameter->code}"] = $measurement->is_valid;
					}
				}
			}

			$is_exist = count($arr) > 0; 

			if ($is_exist) {
				// Sent to Server
				$trusur_api_username = get_config("trusur_api_username");
				$trusur_api_password = get_config("trusur_api_password");
				$trusur_api_key = get_config("trusur_api_key");
				$data = json_encode($arr);
				$curl = curl_init();
				curl_setopt_array($curl, array(
					CURLOPT_URL => "https://" . $trusur_api_server . "/api/put_data_sec.php",
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => "",
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => 30,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => "PUT",
					CURLOPT_USERPWD => $trusur_api_username . ":" . $trusur_api_password,
					CURLOPT_POSTFIELDS => $data,
					CURLOPT_HTTPHEADER => array(
						"Api-Key: " . $trusur_api_key,
						"cache-control: no-cache",
						"content-type: application/json"
					),
					CURLOPT_SSL_VERIFYPEER => 0, //skip SSL Verification | disable SSL verify peer
				));

				$response = curl_exec($curl);
				$responseArr = json_decode($response, true);
				$err = curl_error($curl);
				
				CLI::write($response, "green");

				curl_close($curl);

				if ($err) {
					CLI::write("cURL Error #:" . $err,"red");
				} 
				if ($responseArr["success"]) {
					CLI::write("Success", "green");
					$this->logSent->whereIn("time_group", $timeGroup)->delete();
				} else {
					print_r($response);
				}
			}
		}
	}

	public function getLastSent(){
		$twoMinAgo = date("Y-m-d H:i:00", strtotime("-2 minutes"));
		try{
			$logSent = $this->logSent->where("is_sent_cloud = 0")->first();
			if(empty($logSent)){
				return $twoMinAgo;
			}
			return $logSent->time_group;
		}catch(Exception $e){
			return $twoMinAgo;
		}
	}
}
