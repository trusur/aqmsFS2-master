<?php

namespace App\Commands;

use App\Models\m_calibration;
use App\Models\m_configuration;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\m_sensor_value;
use App\Models\m_measurement;
use App\Models\m_measurement_log;
use App\Models\m_measurement_history;
use App\Models\m_parameter;
use App\Models\m_formula_reference;
use App\Models\m_realtime_value;
use DateTime;
use DivisionByZeroError;
use Error;
use Exception;
use ParseError;
use stdClass;

class FormulaMeasurementLogs extends BaseCommand
{
	/**
	 * The Command's Group
	 *
	 * @var string
	 */
	protected $group = 'CodeIgniter';

	protected $parameters;
	protected $formula_references;
	protected $sensor_values;
	protected $measurement_logs;
	protected $measurement_histories;
	protected $configurations;
	protected $lastPutData;
	protected $measurements;
	protected $realtime_value;
	protected $calibrations;

	public function __construct()
	{
		$this->parameters =  new m_parameter();
		$this->formula_references =  new m_formula_reference();
		$this->sensor_values =  new m_sensor_value();
		$this->measurement_logs =  new m_measurement_log();
		$this->measurement_histories =  new m_measurement_history();
		$this->configurations =  new m_configuration();
		$this->measurements =  new m_measurement();
		$this->realtime_value = new m_realtime_value();
		$this->calibrations = new m_calibration();
		$this->lastPutData = "0000-00-00 00:00";
	}

	/**
	 * The Command's Name
	 *
	 * @var string
	 */
	protected $name = 'command:formula_measurement_logs';

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
	protected $usage = 'command:formula_measurement_logs';

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

	public function hexToFloat($strHex)
	{
		$v = hexdec($strHex);
		$x = ($v & ((1 << 23) - 1)) + (1 << 23) * ($v >> 31 | 1);
		$exp = ($v >> 23 & 0xFF) - 127;
		return $x * pow(2, $exp - 23);
	}

