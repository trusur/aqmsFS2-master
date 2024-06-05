<?php

namespace App\Controllers;

use App\Models\m_calibration;
use App\Models\m_configuration;
use App\Models\m_sensor_value;
use Exception;

class Calibration extends BaseController
{
	public function __construct()
	{
		parent::__construct();
		$this->calibrations = new m_calibration();
		$this->configuration = new m_configuration();
		$this->sensor_values = new m_sensor_value();
	}

	public function index()
	{
		$data['__modulename'] = 'Calibrations'; /* Title */
		$data['__routename'] = 'calibration'; /* Route for check menu */
		$data['__this'] = $this;
		$linechartcolors[0] = "#000000";
		$linechartcolors[1] = "#0000ff";
		$linechartcolors[2] = "#00ff00";
		$linechartcolors[3] = "#00ffff";
		$linechartcolors[4] = "#ff0000";
		$linechartcolors[5] = "#ff00ff";
		$linechartcolors[6] = "#ffff00";
		$linechartcolors[7] = "#888888";
		$data["is_valve_calibrator"] = @$this->configuration->where(["name" => "is_valve_calibrator"])->findAll()[0]->content;
		$data["linechartcolors"] = $linechartcolors;
		$data["sensor_values"] = $this->sensor_values->where("`value` LIKE 'SEMEATECH%'")->findAll();

		echo view("calibrations/v_index", $data);
	}

	public function zero_calibration_starting($calibrator_name, $zerocal_duration)
	{
		$this->configuration->where(["name" => "calibrator_name"])->set(["content" => $calibrator_name])->update();
		$this->configuration->where(["name" => "zerocal_duration"])->set(["content" => $zerocal_duration])->update();
		$this->configuration->where(["name" => "is_zerocal"])->set(["content" => 1])->update();
		echo json_encode(["status" => "ok"]);
	}

	public function force_stop_zero_calibration()
	{
		$this->configuration->where(["name" => "zerocal_finished_at"])->set(["content" => ""])->update();
		echo json_encode(["status" => "ok"]);
	}

	public function get_data()
	{
		$data["zerocal_started_at"] = @$this->configuration->where(["name" => "zerocal_started_at"])->find()[0]->content;
		$data["zerocal_finished_at"] = @$this->configuration->where(["name" => "zerocal_finished_at"])->find()[0]->content;
		$remaining = strtotime($data["zerocal_finished_at"]) - strtotime(date("Y-m-d H:i:s"));
		if ($remaining < 0 || $data["zerocal_finished_at"] == "")
			$data["remaining"] = "";
		else
			$data["remaining"] = date("i:s", $remaining);
		$data["is_zerocalibrating"] = 0;
		if ($data["zerocal_started_at"] != "" && $data["zerocal_started_at"] != "")
			$data["is_zerocalibrating"] = 1;
		echo json_encode($data);
	}
	public function datatable()
	{
		try {
			$calibrationLogs = $this->calibrations
				->select('sensor_readers.sensor_code, calibrations.*')
				// ->groupBy('started_at')
				->join('sensor_readers', 'sensor_readers.id = calibrations.sensor_reader_id', 'left')
				->orderBy('calibrations.id', 'desc')->findAll(5);
			$data['draw'] = @$this->request->getGet('draw') ?  (int) $this->request->getGet('draw') : 1;
			$data['recordsTotal'] = 0;
			$data['recordsFiltered'] = 0;
			$data['data'] = $calibrationLogs;
			return $this->response->setJson($data);
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}
	function array2csv(array &$array)
	{
		if (count($array) == 0) {
			return null;
		}
		ob_start();
		$df = fopen("php://output", 'w');
		fputcsv($df, array_keys(reset($array)));
		foreach ($array as $row) {
			fputcsv($df, $row);
		}
		fclose($df);
		return ob_get_clean();
	}
	function download_send_headers($filename)
	{
		// disable caching
		$now = gmdate("D, d M Y H:i:s");
		// header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
		header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
		header("Last-Modified: {$now} GMT");

		// force download  
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");

		// disposition / encoding on response body
		header("Content-Disposition: attachment;filename={$filename}");
		header("Content-Transfer-Encoding: binary");
	}

	public function getDataExport($started_at, $end_at)
	{
		try {
			$whereRaw = "1=1";
			$whereRaw .= !empty($started_at) ? " AND DATE_FORMAT(started_at,'%Y-%m-%d') >= '${started_at}'" : "";
			$whereRaw .= !empty($end_at) ? " AND DATE_FORMAT(calibrations.xtimestamp,'%Y-%m-%d') <= '${end_at}'" : "";
			// print_r($whereRaw);
			// exit();
			$calibrationLogs = $this->calibrations
				->asArray()
				->select('sensor_readers.sensor_code, calibrations.*')
				// ->groupBy('started_at')
				->join('sensor_readers', 'sensor_readers.id = calibrations.sensor_reader_id', 'left')
				->where($whereRaw)
				->orderBy('calibrations.id', 'desc')->findAll();
			return $calibrationLogs;
		} catch (Exception $e) {
			return [];
		}
	}

	public function validateExport()
	{
		try {
			$started_at = $this->request->getGet('started_at');
			$end_at = $this->request->getGet('end_at');
			if (count($this->getDataExport($started_at, $end_at)) > 0) {
				$data['success'] = true;
				$data['message'] = 'Generate CSV, Please wait...';
				$data['download_url'] = base_url("calibration/export?started_at={$started_at}&end_at={$end_at}");
				return $this->response->setJSON($data);
			}
			return $this->response->setJSON(['success' => false, 'message' => 'No data available!']);
		} catch (Exception $e) {
			return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
		}
	}

	public function export()
	{
		try {
			$started_at = $this->request->getGet('started_at');
			$end_at = $this->request->getGet('end_at');
			$calibrationLogs = $this->getDataExport($started_at, $end_at);
			$export = [];
			foreach ($calibrationLogs as $key =>  $log) {
				$export[$key]['Started at'] = $log['started_at'];
				$export[$key]['End at'] = $log['xtimestamp'];
				$export[$key]['Calibrator Name'] = $log['calibrator_name'];
				$export[$key]['Sensor'] = $log['sensor_code'];
				$export[$key]['PIN'] = $log['pin'];
				$export[$key]['Value'] = $log['value'];
			}
			$filename = "Report Calibration {$started_at} - {$end_at} " . rand(0, 99);
			$this->download_send_headers("{$filename}.csv");
			return $this->array2csv($export);
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}
}
