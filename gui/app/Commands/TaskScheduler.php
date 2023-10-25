<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\m_configuration;
use App\Models\m_measurement_log;
use Exception;

class TaskScheduler extends BaseCommand
{
	/**
	 * The Command's Group
	 *
	 * @var string
	 */
	protected $group = 'CodeIgniter';

	protected $configurations;
	protected $measurement_logs;


	public function __construct()
	{
		$this->configurations =  new m_configuration();
		$this->measurement_logs =  new m_measurement_log();
	}
	/**
	 * The Command's Name
	 *
	 * @var string
	 */
	protected $name = 'command:task_scheduler';

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
		while (true) {
			try {
				$restart_schedule = @$this->configurations->where(["name" => "restart_schedule"])->find()[0]->content;
				if ($restart_schedule != "") {
					$last_restart_schedule = @$this->configurations->where(["name" => "last_restart_schedule"])->find()[0]->content;
					if (substr($restart_schedule, 0, 5) == date("H:i") && substr($restart_schedule, 0, 5) . ":00" <= date("H:i:s") && $last_restart_schedule != date("Y-m-d H:i")) {
						$this->configurations->where(["name" => "last_restart_schedule"])->set(["content" => date("Y-m-d") . " " . substr($restart_schedule, 0, 5)])->update();
						$this->configurations->where(["name" => "is_psu_restarting"])->set(["content" => "1"])->update();
					}
				}
			} catch (Exception $e) {
			}

			try {
				if (!isset($counter)) $counter = 59;
				$counter++;
				if ($counter >= 60) {
					$counter = -1;
					$id_stasiun = @$this->configurations->where(["name" => "id_stasiun"])->first()->content;
					$parameter_ids = [1, 2, 3, 4, 5, 12, 14];
					foreach ($parameter_ids as $parameter_id) {
						// for ($parameter_id = 1; $parameter_id < 6; $parameter_id++) {
						if (!isset($is_anomaly[$parameter_id])) $is_anomaly[$parameter_id] = true;
						foreach ($this->measurement_logs->where("parameter_id", $parameter_id)->orderBy("id DESC")->findAll(20) as $key => $measurement_log) {
							if ($key == 0) { //cek timestamp pertama
								$value = $measurement_log->value;
								$to_time = strtotime(date("Y-m-d H:i:s"));
								$from_time = strtotime($measurement_log->xtimestamp);
								$age = abs($to_time - $from_time) / 60;
								if ($age > 2) break; //sudah lebih dari 2 menit
							} else {
								if ($value != $measurement_log->value) { //ada yg beda, jadi tidak flat
									$is_anomaly[$parameter_id] = false;
									break;
								}
							}
						}
					}

					$status_codes = "";
					foreach ($is_anomaly as $parameter_id => $is_anomaly_) {
						if ($is_anomaly_) $status_codes .= ";43" . $parameter_id;
					}

					$curl = curl_init();
					curl_setopt_array($curl, array(
						CURLOPT_URL => "https://dashboards.trusur.tech/api/aqms/push_status/" . $id_stasiun . $status_codes,
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_MAXREDIRS => 10,
						CURLOPT_TIMEOUT => 30,
						CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
						CURLOPT_CUSTOMREQUEST => "GET",
						CURLOPT_SSL_VERIFYPEER => 0, //skip SSL Verification | disable SSL verify peer
					));
					$response = curl_exec($curl);
					$err = curl_error($curl);
					curl_close($curl);
				}
			} catch (Exception $e) {
			}
			sleep(1);
		}
	}
}