	public function run(array $params)
	{
		$start = microtime(true);

		$check_calibration = $this->configurations
			->select("calibrations.id,parameters.code,calibrations.calibration_type,calibrations.is_executed, calibrations.start_calibration, calibrations.end_calibration")
			->join('calibrations', 'calibrations.id = configurations.content AND calibrations.end_calibration IS NULL AND calibrations.is_executed != 2')
			->join("parameters", "parameters.id = calibrations.parameter_id")
			->where("configurations.name", "is_calibration")
			->orderBy('calibrations.id', 'desc')
			->first();
		$parameter_calibration = $check_calibration['code'] ?? null;

		#tambahkan logic untuk ketika dia is_executed = 1, check data terakhir di measurement_log untuk waktu start  berdasarkan parameter
		# ketika is_executed = 2, update data terakhir di measurement_log untuk waktu end berdasarkan parameter

		# check for minute delay after calibration
		// $delay_1_minute_after_calibration = $this->calibrations
		// 	->select("start_calibration,end_calibration")
		// 	->where('is_executed', 2)
		// 	->orderBy('id','desc')
		// 	->first();

		// if ($delay_1_minute_after_calibration) {
		// 	$end_calibration = new \DateTime($delay_1_minute_after_calibration->end_calibration);
		// 	$end_calibration->modify('+1 minute');
		// 	if (new \DateTime() <= $end_calibration) {
		// 		CLI::write("1 minute delay at : ".$delay_1_minute_after_calibration->end_calibration);
		// 		return;
		// 	} 
		// }


		// $id_calibration = $check_calibartion['id'] ?? null;
		// $type_calibration = isset($check_calibration) ? ($check_calibration['calibration_type'] == 1 ? 'span' : ($check_calibration['calibration_type'] == 0 ? 'zero' : null)) : null;

		try {
			// Loop through sensor values and process them
			foreach ($this->sensor_values->findAll() as $sensor_value) {
				$sensor[$sensor_value->sensor_reader_id][$sensor_value->pin] = $sensor_value->value;
			}
			$flow_pm25 = $this->getPMFlow("pm25_flow", $sensor);
			$flow_pm10 = $this->getPMFlow("pm10_flow", $sensor);

			// Process parameters and perform necessary calculations
			foreach ($this->parameters->where("is_view=1 and formula is not null")->findAll() as $parameter) {
				try {
					$measured = 0;
					$value_ppb = null;
					$raw = 0;

					// If calibration parameter in process then skip, not get any data
					if ($parameter->code == $parameter_calibration) {
						CLI::write("Calibration in process : " . $parameter->code);
						continue;
					}

					try {
						eval("\$measured = $parameter->formula ?? -1;");
						$raw = $measured;
						if ($parameter->formula1) {
							eval("\$value_ppb = $parameter->formula1;");
						}
					} catch (Exception $e) {
						$measured = 0;
						$raw = 0;
					}

					$isInsertLog = true;
					$is_valid = '';

					//START VALIDASI
					if (!empty($parameter->range_max)) {
						if ($parameter->p_type == "particulate") {
							if ($parameter->code == "pm25" && $flow_pm25 < 1.6) {
								$is_valid = 20;
							} else if ($parameter->code == "pm10" && $flow_pm10 < 1.6) {
								$is_valid = 20;
							} else {
								if ($measured <= 0) {
									$is_valid = 12; // Abnormal
								} else if ($measured > $parameter->range_max) {
									$is_valid = 13; // Out of range
								} else {
									$is_valid = $this->isFlat($parameter->id, $measured);
								}
							}
						} else {
							if ($measured <= 0) {
								$is_valid = 12; // Abnormal
							} else if ($measured > $parameter->range_max) {
								$is_valid = 13; // Out of range
							} else {
								$is_valid = $this->isFlat($parameter->id, $measured);
							}
						}
					} else {
						$is_valid = 1; // Valid
					}

					//END VALIDASI
					// Insert log data
					$this->insert_logs([
						"parameter_id" => $parameter->id,
						"value" => $measured,
						"sensor_value" => $raw,
						"is_averaged" => 0,
						"time_group" => date("Y-m-d H:i:s"),
						"is_valid" => $is_valid,
						"xtimestamp" => date('Y-m-d H:i:s'),
					], $isInsertLog, $value_ppb);
				} catch (Exception $e) {
					log_message("error", "Formula Error [$parameter->code] : " . $e->getMessage());
				}
			}

			// Calculate and log execution time for debugging
			$end = microtime(true);
			CLI::write("Done in  : " . ($end - $start) . "s");
		} catch (Exception $e) {
			log_message("error", "Formula Convertion Service Error : " . $e->getMessage());
		}
	}


	public function isFlat($parameterId, $measured)
	{
		//check data Flat
		$lastValue = $this->measurement_logs
			->where("parameter_id='{$parameterId}'")
			->orderBy("id", "desc")->first();
		$lastValueAVG = $this->measurement_logs
			->where("parameter_id='{$parameterId}'")
			->orderBy("id", "desc")->findAll(60);
		$flat = 0;
		foreach ($lastValueAVG as $avg) {
			if ($lastValue->value == $avg->value) {
				$flat += 1;
			}
		}
		if ($flat == 60) {
			$is_valid = 14;
			$ids = array_column($lastValueAVG, 'id');
			$this->measurement_logs->set(['is_valid' => $is_valid])->whereIn('id', $ids)->update();
			if ($lastValue->value != $measured) {
				$is_valid = 11;
			}
		} else {
			//data normal
			$is_valid = 11;
		}
		return $is_valid;
	}

	public function getPMData($value)
	{
		try {
			$measured = 2;
			$data = explode(";", $value);
			return [$data[6], $data[6], $data[2], $data[3]];
		} catch (Exception $e) {
			CLI::write($e->getMessage(), "red pm");
			return false;
		}
	}

