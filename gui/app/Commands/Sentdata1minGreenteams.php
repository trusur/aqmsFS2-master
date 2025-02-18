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

class Sentdata1minGreenteams extends BaseCommand
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
	protected $name = 'command:sent1mingt';

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
	protected $usage = 'command:sent1mingt';

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
		$idStation = @$this->configurations->where("name", "id_stasiun")->first()->content ?? null;
		$idStation = 'DKI_CIRACAS';
		$startAt = date("Y-m-d H:i:00", strtotime("-2 minutes"));
		$endAt = date("Y-m-d H:i:00");
		$timerRange = @$this->measurements->where("time_group <= '{$endAt}' and time_group >= '{$startAt}'")->groupBy("time_group")->select("time_group")->findAll();

		$result = [];
		foreach ($timerRange as $timer) {
			$measurements = @$this->measurements->where(["time_group" => $timer->time_group, "is_sent_cloud" => 0])->orderBy("id")->findAll(500);
			$arr = [];
			$arr['id_stasiun'] = $idStation;
			$arr['waktu'] = (new DateTime($timer->time_group, new DateTimeZone('Asia/Jakarta')))
				->setTimezone(new DateTimeZone('UTC'))
				->format('Y-m-d\TH:i:s.v\Z');
			$arr['tipe_stasiun'] = 'lowcost';
			$arr['sta_lat'] = '';
			$arr['sta_lon'] = '';
			foreach ($measurements as $data) {
				$parameter = @$this->parameters->select("code,p_type")->where(["id" => $data->parameter_id])->first();
				$arr[$parameter->code] = ($data->value === null || $data->value === "") ? 0 : (int) $data->value;
			}
			
			$result[] = $arr;
		}


		if (empty($result)) {
			CLI::write("No data to sent from $startAt to $endAt", "red");
			return;
		}

		$client_url = getenv('CLIENT_API_URL');
		$client_key = getenv('CLIENT_API_KEY');
		$body = json_encode($result, JSON_UNESCAPED_SLASHES);
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $client_url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => $body,
			CURLOPT_HTTPHEADER => array(
				"CLIENT-API-KEY: " . $client_key,
				"cache-control: no-cache",
				"content-type: application/json"
			),
			CURLOPT_SSL_VERIFYPEER => 0, // Disable SSL peer verification (use with caution)
		));

		$repo = curl_exec($curl);
		$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE); // Dapatkan HTTP Status Code
		$curl_error = curl_error($curl);

		curl_close($curl);

		if ($curl_error) {
			CLI::write("cURL Error: $curl_error");
		}

		CLI::write("Sent data to Genteams, Status : $http_status,  total data: " . count($arr));
	}
}
