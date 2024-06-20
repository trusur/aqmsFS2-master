<?php

namespace App\Controllers;

use App\Models\m_configuration;
use App\Models\m_parameter;
use Exception;

class Home extends BaseController
{

	protected $parameter;
	protected $configuration;
	public function __construct()
	{
		parent::__construct();
		$this->parameter = new m_parameter();
		$this->configuration = new m_configuration();
	}
	public function index()
	{
		$data['__modulename'] = 'Dashboard'; /* Title */
		$data['__routename'] = 'dashboard'; /* Route for check menu */
		$data['gases'] = $this->parameter->where(['is_view' => 1, 'p_type' => 'gas'])->findAll();
		$data['particulates'] = $this->parameter->where(['is_view' => 1, 'p_type' => 'particulate'])->findAll();
		$data['weathers'] = $this->parameter->where(['is_view' => 1, 'p_type' => 'weather'])->findAll();
		$data['flow_meters'] = $this->parameter->where(['is_view' => 1, 'p_type' => 'flowmeter'])->findAll();
		$data['stationname'] = $this->configuration->where(['name' => 'nama_stasiun'])->first()->content ?? '-';
		$data['is_cems'] = $this->configuration->where(['name' => 'is_cems'])->first()->content ?? 0;
		$data['pump_interval'] = $this->configuration->where(['name' => 'pump_interval'])->first()->content ?? 360;
		return view("v_home", $data);
	}

	public function pump()
	{
		try{
			$pumpState = $this->configuration->where(["name" => "pump_has_trigger_change"])->first(); 

			if($pumpState){
				// Check Is Pump State Exist in Configuration
				$switch = $pumpState->content == 1 ? 0 : 1;
				$pumpStateData['content'] 	= $switch;
				$this->configuration->update($pumpState->id, $pumpStateData);
			}else{
				// Insert New Configuration if empty
				$pumpStateData['name'] 		= 'pump_has_trigger_change';
				$pumpStateData['content'] 	= 1;
				$this->configuration->insert($pumpStateData);
			}
			return $this->response->setJSON(['success' => true, 'message' => 'Pump switch success']);
		}catch(Exception $e){
			return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
		}
	}
}
