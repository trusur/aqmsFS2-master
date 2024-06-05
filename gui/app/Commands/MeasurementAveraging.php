<?php

namespace App\Commands;

use App\Models\m_configuration;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\m_sensor_value;
use App\Models\m_measurement;
use App\Models\m_measurement_log;
use App\Models\m_parameter;
use Exception;

class MeasurementAveraging extends BaseCommand
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
	protected $configurations;
	protected $measurements;

	public function __construct()
	{
		$this->parameters =  new m_parameter();
		$this->sensor_values =  new m_sensor_value();
		$this->measurement_logs =  new m_measurement_log();
		$this->configurations =  new m_configuration();
		$this->measurements =  new m_measurement();
	}
	/**
	 * The Command's Name
	 *
	 * @var string
	 */
	protected $name = 'command:measurement_averaging';

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

	public function get_measurement_logs_range($minute)
	{
		$id_end = @$this->measurement_logs->orderBy("id DESC")->findAll()[0]->id;
		$lasttime = date("Y-m-d H:i", mktime(date("H"), date("i") - $minute));
		$mm = date("i") * 1;
		$current_time = date("Y-m-d H:i") . ":00";
		$lastPutData = @$this->measurements->orderBy("time_group DESC")->findAll()[0]->time_group;
		if ($mm % $minute == 0 && $lastPutData != $current_time) {
			$id_start = @$this->measurement_logs->where("xtimestamp >= '" . $lasttime . ":00'")->where("is_averaged", 0)->orderBy("id")->findAll()[0]->id;
			if ($id_start > 0) {
				$measurement_logs = $this->measurement_logs->where("id BETWEEN '" . $id_start . "' AND '" . $id_end . "'")->where("is_averaged", 0)->findAll();
				$return["id_start"] = $id_start;
				$return["id_end"] = $id_end;
				$return["waktu"] = $current_time;
				$return["data"] = $measurement_logs;
				return $return;
			} else {
				return 0;
			}
		} else {
			return 0;
		}
	}

	public function measurements_averaging()
	{
		$data_interval = $this->configurations->where("name", "data_interval")->findAll()[0]->content;
		$measurement_logs = $this->get_measurement_logs_range($data_interval);
		if ($measurement_logs != 0) {
			$total = [];
			$numdata = [];
			foreach ($measurement_logs["data"] as $measurement_log) {
				@$total[$measurement_log->parameter_id] += $measurement_log->value;
				@$lastdata[$measurement_log->parameter_id] = $measurement_log->value;
				@$numdata[$measurement_log->parameter_id]++;
			}
			foreach ($this->parameters->where("is_view", 1)->findAll() as $parameter) {
				if (@$numdata[$parameter->id] > 0) {
					if ($parameter->p_type == "gas" || $parameter->p_type == "particulate")
						$value = round($total[$parameter->id] / $numdata[$parameter->id], 0);
					else
						$value = round($lastdata[$parameter->id], 2);

					// $value = round($total[$parameter->id] / $numdata[$parameter->id], 2);
					$measurements = [
						"time_group" => $measurement_logs["waktu"],
						"parameter_id" => $parameter->id,
						"value" => $value,
						"sensor_value" => 0,
						"is_sent_cloud" => 0,
						"is_sent_klhk" => 0,
					];
					try {
						$this->measurements->save($measurements);
					} catch (Exception $e) {
					}
				}
			}
			// $this->measurement_logs->set(["is_averaged" => 1])->where("id BETWEEN '" . $measurement_logs["id_start"] . "' AND '" . $measurement_logs["id_end"] . "'")->update();
			// $this->measurement_logs->where("id BETWEEN '" . $measurement_logs["id_start"] . "' AND '" . $measurement_logs["id_end"] . "'")->delete();
			// $this->measurement_logs->truncate();
		}
	}

	public function run(array $params)
	{
		while (true) {
			// $this->measurements->where("xtimestamp < ('" . date("Y-m-d H:i:s") . "' - INTERVAL 2 HOUR)")->delete();
			$this->measurements_averaging();
			sleep(1);
		}
	}
}
