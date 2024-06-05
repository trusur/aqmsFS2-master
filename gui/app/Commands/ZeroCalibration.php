<?php

namespace App\Commands;

use App\Models\m_calibration;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\m_configuration;
use App\Models\m_sensor_reader;
use App\Models\m_sensor_value;
use Exception;

class ZeroCalibration extends BaseCommand
{
	/**
	 * The Command's Group
	 *
	 * @var string
	 */
	protected $group = 'CodeIgniter';

	protected $configurations;
	protected $calibrations;
	protected $sensor_readers;
	protected $sensor_values;

	public function __construct()
	{
		$this->configurations =  new m_configuration();
		$this->calibrations =  new m_calibration();
		$this->sensor_readers =  new m_sensor_reader();
		$this->sensor_values =  new m_sensor_value();
	}
	/**
	 * The Command's Name
	 *
	 * @var string
	 */
	protected $name = 'command:zero_calibration';

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
		$is_calibrating = false;
		$sensor_reader_id = @$this->sensor_readers->where(["driver" => "fs2_autozerovalve.py"])->find()[0]->id;
		$this->configurations->where(["name" => "is_zerocal"])->set(["content" => "0"])->update();
		$this->configurations->where(["name" => "zerocal_started_at"])->set(["content" => ""])->update();
		$this->configurations->where(["name" => "zerocal_finished_at"])->set(["content" => ""])->update();
		if (@$this->sensor_readers->where("driver LIKE 'fs2_autozerovalve.py' AND sensor_code != ''")->find()[0]->id > 0)
			$looping = true;
		else
			$looping = false;

		while ($looping) {
			try {
				$is_zerocal = @$this->configurations->where(["name" => "is_zerocal"])->find()[0]->content;
				$zerocal_schedule = @$this->configurations->where(["name" => "zerocal_schedule"])->find()[0]->content;
				if ($is_zerocal == 0 && substr($zerocal_schedule, 0, 5) == date("H:i") && $zerocal_schedule <= date("H:i:s")) {
					$this->configurations->where(["name" => "is_zerocal"])->set(["content" => "1"])->update();
					$is_zerocal = "1";
				}

				if ($is_zerocal == "1") {
					if (!$is_calibrating) {
						$sensor_value = @$this->sensor_values->where(["sensor_reader_id" => $sensor_reader_id])->find()[0]->value;
						if (@explode(";", @$sensor_value)[1] == "2") {
							$is_calibrating = true;
							$zerocal_duration = @$this->configurations->where(["name" => "zerocal_duration"])->find()[0]->content;
							$this->configurations->where(["name" => "zerocal_started_at"])->set(["content" => date("Y-m-d H:i:s")])->update();
							$this->configurations->where(["name" => "zerocal_finished_at"])->set(["content" => date("Y-m-d H:i:s", mktime(date("H"), date("i"), date("s") + $zerocal_duration))])->update();
							//send command to membrasens
						}
					}
				} else
					$is_calibrating = false;
			} catch (Exception $e) {
			}
			sleep(1);
		}
	}
}
