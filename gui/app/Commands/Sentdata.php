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
use DateTime;
use DateTimeZone;
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
		$is_sentto_trusur = @$this->configurations->where("name", "is_sentto_trusur")->first()->content ?? "1";
		if ($is_sentto_trusur == "1") {
			$trusur_api_server = @$this->configurations->where("name", "trusur_api_server")->first()->content ?? "";
			$measurement_ids = "";
			$arr["id_stasiun"] = @$this->configurations->where("name", "id_stasiun")->first()->content ?? null;

			$time_groups = @$this->measurements->select("time_group")->where(["is_sent_cloud" => 0])->groupBy("time_group")->orderBy("id")->findAll();
			foreach ($time_groups as $timeGroup) {
				$time_group = $timeGroup->time_group;
				CLI::write("Sent data {$time_group} to Trusur Server", "green");
				$is_exist = false;
				if ($time_group) {
					$is_exist = true;
					$arr["waktu"] = $time_group;
					$measurements = @$this->measurements->where(["time_group" => $time_group, "is_sent_cloud" => 0])->orderBy("id")->findAll();
					foreach ($measurements as $measurement) {
						$parameter = @$this->parameters->where(["id" => $measurement->parameter_id])->first();
						$arr[$parameter->code] = $measurement->value;
						if($measurement->avg_id){
							$arr["avg_id"] = $measurement->avg_id;
						}
						if($parameter->p_type == "particulate" || $parameter->p_type == "gas"){
							$arr["stat_{$parameter->code}"] = $measurement->is_valid;
							$arr["total_{$parameter->code}"] = $measurement->total_data;
							$arr["valid_{$parameter->code}"] = $measurement->total_valid;
							
						}
						$measurement_ids .= $measurement->id . ",";
					}
				}
				
				$measurement_ids = substr($measurement_ids, 0, -1);
				if ($is_exist) {
					$trusur_api_username = @$this->configurations->where("name", "trusur_api_username")->first()->content ?? "";
					$trusur_api_password = @$this->configurations->where("name", "trusur_api_password")->first()->content ?? "";
					$trusur_api_key = @$this->configurations->where("name", "trusur_api_key")->first()->content ?? "";
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
						$arr["tipe_stasiun"] = "lowcost";
						$arr['sta_lat'] = "";
						$arr['sta_lon'] = "";
						$arr['waktu'] = (new DateTime($arr['waktu'], new DateTimeZone('Asia/Jakarta')))
											->setTimezone(new DateTimeZone('UTC'))
											->format('Y-m-d\TH:i:s.v\Z');
						$new_arr = [$arr];
						// SENDING DATA TO GREENTEAMS
						try {
							$client_url = getenv('CLIENT_API_URL');
							$client_key = getenv('CLIENT_API_KEY');

							$data = json_encode($new_arr);
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
								CURLOPT_SSL_VERIFYPEER => 0,
							));
							curl_exec($curl);
							if (curl_errno($curl)) {
								echo 'cURL Error: ' . curl_error($curl);
							}
							curl_close($curl);
						} catch (Exception $e) {
							echo "Error: " . $e->getMessage();
						} finally {
							if (isset($curl)) {
								curl_close($curl);
							}
						}
						
						if (strpos(" " . $response, "success") > 0) {
							$this->measurements->where(["time_group" => $time_group])->set(["is_sent_cloud" => 1, "sent_cloud_at" => date("Y-m-d H:i:s")])->update();
						} else {
							echo $response;
						}
					}
				}
			}
			
		}
		// return; // Disable sending data to other server
		//START DKI
		$time_groups = @$this->measurements->select("time_group")->where(["is_sent_dki" => 0])->groupBy("time_group")->orderBy("id")->findAll();
		foreach ($time_groups as $timeGroup) {
			$is_exist_dki = false;
			$time_group_dki = $timeGroup->time_group;
			CLI::write("Sent data {$time_group_dki} to DKI Server", "green");
			if ($time_group_dki) {
				$is_exist_dki = true;
				$arr["waktu"] = $time_group_dki;
				$measurements = @$this->measurements->where(["time_group" => $time_group_dki, "is_sent_dki" => 0])->orderBy("id")->findAll();
				foreach ($measurements as $measurement) {
					$parameter = @$this->parameters->where(["id" => $measurement->parameter_id])->first();
					$arr[$parameter->code] = $measurement->value;
					$measurement_ids .= $measurement->id . ",";
				}
			}
			
			$measurement_ids = substr($measurement_ids, 0, -1);
			if ($is_exist_dki) {
				$trusur_api_username = @$this->configurations->where("name", "trusur_api_username")->findAll()[0]->content;
				$trusur_api_password = @$this->configurations->where("name", "trusur_api_password")->findAll()[0]->content;
				$trusur_api_key = '1VHJ1c3VyVW5nZ3VsVGVrbnVzYV9wVA==';
				//$trusur_api_key = @$this->configurations->where("name", "trusur_api_key")->findAll()[0]->content;
				$data = json_encode($arr);
				$curl = curl_init();
				curl_setopt_array($curl, array(
					CURLOPT_URL => "http://103.135.214.229:22380/put_data.php",
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
						$this->measurements->where(["time_group" => $time_group_dki])->set(["is_sent_dki" => 1, "sent_dki_at" => date("Y-m-d H:i:s")])->update();
						// $this->measurements->where("id IN (" . $measurement_ids . ")")->set(["is_sent_cloud" => 1, "sent_cloud_at" => date("Y-m-d H:i:s")])->update();
					} else {
						echo $response;
					}
				}
			}
		}
		// Check if all data 1sec has been sent
		$totalData1sec = $this->MLogSent->countAllResults();
		if ($totalData1sec == 0 || $totalData1sec > 2100) {
			$this->MLogSent->truncate();
		}
	}
}
