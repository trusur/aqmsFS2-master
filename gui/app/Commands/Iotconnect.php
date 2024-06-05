<?php

namespace App\Commands;

use App\Database\Migrations\Measurements;
use App\Models\m_configuration;
use App\Models\m_measurement;
use App\Models\m_measurement_log;
use App\Models\m_parameter;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class Iotconnect extends BaseCommand
{
	/**
	 * The Command's Group
	 *
	 * @var string
	 */
	protected $group = 'CodeIgniter';

	/**
	 * The Command's Name
	 *
	 * @var string
	 */
	protected $name = 'command:iot_connect';

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
	protected $usage = 'command:iot_connect [arguments] [options]';

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
		try {
			// $baseIotServer = "http://localhost:8000/iot/";
			$baseIotServer = "http://api.trusur.tech/iot/";
			$client = \Config\Services::curlrequest([
				'timeout' => 3
			]);
			$MeasurementLogs = new m_measurement_log();
			$MConfig = new m_configuration();
			// $data = $MeasurementLogs
			// 		->select('measurement_logs.value, parameters.caption_id')
			// 		->join('parameters','parameters.id = measurement_logs.parameter_id')
			// 		->where('parameters.p_type = "particulate" AND parameters.is_view = "1"')
			// 		->where('measurement_logs.id < 30')
			// 		->findAll();
			$stationId = $MConfig->where('name', 'id_stasiun')->first()->content;
			while (true) {
				$url = $baseIotServer . "device-connect/?station_id={$stationId}";
				// Check command
				$requestToServer = $client->get($url, []);
				$json = json_decode($requestToServer->getJSON());
				$response = json_decode($json, true);
				// Check if command exists
				$content = [];
				if ($response['hasCommand']) {
					$commands = $response['commands'];
					foreach ($commands as $key => $command) {
						$commandId = $command['id'];
						$code = $command['code']['code'];
						$url .= "&content[$commandId]=" . urlencode($this->executeCommand($code, $command['content']));
					}
				}
				$problems = $this->getMeasurements();
				$dateI = (int) date('i');
				if (count($problems) > 0 && ($dateI == 0 || $dateI == 30)) { //Sent problem every 30 min
					foreach ($problems as $key => $problem) {
						$url .= "code[{$key}]={$problem['code']}&";
					}
					// Send Device Status Problem
					$requestToServer = $client->get($url, []);
					$response = $requestToServer->getJSON();
				} else {
					$url .= "&code[0]=200";
				}
				$requestToServer = $client->get($url, []);
				$json = json_decode($requestToServer->getJSON());
				sleep(30); // Sleep 30sec
			}
		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

	public function getMeasurements()
	{
		$Measurement = new m_measurement();
		$problems = [];
		$dateStart = date('Y-m-d H:i:s', strtotime('-30 min'));
		$dateEnd = date('Y-m-d H:i:s');
		$whereRaw = "time_group >= '{$dateStart}' AND time_group <= '{$dateEnd}'";
		$measurements = $Measurement->select('parameters.p_type, parameters.code, measurements.value')
			->where($whereRaw)
			->findAll();
		foreach ($measurements as $measurement) {
			$isAbnormal = $this->isAbnormal($measurement);
			if ($isAbnormal['abnormal']) {
				$problems[] = $isAbnormal['code'];
			}
		}
		return $problems;
	}

	public function isAbnormal($measurement)
	{
		$bakuMutu = $this->getBakuMutu($measurement->code);
		if ($measurement->value > $bakuMutu) {
			$data = [
				'abnormal' => true,
				'code' => $this->getCodeZeroValue($measurement->code) . ".1",
			];
		} elseif ($measurement->value <= 0) {
			$data = [
				'abnormal' => true,
				'code' => $this->getCodeZeroValue($measurement->code),
			];
		} else {
			$data['abnormal'] = false;
			$data['code'] = '200';
		}
		return $data;
	}

	/**
	 * Hardcode baku mutu 
	 *
	 * @param [type] $code
	 * @return void
	 */
	public function getBakuMutu($code)
	{
		switch ($code) {
			case 'pm10':
				$bakuMutu = 150;
				break;
			case 'pm25':
				$bakuMutu = 65;
				break;
			case 'no2':
				$bakuMutu = 400;
				break;
			case 'so2':
				$bakuMutu = 900;
				break;
			case 'co':
				$bakuMutu = 30000;
				break;
			case 'o3':
				$bakuMutu = 235;
				break;
			case 'hc':
				$bakuMutu = 160;
				break;

			default:
				$bakuMutu = 0;
				break;
		}
		return $bakuMutu;
	}

	public function getCodeZeroValue($code)
	{
		switch ($code) {
			case 'pm10':
				$code = "422";
				break;
			case 'pm25':
				$code = "421";
				break;
			case 'no2':
				$code = "431";
				break;
			case 'so2':
				$code = "434";
				break;
			case 'co':
				$code = "433";
				break;
			case 'o3':
				$code = "432";
				break;
			case 'hc':
				$code = "435";
				break;
			case 'pressure':
				$code = "440";
				break;

			default:
				$code = "200";
				break;
		}
		return $code;
	}

	/**
	 * execute controlling & request command from server
	 *
	 * @param [string] $code
	 * @param [string] $content
	 * @return [int] $idCommand
	 */
	public function executeCommand($code, $content)
	{
		$Configuration = new m_configuration();
		$data = "";
		switch ($code) {
			case '10': // Request Active PUMP
				$pumpState = @$Configuration->where('name', 'pump_state')->content;
				$data = @$pumpState ? $pumpState : 'Cant detect pump state';
				break;
			case '20': // Request PM 2.5 & PM 10 Value
				break;
			case '30': // Request Gass Concetrate
				break;
			case '40': // Request Meteorologi
				$data = "Test data meteorologi";
				break;
			case '331': // Request Formula NO2
				$data = $this->getParameterFormula('no2');
				break;
			case '332': // Request Formula O3
				$data = $this->getParameterFormula('o3');
				break;
			case '333': // Request Formula CO
				$data = $this->getParameterFormula('co');
				break;
			case '334': // Request Formula SO2
				$data = $this->getParameterFormula('so2');
				break;
			case '335': // Request Formula HC
				$data = $this->getParameterFormula('hc');
				break;
			case '331.1': // Update Formula NO2
				$isUpdated  = $this->updateParameterFormula('no2', $content);
				$data = $isUpdated ? 'Update formula for NO2 successfully!' : 'Cant update formula!';
				break;
			case '332.1': // Update Formula O3
				$isUpdated  = $this->updateParameterFormula('o3', $content);
				$data = $isUpdated ? 'Update formula for O3 successfully!' : 'Cant update formula!';
				break;
			case '333.1': // Update Formula CO
				$isUpdated  = $this->updateParameterFormula('co', $content);
				$data = $isUpdated ? 'Update formula for CO successfully!' : 'Cant update formula!';
				break;
			case '334.1': // Update Formula SO2
				$isUpdated  = $this->updateParameterFormula('so2', $content);
				$data = $isUpdated ? 'Update formula for SO2 successfully!' : 'Cant update formula!';
				break;
			case '335.1': // Update Formula HC
				$isUpdated  = $this->updateParameterFormula('hc', $content);
				$data = $isUpdated ? 'Update formula for HC successfully!' : 'Cant update formula!';
				break;
			case '11': // Update to PUMP 1
				$isUpdated = $Configuration->set(['content' => 0])->where('name', 'pump_state')->update();
				$data = $isUpdated ? 'Update PUMP to PUMP 1 successfully!' : 'Cant update PUMP State!';
				break;
			case '12': // Update to PUMP 2
				$isUpdated = $Configuration->set(['content' => 1])->where('name', 'pump_state')->update();
				$data = $isUpdated ? 'Update PUMP to PUMP 1 successfully!' : 'Cant update PUMP State!';
				break;
			case '90': // Request Screenshoot
				// break;

			default:
				break;
		}
		return $data;
	}

	/**
	 * get formula from paramters table
	 *
	 * @param [type] $param / code parameter
	 * @return string
	 */
	public function getParameterFormula($param)
	{
		$Parameter = new m_parameter();
		$formula = @$Parameter->where('code', $param)->first()->formula;
		return $formula;
	}

	public function updateParameterFormula($param, $content)
	{
		$Parameter = new m_parameter();
		$isUpdated = $Parameter->set(['formula' => $content])->where('code', $param)->update();
		return $isUpdated;
	}
}
