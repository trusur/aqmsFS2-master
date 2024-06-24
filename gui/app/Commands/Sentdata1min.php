<?php

namespace App\Commands;

use App\Models\m_configuration;
use App\Models\m_measurement;
use App\Models\m_measurement_1min;
use App\Models\m_measurement_log;
use App\Models\m_parameter;
use App\Models\m_sensor_value;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Exception;

class Sentdata1min extends BaseCommand
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
	protected $measurement1min;
	protected $configurations;
	protected $lastPutData;

	public function __construct()
	{
		$this->parameters =  new m_parameter();
		$this->sensor_values =  new m_sensor_value();
		$this->measurement_logs =  new m_measurement_log();
		$this->configurations =  new m_configuration();
		$this->measurement1min =  new m_measurement_1min();
		$this->lastPutData = "0000-00-00 00:00";
	}
	/**
	 * The Command's Name
	 *
	 * @var string
	 */
	protected $name = 'command:sentdata1min';

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
	protected $usage = 'command:sentdata1min';

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
		$is_sentto_trusur = get_config("is_sentto_trusur",1);
		$data_interval = get_config("data_interval",30);
		$startAt = date("Y-m-d H:i:00", strtotime("-{$data_interval} minutes"));
        $endAt = date("Y-m-d H:i:00");
		if($is_sentto_trusur != 1){
			CLI::write("Pengiriman tidak diaktifkan", "yellow");
			return;
		}
		// if(date("i") % $data_interval != 0) {
		// 	CLI::write("Waktu harus di kelipatan {$data_interval} menit", "yellow");
		// 	return;
		// }
		// if(date("s") != "00"){
		// 	return;
		// }

		$idStation = get_config("id_stasiun");
		$lastSent = $this->getLastSent() ?? $startAt;
		$arr = [];
		enabled_group_by();
		$time_groups = $this->measurement1min
			->select("time_group")
			->where("is_sent_cloud = 0 and time_group >= '{$lastSent}' and time_group < '{$endAt}'")
			->groupBy("time_group")->findAll(1000);
		foreach ($time_groups as $time_group) {
			$arr["id_stasiun"] = $idStation;
			$arr["waktu"] = $time_group->time_group;
			$measurement1min = $this->measurement1min->where(["time_group" => $time_group->time_group, "is_sent_cloud" => 0])->orderBy("id")->findAll();
			foreach ($measurement1min as $measurement) {
				$parameter = $this->parameters->select("code,p_type")->where(["id" => $measurement->parameter_id])->first();
				if(empty($parameter)) continue;

				$arr[$parameter->code] = $measurement->value;
				if ($parameter->p_type == "particulate" || $parameter->p_type == "gas") {
					$arr["stat_{$parameter->code}"] = $measurement->is_valid;
					$arr["total_{$parameter->code}"] = $measurement->total_data;
					$arr["valid_{$parameter->code}"] = $measurement->total_valid;
				}
				$arr["avg_id"] = $measurement->avg_id;
				$arr["sub_avg_id"] = $measurement->sub_avg_id;
			}
			// Sent to Server
			$this->sendData($arr, $time_group->time_group);
		}
		$exec_end =  microtime(true);
		$exec_time = $exec_end - $exec_start;
		CLI::write("Waktu eksekusi pengriman data 1 mnt: " . $exec_time, "green");
	}

	public function sendData($data, $timegroup){
		try{
			$trusur_api_server = get_config("trusur_api_server");
			$trusur_api_username = get_config("trusur_api_username");
			$trusur_api_password = get_config("trusur_api_password");
			$trusur_api_key = get_config("trusur_api_key");
			$data = json_encode($data);
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => "https://" . $trusur_api_server . "/api/put_data_min.php",
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
			$err = curl_error($curl);

			curl_close($curl);

			if ($err) {
				// echo "cURL Error #:" . $err;
				CLI::write("Error: " . $err, "red");
			} 
			if (strpos(" " . $response, "success") > 0) {
				CLI::write("Success Sending 1min data", "green");
				$this->measurement1min->where(["time_group" => $timegroup])->delete();
			} else {
				print_r($response);
			}
		}catch(Exception $e){
			CLI::write("Error Sending 1min data: " . $e->getMessage(), "red");
		}
	}

	public function getLastSent(){
		try{
			return $this->measurement1min->where(["is_sent_cloud" => 0])->orderBy("id", "asc")->first()->time_group ?? null;
		}catch(Exception $e){
			return null;
		}
	}
}