	public function getGasData($value)
	{
		try {
			$data = explode(";", $value);
			return [$data[2], $data[3], $data[4]];
		} catch (Exception $e) {
			CLI::write($e->getMessage(), "red gas");
			return false;
		}
	}

	public function getMeteorologiData($value)
	{
		try {
			$data = explode(";", $value);
			$response = new stdClass();
			$response->WD = (int) ($data[2] ?? 0);        // Arah angin (Wind Direction)
			$response->WS = (float) ($data[3] ?? 0); // Kecepatan angin (Wind Speed)
			$response->TEMPERATURE = (float) ($data[4] ?? 0); // Suhu udara (Temperature)
			$response->HUMIDITY = (int) ($data[5] ?? 0);  // Kelembapan udara (Humidity)
			$response->PRESSURE = (float)($data[6] ?? 0); // Tekanan udara (Pressure)
			$response->RAIN_INTENSITY = (float) ($data[9] ?? 0); // Curah hujan (Rainfall)
			// $response->SR = (float) ($data[10] ?? 0); // Radiasi matahari (Solar Radiation)

			// Mengembalikan objek response
			return $response;
		} catch (Exception $e) {
			CLI::write($e->getMessage(), "red weather");
			return false;
		}
	}

	public function getGasHC($value)
	{
		try {
			$data = explode(";", $value);
			# using senovol
			if (stripos($value, 'SENOVOL') === 0) {
				$hc_data = $data[2];
				# using semeatech
			} else if (stripos($value, '4ECM') === 0) {
				$hc_data = $data[5];
			}
			return $hc_data;
		} catch (Exception $e) {
			CLI::write($e->getMessage(), "red hc senovol");
			return false;
		}
	}
	public function insert_logs($logs, $insertLogs = true, $value_ppb)
	{
		try {
			if ($insertLogs) {
				$this->measurement_logs->insert($logs);
			}
			// Check is parameter exist
			$parameterId = $logs["parameter_id"];
			$isParameterExist = $this->realtime_value->where("parameter_id={$parameterId}")->countAllResults() > 0 ? true : false;
			if ($isParameterExist) {
				// Update value is parameter exist
				return $this->realtime_value->where("parameter_id={$parameterId}")
					->set([
						"measured" => $logs["value"],
						"raw" => $logs['sensor_value'],
						"ppb_value" => $value_ppb,
						"xtimestamp" => date("Y-m-d H:i:s"),
					])->update();
			}
			// Insert parameter
			return $this->realtime_value->insert([
				"parameter_id" => $parameterId,
				"measured" => $logs["value"],
				"raw"	=> $logs['sensor_value'],
				"ppb_value" => $value_ppb
			]);
		} catch (Exception $e) {
			log_message("error", "Insert Logs : " . $e->getMessage());
			return false;
		}
	}
	public function remove_outliers($dataset, $magnitude = 1)
	{
		$count = count($dataset);
		$mean = array_sum($dataset) / $count; // Calculate the mean
		$deviation = sqrt(array_sum(array_map([$this, "sd_square"], $dataset, array_fill(0, $count, $mean))) / $count) * $magnitude; // Calculate standard deviation and times by magnitude
		return array_filter($dataset, function ($x) use ($mean, $deviation) {
			return ($x <= $mean + $deviation && $x >= $mean - $deviation);
		}); // Return filtered array of values that lie within $mean +- $deviation.
	}
	public function sd_square($x, $mean)
	{
		return pow($x - $mean, 2);
	}

	public function getPMFlow($code, $sensor)
	{
		try {
			// $sensor for exec formula
			$sensor = $sensor;
			$measured = -1;
			$parameter = $this->parameters->select("sensor_value_id,formula")->where("code", $code)->first();
			if ($parameter->formula && $parameter->formula != 0) {
				eval("\$measured = $parameter->formula ?? -1;");
			}
			return $measured;
		} catch (Exception $e) {
			CLI::write($e->getMessage(), "red");
			return false;
		}
	}
}
