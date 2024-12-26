<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\m_configuration;
use App\Models\m_measurement_log;
use App\Models\m_parameter;
use App\Models\m_realtime_value;
use Exception;

class Measurementlog extends BaseController
{
	protected $measurement_log;
	protected $parameter;
	protected $realtime_value;
	public function __construct()
	{
		$this->measurement_log = new m_measurement_log();
		$this->parameter = new m_parameter();
		$this->configuration = new m_configuration();
		$this->realtime_value = new m_realtime_value();
	}

	public function index(){
		try{
			$data['success'] = true;
			$data['config'] = [
				'pump_state' => $this->configuration->where(['name' => 'pump_state'])->first()->content ?? 0,
				'pump_last' => $this->configuration->where(['name' => 'pump_last'])->first()->content ?? date("Y-m-d H:i:s",strtotime("-1 hour")),
				'pump_interval' => $this->configuration->where(['name' => 'pump_interval'])->first()->content ?? 360,
				'pump_mode' => $this->configuration->where(['name' => 'pump_mode'])->first()->content ?? 0,
				'now' => date('Y-m-d H:i:s'),
			];
			$data['logs'] = $this->realtime_value
				->select("parameters.code as code, parameters.default_unit,parameters.p_type,parameters.molecular_mass, realtime_values.*")
				->join("parameters","realtime_values.parameter_id = parameters.id","left")
				->findAll();
			return $this->response->setJSON($data);
		}catch(Exception $e){
			log_message('error', "Measurementlog->index: ".$e->getMessage());
			return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $e->getMessage()]);
		}
	}
}
