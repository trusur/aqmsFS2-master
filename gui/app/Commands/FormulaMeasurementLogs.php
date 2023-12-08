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
use Exception;

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

	public function __construct()
	{
		$this->parameters =  new m_parameter();
		$this->formula_references =  new m_formula_reference();
		$this->sensor_values =  new m_sensor_value();
		$this->measurement_logs =  new m_measurement_log();
		$this->measurement_histories =  new m_measurement_history();
		$this->configurations =  new m_configuration();
		$this->measurements =  new m_measurement();
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
			// $now = date("Y-m-d H:i:s");
			// $this->measurement_logs->where("(is_averaged = 1 AND xtimestamp < ('{$now}' - INTERVAL 48 HOUR))")->delete();

			foreach ($this->sensor_values->findAll() as $sensor_value) {
				$sensor[$sensor_value->sensor_reader_id][$sensor_value->pin] = $sensor_value->value;
			}

			foreach ($this->parameters->where("is_view", 1)->findAll() as $parameter) {
				if ($parameter->formula) {
					if (substr($parameter->formula, 0, 21) != "formula_references==>") {
						try {
							@eval("\$data[$parameter->id] = $parameter->formula;");
						} catch (Exception $e) {
							log_message('error', "Formula Error [{$parameter->code}]: ".$e->getMessage());
						}
						$sensor_value = $this->sensor_values->where("id", $parameter->sensor_value_id)->first();
						$sensor_check = $sensor[$sensor_value->sensor_reader_id][$sensor_value->pin] ?? null;
						if (strpos(" " . $sensor_check, "FS2_MEMBRASENS") > 0) {
							try {
								$arr_sensor_value = explode('$sensor[' . $sensor_value->sensor_reader_id . '][' . $sensor_value->pin . '])[', $parameter->formula)[1];
								$arr_sensor_value = explode("])", $arr_sensor_value)[0];
								$sensor_value = explode(";", $sensor_check)[$arr_sensor_value + 4];
							} catch (Exception $e) {
								$sensor_value = (float) $sensor_check * 1;
							}
						} elseif ((count(explode(",", $sensor_check)) == 7) && (count(explode(";", $sensor_check)) == 2)) {
							// Check PM AQMS FS1 Value
							try {
								$sensor_value = @eval("\$parameter->formula;");
							} catch (Exception $e) {
							}
						} else {
							$sensor_value = (float) $sensor_check * 1;
						}
					} else {
						$parameter_formulas = explode("==>", $parameter->formula);
						$x = 0;
						$data[$parameter->id] = 0;
						$sensor_value = 0;
						try {
							@eval("\$x = $parameter_formulas[1];");
						} catch (Exception $e) {
							log_message('error', "Formula Error [{$parameter->code}]: ".$e->getMessage());
						}
						$formula_references = @$this->formula_references->where("parameter_id", $parameter->id)->where("(" . $x . ") BETWEEN min_value AND max_value")->findAll()[0];
						if (@$formula_references->id > 0) {
							try {
								@eval("\$data[$parameter->id] = $formula_references->formula;");
							} catch (Exception $e) {
							}
						}
						$sensor_value = $x;
					}
				} else {
					$data[$parameter->id] = 0;
					$sensor_value = 0;
				}
				$measurement_logs = [
					"parameter_id" => $parameter->id,
					"value" => ($data[$parameter->id] < 0) ? 0 : $data[$parameter->id],
					"sensor_value" => $sensor_value,
					"is_averaged" => 0
				];
				$this->measurement_logs->save($measurement_logs);
			}
			sleep(1);
		}
	}
}
