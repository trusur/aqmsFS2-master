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

class Sentdataews extends BaseCommand
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

	public function __construct()
	{
		$this->parameters =  new m_parameter();
		$this->sensor_values =  new m_sensor_value();
		$this->measurement_logs =  new m_measurement_log();
		$this->configurations =  new m_configuration();
		$this->measurements =  new m_measurement_1min();
		$this->lastPutData = "0000-00-00 00:00";
	}
	/**
	 * The Command's Name
	 *
	 * @var string
	 */
	protected $name = 'command:sentdataews';

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
	protected $usage = 'command:sentdataews';

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
		$is_sentto_trusur = @$this->configurations->where("name", "is_sentto_trusur")->first()->content ?? "1";
		if ($is_sentto_trusur == "1") {
			$trusur_api_server = @$this->configurations->where("name", "trusur_api_server")->first()->content ?? "";
			$getDataSatuMnt = @$this->measurements
							->select('parameters.code as p_code, measurement_1mins.*')
							->join('parameters', 'parameters.id = measurement_1mins.parameter_id')
							->where("measurement_1mins.ews_sent = 0 AND measurement_1mins.is_valid != 11 AND measurement_1mins.is_valid != 15")->orderBy("id")->findAll();
			$idStation = @$this->configurations->where("name", "id_stasiun")->first()->content ?? null;
			if (!empty($getDataSatuMnt)) {
				foreach ($getDataSatuMnt as $getData) {
					$arr["avg_id"] = $getData->avg_id;
					$arr["id_stasiun"] = $idStation;
					$arr["waktu"] = $getData->time_group;
					$arr["parameter"] = $getData->p_code;
					$arr["value"] = $getData->value;
					$arr["status_parameter"] = $getData->is_valid;
					$arr["total_data"] = $getData->total_data;
					$arr["data_valid"] = $getData->total_valid;
					$arr["is_notification"] = 0;

					// Sent to Server
					$trusur_api_username = @$this->configurations->where("name", "trusur_api_username")->first()->content ?? "";
					$trusur_api_password = @$this->configurations->where("name", "trusur_api_password")->first()->content ?? "";
					$trusur_api_key = @$this->configurations->where("name", "trusur_api_key")->first()->content ?? "";
					$data = json_encode($arr);
					$curl = curl_init();
					curl_setopt_array($curl, array(
						CURLOPT_URL => "https://" . $trusur_api_server . "/api/put_data_ews.php",
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
							$this->measurements->where(["id" => $getData->id])->set(["ews_sent" => 1, "ews_sent_at" => date("Y-m-d H:i:s")])->update();
						} else {
							echo $response;
						}
					}

				}
				exit();
			}
		}
	}
}
