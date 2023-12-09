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
}
