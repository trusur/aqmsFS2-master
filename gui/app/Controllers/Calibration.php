<?php

namespace App\Controllers;

use App\Models\m_calibration;
use App\Models\m_calibration_log;
use App\Models\m_configuration;
use App\Models\m_parameter;
use App\Models\m_sensor_value;
use Exception;

class Calibration extends BaseController
{
	protected $calibrations;
	protected $configuration;
	protected $sensor_values;
	protected $parameters;
	protected $calibration_logs;
	public function __construct()
	{
		parent::__construct();
		$this->calibrations = new m_calibration();
		$this->calibration_logs = new m_calibration_log();
		$this->configuration = new m_configuration();
		$this->sensor_values = new m_sensor_value();
		$this->parameters = new m_parameter();
	}

	public function index(){
		$data['__modulename'] = 'Calibrations'; /* Title */
		$data['__routename'] = 'calibration'; /* Route for check menu */
		$data['parameters'] = $this->parameters->where(["p_type" => "gas","is_view" => 1])->findAll();
		return view("calibrations/v_index", $data);
	}

	public function store(){
		try{
			$params['parameter_id'] = request()->getPost('parameter_id');
			$params['calibration_type'] = request()->getPost('calibration_type') ?? 0;
			$params['target_value'] = request()->getPost('target_value') ?? 0;
			$params['duration'] = get_config('span_cal_duration') ?? 0;

			$data = $this->calibrations->insert($params);
			return $this->response->setJSON([
				'success' => true,
				'message' => 'Calibration has been added to queue',
				'data' => $data
			]);

		}catch(Exception $e){
			return $this->response->setJSON([
				'success' => false,
				'message' => $e->getMessage()
			]);
		}
	}
	public function logs(){
		$data['__modulename'] = 'Calibration Logs'; /* Title */
		$data['__routename'] = 'calibration'; /* Route for check menu */
		$data['parameters'] = $this->parameters->where(["p_type" => "gas","is_view" => 1])->findAll();
		return view("calibrations/v_log", $data);
	}
	public function detail_log($id)
	{
		$calibration = $this->calibrations
			->select("parameters.caption_id,calibrations.*")
			->join("parameters", "parameters.id = calibrations.parameter_id", "left")
			->find($id);
		$data['__modulename'] = 'Detail Calibration Logs '.$calibration->caption_id ; /* Title */
		$data['__routename'] = 'calibration'; /* Route for check menu */
		$data['calibration'] = $calibration;
		return view("calibrations/v_log_detail", $data);
	}

	public function datatable_logs(){
		try{
			$length = request()->getGet('length') ?? 10;
			$start = request()->getGet('start') ?? 0;
			$parameterId  = request()->getGet('parameter_id');
			$calibrationType  = request()->getGet('calibration_type');
			$isExecuted  = request()->getGet('is_executed');
			$where = "1=1";

			if($parameterId) $where.=" and parameter_id = '{$parameterId}'";
			if($calibrationType != null) $where.=" and calibration_type = '{$calibrationType}'";
			if($isExecuted != null) $where.=" and is_executed = '{$isExecuted}'";

			$calibration_logs = $this->calibrations
				->select("parameters.caption_id,calibrations.*")
				->join("parameters","calibrations.parameter_id = parameters.id")
				->where($where)
				->orderBy("calibrations.id","desc")
				->findAll($length, $start);
			
			$data['draw'] = request()->getGet('draw');
			$data['recordsTotal'] = $this->calibrations->countAll();
			$data['recordsFiltered'] = $this->calibrations->where($where)->countAllResults();
			$data['data'] = $calibration_logs;
			return $this->response->setJSON($data);

		}catch(Exception $e){
			return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
		}
	}

	public function get_calibration_logs($calibration_id){
		try{
			$data = $this->calibration_logs
				->select("id,value,created_at")
				->where("calibration_id",$calibration_id)
				->orderBy("created_at","desc")
				->findAll(1000);
			$rows = $this->calibration_logs->where("calibration_id",$calibration_id)->countAllResults();
			return $this->response->setJSON([
				'success' => true,
				'total_rows' => $rows,
				'data' => $data
			]);
		}catch(Exception $e){
			return $this->response->setStatusCode(500)->setJSON([
				'message' => $e->getMessage()
			]);
		}
	}

}
