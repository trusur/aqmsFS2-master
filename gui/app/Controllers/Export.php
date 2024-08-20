<?php

namespace App\Controllers;

use App\Models\m_configuration;
use App\Models\m_measurement;
use App\Models\m_parameter;
use Exception;

class Export extends BaseController
{

	protected $measurement;
	protected $configuration;
	protected $parameters;
	public function __construct()
	{
		parent::__construct();
		$this->measurement = new m_measurement();
		$this->configuration = new m_configuration();
		$this->parameters = new m_parameter();
	}
	public function index()
	{
		$data['__modulename'] = 'Exports'; /* Title */
		$data['__routename'] = 'export'; /* Route for check menu */
		$data['parameters'] = $this->parameters->where('is_view', 1)->findAll();
		$data['data_sources'] = $this->getTables();
		return view("export/v_index", $data);
	}

	public function export(){
		try{
			$db = \Config\Database::connect();
			$id_station = $this->configuration->where('name', 'id_stasiun')->first()->content ?? '-';
			
			$data_source = request()->getGet("data_source") ?? "measurements";
			$begindate = request()->getGet('begindate');
			$enddate = request()->getGet('enddate');

			$where = "1=1";
			if($begindate) $where.=" and time_group >= '{$begindate}'";
			if($enddate) $where.=" and time_group <= '{$enddate}'";

			$tGroups = $db->table($data_source)
				->selectMax('id')
				->select('time_group')
				->where($where)
				->groupBy('time_group')
				->orderBy('time_group', 'DESC')
				->limit(1000)->get()->getResultObject();
				
			// $parameters = [];
			ob_start();
			$parameters = $this->parameters->where('is_view', 1)->findAll();
			$headers = [
				"id_stasiun",
				"waktu",
			];
			foreach ($parameters as $value) {
				$headers[] = $value->code;
			}
			$headers[] = "is_sent_klhk";
			$headers[] = "is_sent_cloud";
			// Outfile CSV
			if(!is_dir(getcwd()."/export")){
				mkdir(getcwd()."/export",077, true);
			}
			$filename = "export-".date("YmdHis");
			$output = fopen(getcwd()."/export/{$filename}.csv", "w"); 
			fputcsv($output, $headers);
			foreach ($tGroups as $key => $tGroup) {
				$data['id_stasiun'] = $id_station;
				$data['waktu'] = $tGroup->time_group;
				foreach ($parameters as $parameter) {
					$measurement = $db->table($data_source)
						->select("value,is_sent_klhk,is_sent_cloud")
						->where("time_group = '{$tGroup->time_group}' and parameter_id = '{$parameter->id}'")
						->get()->getFirstRow();
					$data[$parameter->code] = $measurement->value ?? null;
				}
				$data["is_sent_klhk"] = ($measurement->is_sent_klhk??0) == 1 ? "Sent" : "Not Sent";
				$data["is_sent_cloud"] = ($measurement->is_sent_cloud??0) == 1 ? "Sent" : "Not Sent";
				fputcsv($output, $data);
			}
			fclose($output);
			ob_clean();
			return redirect()->to(base_url("export/$filename.csv"));
		}catch(Exception $e){
			return redirect()->back()->with('error', $e->getMessage());

		}
	}

	public function getTables(){
		try{
			$db = \Config\Database::connect();
			return array_filter($db->listTables(), function($table){
				return substr($table,0,strlen('measurements')) == 'measurements' && $table != 'measurements';
			});
		}catch(Exception $e){
			return [];
		}
	}

	public function datatable()
	{
		try{
			$db = \Config\Database::connect();

			// Configuration
			$id_station = $this->configuration->where('name', 'id_stasiun')->first()->content ?? '-';
			/*
				Filter
			*/
			$data_source = request()->getGet("data_source") ?? "measurements";
			$length = request()->getGet("length") ?? 10;
			$start = request()->getGet("start") ?? 0;
			$begindate = request()->getGet('begindate');
			$enddate = request()->getGet('enddate');

			$where = "1=1";
			if($begindate) $where.=" and time_group >= '{$begindate}'";
			if($enddate) $where.=" and time_group <= '{$enddate}'";

			$recordsTotal = $db->table($data_source)
				->selectMax('id')
				->select('time_group')
				->groupBy('time_group')->countAllResults();
			$recordsFiltered = $db->table($data_source)
				->selectMax('id')
				->select('time_group')
				->where($where)
				->groupBy('time_group')
				->countAllResults();
			$tGroups = $db->table($data_source)
				->selectMax('id')
				->select('time_group')
				->where($where)
				->groupBy('time_group')
				->orderBy('time_group', 'DESC')
				->limit($length,$start)->get()->getResultObject();
			$parameters = [];
			foreach ($tGroups as $key => $tGroup) {
				$parameters[$key]['id_stasiun'] = $id_station;
				$parameters[$key]['waktu'] = $tGroup->time_group;
				foreach ($this->parameters->where('is_view', 1)->findAll() as $parameter) {
					$measurement = $db->table($data_source)
						->select("value,is_sent_klhk,is_sent_cloud")
						->where("time_group = '{$tGroup->time_group}' and parameter_id = '{$parameter->id}'")
						->get()->getFirstRow();
					$parameters[$key][$parameter->code] = $measurement->value ?? null;
					$parameters[$key]["is_sent_klhk"] = $measurement->is_sent_klhk ?? 0;
					$parameters[$key]["is_sent_cloud"] = $measurement->is_sent_cloud ?? 0;
				}
			}
			$data['draw'] = request()->getGet('draw');
			$data['recordsTotal'] = $recordsTotal;
			$data['recordsFiltered'] = $recordsFiltered;
			$data['data'] = $parameters;
			return response()->setJSON($data);
		}catch(Exception $e){
			return response()->setStatusCode(500)->setJSON(['message' => $e->getMessage()]);
		}
	}
}
