<?php

namespace App\Controllers;

use App\Models\m_calibration;
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
	public function __construct()
	{
		parent::__construct();
		$this->calibrations = new m_calibration();
		$this->configuration = new m_configuration();
		$this->sensor_values = new m_sensor_value();
		$this->parameters = new m_parameter();
	}

	public function index()
	{
		$data['__modulename'] = 'Calibrations'; /* Title */
		$data['__routename'] = 'calibration'; /* Route for check menu */
		$data['parameters'] = $this->parameters->where(["p_type" => "gas","is_view" => 1])->findAll();
		return view("calibrations/v_index", $data);
	}

}
