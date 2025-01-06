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

class testing extends BaseCommand
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
	protected $name = 'command:testing';

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
	protected $usage = 'command:testing';

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
			$lastPutData = @$this->logSent->where(["is_sent_cloud" => 0, "time_group !=" => null])->orderBy("id")->first()->time_group;
			if ($lastPutData) {
				$measurement_ids = [];
				$this->lastPutData = $lastPutData;
				$is_exist = true;
				$time_groups = $this->logSent->select("time_group")->where("is_sent_cloud = 0 and time_group >= '{$lastPutData}'")->groupBy("time_group")->findAll(1000);

				$idStation = @$this->configurations->where("name", "id_stasiun")->first()->content ?? null;
				$timeGroup = [];
				foreach ($time_groups as $key => $time_group) {
					$timeGroup[] = $time_group->time_group;
					$arr[$key]["id_stasiun"] = $idStation;
					$arr[$key]["waktu"] = $time_group->time_group;
					$arr[$key]['sta_lat'] = "";
					$arr[$key]['sta_lon'] = "";
					$measurements = @$this->logSent->where(["time_group" => $time_group->time_group, "is_sent_cloud" => 0])->orderBy("id")->findAll(500);
					foreach ($measurements as $measurement) {
						$parameter = @$this->parameters->select("code,p_type")->where(["id" => $measurement->parameter_id])->first();
						$arr[$key][$parameter->code] = $measurement->value;
						if ($measurement->sub_avg_id) {
							$arr[$key]["sub_avg_id"] = $measurement->sub_avg_id;
						}
						if ($parameter->p_type == "particulate" || $parameter->p_type == "gas") {
							$arr[$key]["stat_{$parameter->code}"] = $measurement->is_valid;
						}
						//$measurement_ids[] = $measurement->id;
					}
				} // end foreach



				try {
					$client_url = getenv('CLIENT_API_URL');
					$client_key = getenv('CLIENT_API_KEY');

					$data = json_encode($arr);
					$curl = curl_init();
					curl_setopt_array($curl, array(
						CURLOPT_URL => $client_url,
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_ENCODING => "",
						CURLOPT_MAXREDIRS => 10,
						CURLOPT_TIMEOUT => 30,
						CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
						CURLOPT_CUSTOMREQUEST => "POST",
						CURLOPT_POSTFIELDS => $data,
						CURLOPT_HTTPHEADER => array(
							"CLIENT-API-KEY: " . $client_key,
							"cache-control: no-cache",
							"content-type: application/json"
						),
						CURLOPT_SSL_VERIFYPEER => 0, // Disable SSL peer verification (use with caution)
					));
					$response = curl_exec($curl);

					if (curl_errno($curl)) {
						throw new Exception('cURL Error: ' . curl_error($curl));
					}

					curl_close($curl);

					if (strpos(" " . $response, "success") > 0) {
						echo "SUCCESS";
					} else {
						echo $response;
					}
				} catch (Exception $e) {
					echo "Error: " . $e->getMessage();
				}
			}
		}
	}
}
