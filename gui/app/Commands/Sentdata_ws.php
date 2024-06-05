<?php

namespace App\Commands;

use App\Models\m_configuration;
use App\Models\m_measurement;
use App\Models\m_measurement_log;
use App\Models\m_parameter;
use App\Models\m_sensor_value;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class Sentdata_ws extends BaseCommand
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
		$this->measurements =  new m_measurement();
		$this->lastPutData = "0000-00-00 00:00";
	}
	/**
	 * The Command's Name
	 *
	 * @var string
	 */
	protected $name = 'command:sentdataws';

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
		//while (true) {
		$is_sentto_trusur = @$this->configurations->where("name", "is_sentto_trusur")->findAll()[0]->content;
		if ($is_sentto_trusur == "1") {
			$trusur_api_server = @$this->configurations->where("name", "trusur_api_server")->findAll()[0]->content;
			$measurement_ids = "";
			$time_group_dki = false;
			$arr["id_stasiun"] = @$this->configurations->where("name", "id_stasiun")->findAll()[0]->content;
			
			//START DKI
			$time_group_dki = @$this->measurement_logs->orderBy("id", "DESC")->findAll()[0]->xtimestamp;
			//print_r($time_group_dki);
			//print_r("\n");
			//print_r(date('Y-m-d H:i', strtotime($time_group_dki)));
			if ($time_group_dki) {
				$is_exist_dki = true;
				$arr["waktu"] = date('Y-m-d H:i:00');
				$measurements = @$this->measurement_logs->where(["DATE_FORMAT(xtimestamp, '%Y-%m-%d %H:%i')" => date('Y-m-d H:i', strtotime($time_group_dki))])->orderBy("id", "desc")->findAll();
				//print_r($measurements);
				//exit();
				foreach ($measurements as $measurement) {
					$parameter = @$this->parameters->where(["id" => $measurement->parameter_id, 'p_type' => 'weather'])->findAll()[0];
					if($parameter){
						$arr[$parameter->code] = $measurement->value;
						$measurement_ids .= $measurement->id . ",";
					}
				}
			}
			
			$measurement_ids = substr($measurement_ids, 0, -1);
			if ($is_exist_dki) {
				$trusur_api_username = @$this->configurations->where("name", "trusur_api_username")->findAll()[0]->content;
				$trusur_api_password = @$this->configurations->where("name", "trusur_api_password")->findAll()[0]->content;
				$trusur_api_key = '1VHJ1c3VyVW5nZ3VsVGVrbnVzYV9wVA==';
				$trusur_api_keyTrs = @$this->configurations->where("name", "trusur_api_key")->findAll()[0]->content;
				$data = json_encode($arr);
				$curl = curl_init();
				curl_setopt_array($curl, array(
					CURLOPT_URL => "http://103.135.214.229:22380/put_data_ws.php",
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
				// var_dump($data);
				// print_r($response);

				$curlTrs = curl_init();
				curl_setopt_array($curlTrs, array(
					CURLOPT_URL => "https://" . $trusur_api_server . "/api/put_data_weathers.php",
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => "",
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => 30,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => "PUT",
					CURLOPT_USERPWD => $trusur_api_username . ":" . $trusur_api_password,
					CURLOPT_POSTFIELDS => $data,
					CURLOPT_HTTPHEADER => array(
						"Api-Key: " . $trusur_api_keyTrs,
						"cache-control: no-cache",
						"content-type: application/json"
					),
					CURLOPT_SSL_VERIFYPEER => 0, //skip SSL Verification | disable SSL verify peer
				));

				$response = curl_exec($curlTrs);
				$err = curl_error($curlTrs);
				curl_close($curlTrs);
				// var_dump($data);
				// print_r($response);
				
				exit();
			}
			//END DKI
		}
			//sleep(10);
		//}
	}
}
