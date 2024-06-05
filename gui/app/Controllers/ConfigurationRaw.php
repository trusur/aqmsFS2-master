<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\m_configuration;
use Exception;

class ConfigurationRaw extends BaseController
{
    protected $m_config;
    public function __construct(){
        parent::__construct();
        $this->m_config = new m_configuration();
    }
    public function index()
    {
        $data['__modulename'] = 'Configurations'; /* Title */
		$data['__routename'] = 'configuration'; /* Route for check menu */
		return view("configuration/v_raw", $data);
    }

    public function add(){
        try{
            $this->validate([
                'name' => 'required',
                'content' => 'required',
            ]);
            $data = [
                'name' => request()->getPost('name'),
                'content' => request()->getPost('content'),
            ];
            $isExist = $this->m_config->where('name', $data['name'])->countAllResults() > 0;
            if(!$isExist){
                $this->m_config->save($data);
                return response()->setJSON([
                    'success' => true,
                    'message' => 'Configuration added!'
                ]);
            }
            return response()->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Configuration already exist!'
            ]);
        }catch(Exception $e){
            log_message('error', "Error add new config: ".$e->getMessage());
            return response()->setStatusCode(500)->setJSON([
                'success' => true,
                'message' => 'Configuration added!'
            ]);
        }
    }

    public function datatable(){
        try{
            $length = request()->getGet('length') ?? 10;
            $start = request()->getGet('start') ?? 0;
            $search = request()->getGet('search')['value'] ?? "";

            $where = "1=1";
            if($search) $where.=" and ( lower(name) like '%".$search."%' or content like '%".$search."%' )";
            
            $data['draw'] = request()->getGet('draw') ?? 1;
            $data['recordsTotal'] = $this->m_config->countAllResults();
            $data['recordsFiltered'] = $this->m_config->where($where)->countAllResults();
            $data['data'] = $this->m_config->where($where)->orderBy("id","desc")->findAll($length, $start);
            return response()->setJSON($data);
        }catch(Exception $e){
            return response()->setJSON([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
