<?php

namespace App\Controllers;

use App\Models\m_parameter;
use App\Models\m_sensor_value;
use CodeIgniter\HTTP\IncomingRequest;
use Exception;

class Parameter extends BaseController
{

	protected $parameter;
	protected $sensor_value;
	public function __construct()
	{
		parent::__construct();
		$this->parameter = new m_parameter();
		$this->sensor_value = new m_sensor_value();
	}
	public function index()
	{
		if (!$this->session->get("loggedin")) return redirect()->to(base_url() . '/login?url_direction=parameter');

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			return $this->saving();
		}
		$data['__modulename'] = 'Parameters'; /* Title */
		$data['__routename'] = 'parameter'; /* Route for check menu */
		return view("parameter/v_index", $data);
	}

	public function datatable(){
		try{
			$start = request()->getGet('start') ?? 0;
			$length = request()->getGet('length') ?? 10;
			$search = request()->getGet('search')['value'] ?? '';
			$type = request()->getGet('type');
			$is_view = request()->getGet('is_view');

			$where = "1=1";
			if($search) $where.=" and (code like '%{$search}%' or caption_en like '%{$search}%')";
			if($type) $where.=" and p_type = '{$type}'";
			if($is_view) $where.=" and is_view = '{$is_view}'";

			$data['draw'] = request()->getGet('draw') ?? 1;
			$data['where'] = $where;
			$data['recordsTotal'] = $this->parameter->countAllResults();
			$data['recordsFiltered'] = $this->parameter->where($where)->countAllResults();
			$data['data'] = $this->parameter->select('id, caption_en,molecular_mass, code, default_unit, p_type, is_view, formula')
			->where($where)
			->orderBy('id', 'DESC')->findAll($length, $start);
			return response()->setJSON($data);
		}catch(Exception $e){
			return response()->setStatusCode(500)->setJSON($e->getMessage());
		}
	}


	public function saving()
	{
		try {
			$id = request()->getPost('id');
			$data['code'] = request()->getPost('code');
			$data['caption_id'] = request()->getPost('caption_id');
			$data['molecular_mass'] = request()->getPost('molecular_mass');
			$data['is_view'] = request()->getPost('is_view');
			$data['is_graph'] = request()->getPost('is_graph');
			$data['sensor_value_id'] = request()->getPost('sensor_value_id') * 1;
			$data['voltage1'] = request()->getPost('voltage1') * 1;
			$data['voltage2'] = request()->getPost('voltage2') * 1;
			$data['concentration1'] = request()->getPost('concentration1') * 1;
			$data['concentration2'] = request()->getPost('concentration2') * 1;
			$data['formula'] = request()->getPost('formula');
			$this->parameter->update($id, $data);
			$data['success'] = true;
			$data['message'] = 'Parameter berhasil diubah';
		} catch (Exception $e) {
			$data['success'] = false;
			$data['message'] = 'Error : ' . $e->getMessage();
		}
		return $this->response->setJSON($data);
	}
	public function detail()
	{
		try {
			$id = request()->getGet('id');
			$data['success'] = true;
			$data['data'] = @$this->parameter->find($id);
		} catch (Exception $e) {
			$data['success'] = false;
			$data['message'] = $e->getMessage();
		}
		return $this->response->setJSON($data);
	}
	public function voltage()
	{
		try {
			$id = request()->getGet('sensor_value_id');
			$data['success'] = true;
			$data['data'] = @$this->sensor_value->select('value,pin,sensor_reader_id')->find($id);
		} catch (Exception $e) {
			$data['success'] = false;
			$data['message'] = $e->getMessage();
		}
		return $this->response->setJSON($data);
	}
}
