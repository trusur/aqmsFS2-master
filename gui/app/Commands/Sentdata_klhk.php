<?php

namespace App\Commands;

use App\Models\m_configuration;
use App\Models\m_measurement;
use App\Models\m_measurement_log;
use App\Models\m_parameter;
use App\Models\m_sensor_value;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class Sentdata_klhk extends BaseCommand
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
	protected $configurations;
	protected $lastPutData;

	public function __construct()
	{
		$this->parameters =  new m_parameter();
		$this->sensor_values =  new m_sensor_value();
		$this->measurement_logs =  new m_measurement_log();
		$this->configurations =  new m_configuration();
		$this->measurements =  new m_measurement();
		$this->lastPutData = "0000-00-00 00:00";
	}
	/**
	 * The Command's Name
	 *
	 * @var string
	 */
	protected $name = 'command:sentdata_klhk';

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
	protected $usage = 'command:name [arguments] [options]';

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
		while (true) {
			$is_sentto_klhk = @$this->configurations->where("name", "is_sentto_klhk")->findAll()[0]->content;
			if ($is_sentto_klhk == "1") {
				$klhk_api_server = @$this->configurations->where("name", "klhk_api_server")->findAll()[0]->content;
				$measurement_ids = "";
				$is_exist = false;
				$arr["id_stasiun"] = @$this->configurations->where("name", "id_stasiun")->findAll()[0]->content;

				$time_group = @$this->measurements->where(["is_sent_klhk" => 0])->orderBy("id")->findAll()[0]->time_group;
				if ($time_group) {
					$is_exist = true;
					$arr["waktu"] = $time_group;
					$measurements = @$this->measurements->where(["time_group" => $time_group, "is_sent_klhk" => 0])->orderBy("id")->findAll();
					foreach ($measurements as $measurement) {
						$parameter = @$this->parameters->where(["id" => $measurement->parameter_id])->findAll()[0];
						$arr[$parameter->code] = $measurement->value;
						$measurement_ids .= $measurement->id . ",";
					}
				}


				// foreach ($this->parameters->where("is_view", 1)->findAll() as $parameter) {
				// 	$measurement = @$this->measurements->where(["parameter_id" => $parameter->id, "is_sent_klhk" => 0])->orderBy("id")->findAll()[0];
				// 	if ($measurement) {
				// 		$arr["waktu"] = date("Y-m-d H:i:00", strtotime($measurement->xtimestamp));
				// 		$arr[$parameter->code] = $measurement->value;
				// 		if ($measurement->value) $is_exist = true;
				// 		$measurement_ids .= $measurement->id . ",";
				// 	}
				// }
				$measurement_ids = substr($measurement_ids, 0, -1);

				if ($is_exist) {
					$klhk_api_username = @$this->configurations->where("name", "klhk_api_username")->findAll()[0]->content;
					$klhk_api_password = @$this->configurations->where("name", "klhk_api_password")->findAll()[0]->content;
					$klhk_api_key = @$this->configurations->where("name", "klhk_api_key")->findAll()[0]->content;
					$arr["stat_pm10"] = @$this->parameters->where(["code" => "pm10"])->findAll()[0]->is_view * 1;
					$arr["stat_pm25"] = @$this->parameters->where(["code" => "pm25"])->findAll()[0]->is_view * 1;
					$arr["stat_so2"] = @$this->parameters->where(["code" => "so2"])->findAll()[0]->is_view * 1;
					$arr["stat_co"] = @$this->parameters->where(["code" => "co"])->findAll()[0]->is_view * 1;
					$arr["stat_o3"] = @$this->parameters->where(["code" => "o3"])->findAll()[0]->is_view * 1;
					$arr["stat_no2"] = @$this->parameters->where(["code" => "no2"])->findAll()[0]->is_view * 1;
					$arr["stat_hc"] = @$this->parameters->where(["code" => "hc"])->findAll()[0]->is_view * 1;
					print_r($arr);

					$token = "";
					$data = json_encode(["username" => $klhk_api_username, "password" => $klhk_api_password]);
					$curl = curl_init();
					curl_setopt_array($curl, array(
						CURLOPT_URL => "https://" . $klhk_api_server . "/api/v1/auth",
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_ENCODING => "",
						CURLOPT_MAXREDIRS => 10,
						CURLOPT_TIMEOUT => 30,
						CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
						CURLOPT_CUSTOMREQUEST => "POST",
						CURLOPT_SSL_VERIFYHOST => 0,
						CURLOPT_SSL_VERIFYPEER => 0,
						CURLOPT_POSTFIELDS => $data,
						CURLOPT_HTTPHEADER => array(
							"cache-control: no-cache",
							"content-type: application/json"
						),
					));
					$response = curl_exec($curl);
					$err = curl_error($curl);

					curl_close($curl);

					if ($err) {
						echo "cURL Error #:" . $err;
					} else {
						$response = json_decode($response, true);
						$token = @$response["token"];
					}

					if ($token != "") {
						$data = json_encode($arr);
						$curl = curl_init();
						curl_setopt_array($curl, array(
							CURLOPT_URL => "https://" . $klhk_api_server . "/api/v1/aqmdata",
							CURLOPT_RETURNTRANSFER => true,
							CURLOPT_ENCODING => "",
							CURLOPT_MAXREDIRS => 10,
							CURLOPT_TIMEOUT => 30,
							CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
							CURLOPT_CUSTOMREQUEST => "POST",
							CURLOPT_SSL_VERIFYHOST => 0,
							CURLOPT_SSL_VERIFYPEER => 0,
							CURLOPT_POSTFIELDS => $data,
							CURLOPT_HTTPHEADER => array(
								"cache-control: no-cache",
								"content-type: application/json",
								sprintf('Authorization: Bearer %s', $token)
							),
						));

						$response = curl_exec($curl);
						$err = curl_error($curl);

						curl_close($curl);

						if ($err) {
							echo "cURL Error #:" . $err;
						} else {
							echo "\n" . $arr["id_stasiun"] . " => " . $response;
							if (strpos(" " . $response, "\"status\":1") > 0) {
								$this->measurements->where(["time_group" => $time_group])->set(["is_sent_klhk" => 1, "sent_klhk_at" => date("Y-m-d H:i:s")])->update();
								// $this->measurements->where("id IN (" . $measurement_ids . ")")->set(["is_sent_klhk" => 1, "sent_klhk_at" => date("Y-m-d H:i:s")])->update();
							}
						}
					}
				}
			}
			sleep(20);
		}
	}
}
