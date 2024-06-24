<?php

namespace App\Commands;

use App\Models\m_configuration;
use App\Models\m_log_sent;
use App\Models\m_measurement;
use App\Models\m_measurement_1min;
use App\Models\m_measurement_log;
use App\Models\m_parameter;
use App\Models\m_sensor_value;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Exception;

class Sentdata extends BaseCommand
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
	protected $MLogSent;

	public function __construct()
	{
		$this->parameters =  new m_parameter();
		$this->sensor_values =  new m_sensor_value();
		$this->measurement_logs =  new m_measurement_log();
		$this->configurations =  new m_configuration();
		$this->measurements =  new m_measurement();
		$this->lastPutData = "0000-00-00 00:00";
		$this->MLogSent = new m_log_sent();
	}
	/**
	 * The Command's Name
	 *
	 * @var string
	 */
	protected $name = 'command:sentdata';

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
	protected $usage = 'command:sentdata';

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
		if(date("s") != "00") return;
		$is_sentto_trusur = get_config("is_sentto_trusur");
		$data_interval = get_config("data_interval", 30);
		if($is_sentto_trusur != 1){
			CLI::write("Pengiriman tidak diaktifkan", "yellow");
			return;
		}
		// if(date("i") % $data_interval != 0){
		// 	CLI::write("Pengiriman dilakukan setiap {$data_interval} menit", "yellow");
		// 	return;
		// }
		$arr["id_stasiun"] = get_config("id_stasiun");
		enabled_group_by();
		$time_groups = $this->measurements->select("time_group")->where(["is_sent_cloud" => 0])->groupBy("time_group")->orderBy("id")->findAll(1000);
		foreach ($time_groups as $timeGroup) {
			$time_group = $timeGroup->time_group;
			CLI::write("Sent data {$time_group} to Trusur Server", "green");
			$arr["waktu"] = $time_group;
			$measurements = @$this->measurements->where(["time_group" => $time_group, "is_sent_cloud" => 0])->orderBy("id")->findAll();
			foreach ($measurements as $measurement) {
				$parameter = @$this->parameters->where(["id" => $measurement->parameter_id])->first();
				if(empty($parameter)) continue;

				$arr[$parameter->code] = $measurement->value;
				if($measurement->avg_id){
					$arr["avg_id"] = $measurement->avg_id;
				}
				if($parameter->p_type == "particulate" || $parameter->p_type == "gas"){
					$arr["stat_{$parameter->code}"] = $measurement->is_valid;
					$arr["total_{$parameter->code}"] = $measurement->total_data;
					$arr["valid_{$parameter->code}"] = $measurement->total_valid;
				}
				$this->sendData($arr, $time_group);
			}
		}
		// Check if all data 1sec has been sent
		$totalData1sec = $this->MLogSent->countAll();
		if ($totalData1sec == 0 || $totalData1sec > 2100) {
			$this->MLogSent->truncate();
		}
	}

	public function sendData($data, $time_group){
		try{
			$trusur_api_server = get_config("trusur_api_server");
			$trusur_api_username = get_config("trusur_api_username");
			$trusur_api_password = get_config("trusur_api_password");
			$trusur_api_key = get_config("trusur_api_key");
			$data = json_encode($data);
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => "https://" . $trusur_api_server . "/api/put_data.php",
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
				echo "cURL Error #:" . $err;
				CLI::write("[Error] - $err", "red");
				return false;
			} else {
				if (strpos(" " . $response, "success") > 0) {
					$this->measurements->where(["time_group" => $time_group])->set(["is_sent_cloud" => 1, "sent_cloud_at" => date("Y-m-d H:i:s")])->update();
					CLI::write("[Success]  -Sent data {$time_group} to Trusur Server", "green");
					return true;
				} else {
					CLI::write("[Error] - Sending data {$time_group} to Trusur Server", "red");
					print_r($response);
					return false;
				}
			}
		}catch(Exception $e){
			return false;
		}
	}
}
