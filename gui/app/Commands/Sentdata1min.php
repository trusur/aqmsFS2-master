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
use DateTime;
use DateTimeZone;
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
		$is_sentto_trusur = @$this->configurations->where("name", "is_sentto_trusur")->first()->content ?? "1";
		$minute = date("i") > 30 ? "30" : "00";
		$endAt = date("Y-m-d H:$minute:00");
		if ($is_sentto_trusur == "1") {
			$trusur_api_server = @$this->configurations->where("name", "trusur_api_server")->first()->content ?? "";
			$lastPutData = @$this->measurements->where(["is_sent_cloud" => 0])->orderBy("id")->first()->time_group;
			if ($lastPutData) {
				$measurement_ids = [];
				$this->lastPutData = $lastPutData;
				$is_exist = true;
				$time_groups = $this->measurements->select("time_group")->where("is_sent_cloud = 0 and time_group < '{$endAt}'")->groupBy("time_group")->findAll();
				$idStation = @$this->configurations->where("name", "id_stasiun")->first()->content ?? null;
				foreach ($time_groups as $time_group) {
					$arr["id_stasiun"] = $idStation;
					$arr["waktu"] = $time_group->time_group;
					$measurements = @$this->measurements->where(["time_group" => $time_group->time_group, "is_sent_cloud" => 0])->orderBy("id")->findAll(500);
					foreach ($measurements as $measurement) {
						$parameter = @$this->parameters->select("code,p_type")->where(["id" => $measurement->parameter_id])->first();
						$arr[$parameter->code] = $measurement->value;
						if ($parameter->p_type == "particulate" || $parameter->p_type == "gas") {
							$arr["stat_{$parameter->code}"] = $measurement->is_valid;
							$arr["total_{$parameter->code}"] = $measurement->total_data;
							$arr["valid_{$parameter->code}"] = $measurement->total_valid;
						}
						$measurement_ids[] = $measurement->id;
						$arr["avg_id"] = $measurement->avg_id;
						$arr["sub_avg_id"] = $measurement->sub_avg_id;
					}

					if ($is_exist) {
						// Sent to Server
						$trusur_api_username = @$this->configurations->where("name", "trusur_api_username")->first()->content ?? "";
						$trusur_api_password = @$this->configurations->where("name", "trusur_api_password")->first()->content ?? "";
						$trusur_api_key = @$this->configurations->where("name", "trusur_api_key")->first()->content ?? "";
						$data = json_encode($arr);
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
							echo "cURL Error #:" . $err;
						} else {
							if (strpos(" " . $response, "success") > 0) {
								// START SENT DATA TO DKI
								$trusur_api_username = @$this->configurations->where("name", "trusur_api_username")->first()->content ?? "";
								$trusur_api_password = @$this->configurations->where("name", "trusur_api_password")->first()->content ?? "";
								$trusur_api_key = '1VHJ1c3VyVW5nZ3VsVGVrbnVzYV9wVA==';
								$data = json_encode($arr);
								$curl = curl_init();
								curl_setopt_array($curl, array(
									CURLOPT_URL => "http://103.135.214.229:22380/put_data_min.php",
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
									CURLOPT_SSL_VERIFYPEER => 0,
								));

								$response = curl_exec($curl);
								$err = curl_error($curl);

								curl_close($curl);
								// END SENT DATA TO DKI

								if ($err) {
									echo "cURL Error #:" . $err;
								} else {
									$arr['waktu'] = (new DateTime($arr['waktu'], new DateTimeZone('Asia/Jakarta')))
										->setTimezone(new DateTimeZone('UTC'))
										->format('Y-m-d\TH:i:s.v\Z');
									$arr["tipe_stasiun"] = "lowcost";
									$arr['sta_lat'] = "";
									$arr['sta_lon'] = "";



									// SENDING DATA TO GREENTEAMS
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
										$this->measurements->where(["time_group" => $time_group->time_group])->delete();
									} else {
										echo $response;
									}
								}
							} else {
								echo $response;
							}
						}
					}
				}
			}
		}
	}
}
