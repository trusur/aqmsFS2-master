<?php

namespace App\Commands;

use App\Models\m_configuration;
use App\Models\m_measurement;
use App\Models\m_measurement_log;
use App\Models\m_parameter;
use App\Models\m_sensor_value;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

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
	protected $configurations;
	protected $lastPutData;
	protected $measurements;

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
			$is_sentto_trusur = @$this->configurations->where("name", "is_sentto_trusur")->findAll()[0]->content;
			if ($is_sentto_trusur == "1") {
				$trusur_api_server = @$this->configurations->where("name", "trusur_api_server")->findAll()[0]->content;
				$measurement_ids = "";
				$is_exist = false;
				$arr["id_stasiun"] = @$this->configurations->where("name", "id_stasiun")->findAll()[0]->content;

				$time_group = @$this->measurements->where(["is_sent_cloud" => 0])->orderBy("id")->findAll()[0]->time_group;
				if ($time_group) {
					$is_exist = true;
					$arr["waktu"] = $time_group;
					$measurements = @$this->measurements->where(["time_group" => $time_group, "is_sent_cloud" => 0])->orderBy("id")->findAll();
					foreach ($measurements as $measurement) {
						$parameter = @$this->parameters->where(["id" => $measurement->parameter_id])->findAll()[0];
						$arr[$parameter->code] = $measurement->value;
						$measurement_ids .= $measurement->id . ",";
					}
				}

				// foreach ($this->parameters->where("is_view", 1)->findAll() as $parameter) {
				// 	$measurement = @$this->measurements->where(["parameter_id" => $parameter->id, "is_sent_cloud" => 0])->orderBy("id")->findAll()[0];
				// 	if ($measurement) {
				// 		$arr["waktu"] = date("Y-m-d H:i:00", strtotime($measurement->xtimestamp));
				// 		$arr[$parameter->code] = $measurement->value;
				// 		if ($measurement->value) $is_exist = true;
				// 		$measurement_ids .= $measurement->id . ",";
				// 	}
				// }
				$measurement_ids = substr($measurement_ids, 0, -1);
				if ($is_exist) {
					$trusur_api_username = @$this->configurations->where("name", "trusur_api_username")->findAll()[0]->content;
					$trusur_api_password = @$this->configurations->where("name", "trusur_api_password")->findAll()[0]->content;
					$trusur_api_key = @$this->configurations->where("name", "trusur_api_key")->findAll()[0]->content;
					$data = json_encode($arr);
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
					} else {
						if (strpos(" " . $response, "success") > 0) {
							$this->measurements->where(["time_group" => $time_group])->set(["is_sent_cloud" => 1, "sent_cloud_at" => date("Y-m-d H:i:s")])->update();
							// $this->measurements->where("id IN (" . $measurement_ids . ")")->set(["is_sent_cloud" => 1, "sent_cloud_at" => date("Y-m-d H:i:s")])->update();
						} else {
							echo $response;
						}
					}
				}
			}
			sleep(10);
		}
	}
}
