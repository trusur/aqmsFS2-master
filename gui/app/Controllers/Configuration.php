<?php

namespace App\Controllers;

use App\Models\m_configuration;
use App\Models\m_sensor_reader;
use Exception;

class Configuration extends BaseController
{

	protected $configuration;
	protected $sensor_reader;
	public function __construct()
	{
		parent::__construct();
		$this->configuration = new m_configuration();
		$this->sensor_reader = new m_sensor_reader();
	}
	public function index()
	{
		if (!$this->session->get("loggedin")) return redirect()->to(base_url("login?url_direction=configurations") );

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			return $this->saving_edit();
		}
		$data['__this'] = $this;
		$data['__modulename'] = 'Configurations'; /* Title */
		$data['__routename'] = 'configuration'; /* Route for check menu */
		$data['sensor_readers'] = $this->sensor_reader->findALL();
		$data['drivers'] = $this->getDrivers();
		return view("configuration/v_index", $data);
	}

	public function edit_driver(){
		try{
			$id = request()->getPost('id');
			$this->validate([
				'sensor_code' => 'required',
				'baud_rate' => 'permit_empty',
			]);
			$data = [
				'sensor_code' => request()->getPost('sensor_code'),
				'baud_rate' => request()->getPost('baud_rate'),
			];
			$this->sensor_reader->update($id, $data);
			return response()->setJSON([
				'success' => true,
				'message' => 'Driver has been updated',
			]);
		}catch(Exception $e){
			return response()->setStatusCode(500)->setJSON([
				'message' => $e->getMessage(),
			]);
		}
	}
	
	public function get_driver($id){
		try{
			return response()->setJSON([
				'success' => true,
				'data' => $this->sensor_reader->find($id)
			]);
		}catch(Exception $e){
			return response()->setStatusCode(500)->setJSON([
				'message' => $e->getMessage(),
			]);
		}
	}
	public function delete_driver($id){
		try{
			$this->sensor_reader->delete($id);
			return response()->setJSON([
				'success' => true,
				'message' => 'Driver has been deleted',
			]);
		}catch(Exception $e){
			return response()->setStatusCode(500)->setJSON([
				'message' => $e->getMessage(),
			]);
		}
	}
	public function add_driver(){
		try{
			$this->validate([
				'driver' => 'required',
				'sensor_code' => 'required',
				'baud_rate' => 'permit_empty',
			]);
			$data = [
				'driver' => request()->getPost('driver'),
				'sensor_code' => request()->getPost('sensor_code'),
				'baud_rate' => request()->getPost('baud_rate'),
			];
			$this->sensor_reader->insert($data);
			return response()->setJSON([
				'success' => true,
				'message' => 'Driver has been added',
			]);
		}catch(Exception $e){
			return response()->setStatusCode(500)->setJSON([
				'message' => $e->getMessage(),
			]);
		}
	}
	
	public function datatable_drivers(){
		try{
			$start = request()->getGet("start") ?? 0;
			$length = request()->getGet("length") ?? 10;
			$search = request()->getGet("search")["value"] ?? "";

			$where = "1=1";
			if($search) $where.=" and (driver like '%{$search}%')";
			$data['draw'] = request()->getGet("draw");
			$data['recordsTotal'] = $this->sensor_reader->countAllResults();
			$data['recordsFiltered'] = $this->sensor_reader->where($where)->countAllResults();
			$data['data'] = $this->sensor_reader->where($where)->orderBy('id', 'desc')->findAll($length, $start);
			return response()->setJSON($data);
		}catch(Exception $e){
			return response()->setStatusCode(500)->setJSON(['message' => $e->getMessage()]);
		}
	}

	public function getDrivers(){
		try{
			$files = scandir("../../drivers");
			return array_filter($files, function($file){
				return !in_array($file, ['.', '..','db_connect.py']) && substr($file, -3) == ".py";
			});
		}catch(Exception $e){
			log_message('error', "Cant scan drivers: ".$e->getMessage());
			return [];
		}
	}

	public function update(){
		try{
			$inputs = request()->getPost('name');
			foreach ($inputs as $name => $content) {
				$isExist = $this->configuration->where('name', $name)->countAllResults() > 0 ? true:false;
				if(!$isExist){
					$this->configuration->insert([
						'name' => $name,
						'content' => $content
					]);
				}else{
					$this->configuration->set('content', $content)->where('name', $name)->update();
				}
			}
			return response()->setJSON([
				'success' => true,
				'message' => 'Configuration has been updated',
			]);
		}catch(Exception $e){
			return response()->setStatusCode(500)->setJSON([
				'message' => 'Cant update configuration: '.$e->getMessage(),
			]);
		}
	}


	public function getConfiguration($name){
		try{
			return $this->configuration->where('name', $name)->first()->content ?? null;
		}catch(Exception $e){
			return null;
		}
	}

	public function saving_edit()
	{
		$ports = request()->getPost('sensor_code');
		$parameters = request()->getPost('driver');
		$baudrates = request()->getPost('baud_rate');
		foreach ($ports as $key => $port) {
			$data['baud_rate'] = $baudrates[$key];
			$data['sensor_code'] = $ports[$key];
			$this->sensor_reader->update($key, $data);
		}
		$this->configuration->set('content', request()->getPost('nama_stasiun'))->where('name', 'nama_stasiun')->update();
		$this->configuration->set('content', request()->getPost('id_stasiun'))->where('name', 'id_stasiun')->update();
		$this->configuration->set('content', request()->getPost('city'))->where('name', 'city')->update();
		$this->configuration->set('content', request()->getPost('province'))->where('name', 'province')->update();
		$this->configuration->set('content', request()->getPost('address'))->where('name', 'address')->update();
		$this->configuration->set('content', request()->getPost('latitude'))->where('name', 'latitude')->update();
		$this->configuration->set('content', request()->getPost('longitude'))->where('name', 'longitude')->update();
		$this->configuration->set('content', request()->getPost('pump_interval'))->where('name', 'pump_interval')->update();
		$this->configuration->set('content', request()->getPost('data_interval'))->where('name', 'data_interval')->update();
		$this->configuration->set('content', request()->getPost('graph_interval'))->where('name', 'graph_interval')->update();
		$this->configuration->set('content', request()->getPost('pump_speed'))->where('name', 'pump_speed')->update();
		$this->configuration->set('content', request()->getPost('pump_speed'))->where('name', 'pump_speed')->update();
		$this->configuration->set('content', request()->getPost('zerocal_schedule'))->where('name', 'zerocal_schedule')->update();
		$this->configuration->set('content', request()->getPost('zerocal_duration'))->where('name', 'zerocal_duration')->update();
		$this->configuration->set('content', request()->getPost('is_valve_calibrator'))->where('name', 'is_valve_calibrator')->update();
		$this->configuration->set('content', request()->getPost('restart_schedule'))->where('name', 'restart_schedule')->update();
		$this->configuration->set('content', request()->getPost('is_sentto_klhk'))->where('name', 'is_sentto_klhk')->update();
		$this->configuration->set('content', request()->getPost('klhk_api_server'))->where('name', 'klhk_api_server')->update();
		$this->configuration->set('content', request()->getPost('klhk_api_username'))->where('name', 'klhk_api_username')->update();
		$this->configuration->set('content', request()->getPost('klhk_api_password'))->where('name', 'klhk_api_password')->update();
		$this->configuration->set('content', request()->getPost('klhk_api_key'))->where('name', 'klhk_api_key')->update();
		$this->configuration->set('content', request()->getPost('is_sentto_trusur'))->where('name', 'is_sentto_trusur')->update();
		$this->configuration->set('content', request()->getPost('trusur_api_server'))->where('name', 'trusur_api_server')->update();
		$this->configuration->set('content', request()->getPost('trusur_api_username'))->where('name', 'trusur_api_username')->update();
		$this->configuration->set('content', request()->getPost('trusur_api_password'))->where('name', 'trusur_api_password')->update();
		$this->configuration->set('content', request()->getPost('trusur_api_key'))->where('name', 'trusur_api_key')->update();
		$this->configuration->set('content', request()->getPost('is_auto_restart'))->where('name', 'is_auto_restart')->update();
		$data['success'] = true;
		$data['message'] = 'Configuration has changed';
		$data['data'] = @$_POST;
		return json_encode($data);
	}
}
