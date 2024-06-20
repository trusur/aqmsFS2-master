<?php

namespace App\Controllers\Calibration;

use App\Controllers\BaseController;
use App\Models\m_configuration;
use Exception;

class CalibrationConfiguration extends BaseController
{
    protected $configuration;
    public function __construct()
    {
        $this->configuration = new m_configuration();
    }
    public function index()
    {
        $data['__modulename'] = 'Calibration Configuration';
        $data['__routename'] = 'calibration';
        return view("calibrations/configuration/index",$data);
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
}
