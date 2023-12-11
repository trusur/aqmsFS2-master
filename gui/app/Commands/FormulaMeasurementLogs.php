<?php

namespace App\Commands;

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
use Error;
use Exception;
use ParseError;

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
		while (true) {
			try{
				foreach ($this->sensor_values->findAll() as $sensor_value) {
					$sensor[$sensor_value->sensor_reader_id][$sensor_value->pin] = $sensor_value->value;
				}
				foreach ($this->parameters->where("is_view=1 and formula is not null")->findAll() as $parameter) {
					try{
						$measured = 0;
						$sensor_value = $this->sensor_values->find($parameter->sensor_value_id);
						// Check Is Raw Value bhFrom Motherboard Sensor
						if(count(explode($sensor_value->value,";")) == 1){
							try{
								eval("\$measured = $parameter->formula ?? -1;");
								$raw = $measured;
							}catch(ParseError | Error $e){
								$measured = -1;
								$raw = -1;
							}
						}
						$this->insert_logs([
							"parameter_id" => $parameter->id,
							"value" => $measured,
							"sensor_value" => $raw,
							"is_averaged" => 0,
						]);
					}catch(Exception $e){
						log_message("error","Formula Error [$parameter->code] : ".$e->getMessage());
					}
				}
			}catch(Exception $e){
				log_message("error","Formula Convertion Service Error : ".$e->getMessage());
			}
			sleep(1);
		}
	}
	public function insert_logs($logs){
		try{
			$this->measurement_logs->insert($logs);
			// Check is parameter exist
			$parameterId = $logs["parameter_id"];
			$isParameterExist = $this->realtime_value->where("parameter_id={$parameterId}")->countAllResults() > 0 ? true : false;
			if($isParameterExist){
				// Update value is parameter exist
				return $this->realtime_value->where("parameter_id={$parameterId}")
				->set([
					"measured" => $logs["value"],
				])->update();
			}
			// Insert parameter
			return $this->realtime_value->insert([
				"parameter_id" => $parameterId,
				"measured" => $logs["value"],
			]);

		}catch(Exception $e){
			log_message("error","Insert Logs : ".$e->getMessage());
			return false;
		}
	}
}
