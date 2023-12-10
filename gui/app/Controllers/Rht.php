<?php

namespace App\Controllers;

use App\Models\m_sensor_value;
use App\Models\m_configuration;
use App\Models\m_sensor_value_log;
use Exception;

class Rht extends BaseController
{

	protected $sensor_values;
	protected $configurations;
	protected $sensor_value_logs;
	public function __construct()
	{
		parent::__construct();
		$this->sensor_values = new m_sensor_value();
		$this->configurations = new m_configuration();
		$this->sensor_value_logs = new m_sensor_value_log();
	}

	public function index()
	{
		$data['__this'] = $this;
		$data['__modulename'] = 'RHT'; /* Title */
		$data['__routename'] = 'rht'; /* Route for check menu */
		$data["sensor_values"] = $this->sensor_values->orderBy('sensor_reader_id ASC, pin ASC')->findAll();
		return view("rht/v_index", $data);
	}

	public function get_all(){
		try{
			return response()->setJSON([
				"success" => true,
				"data" => $this->sensor_values->orderBy('sensor_reader_id ASC, pin ASC')->findAll()
			]); 
		}catch(Exception $e){
			return response()->setJSON([
				"message" => $e->getMessage(),
			]);
		}
	}
}
