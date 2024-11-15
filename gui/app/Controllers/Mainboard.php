<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\m_motherboard;
use Exception;

class Mainboard extends BaseController
{
    protected $motherboards;
    public function __construct(){
        $this->motherboards = new m_motherboard();
    }
    public function index()
    {
        $data['__modulename'] = 'List Mainboard' ; /* Title */
		$data['__routename'] = 'mainboard'; /* Route for check menu */
        $data['mainboards'] = $this->motherboards->orderBy('is_priority','desc')->orderBy('is_enable','desc')->findAll();
        return view('mainboard/index', $data);
    }
    public function store(){
        try{
            $data = [
                'sensorname' => request()->getPost('sensorname'),
                'type' => request()->getPost('type'),
                'is_enable' => request()->getPost('is_enable'),
                'is_priority' => request()->getPost('is_priority'),
                'command' => request()->getPost('command'),
                'prefix_return' => request()->getPost('prefix_return'),
            ];
            $this->motherboards->insert($data);
            return redirect()->to("configuration/mainboard?success=1")->with('success', 'Mainboard has been added');
        }catch(Exception $e){
            return redirect()->to("configuration/mainboard?error=1")->with('error', $e->getMessage());
        }
    }
    public function delete($id){
        try{
            $this->motherboards->delete($id);
            return redirect()->to("configuration/mainboard?success=1")->with('success', 'Mainboard has been deleted');
        }catch(Exception $e){
            return redirect()->to("configuration/mainboard?error=1")->with('error', $e->getMessage());
        }
    }
    public function update($id){
        try{
            $data = [
                'sensorname' => request()->getPost('sensorname'),
                'type' => request()->getPost('type'),
                'is_enable' => request()->getPost('is_enable'),
                'is_priority' => request()->getPost('is_priority'),
                'command' => request()->getPost('command'),
                'prefix_return' => request()->getPost('prefix_return'),
            ];
            $this->motherboards->update($id, $data);
            return redirect()->to("configuration/mainboard?success=1")->with('success', 'Mainboard has been updated');
        }catch(Exception $e){
            return redirect()->to("configuration/mainboard?error=1")->with('error', $e->getMessage());
        }
    }
    public function show($id){
        try{
            $mainboard = $this->motherboards->find($id);
            return $this->response->setJSON(['success' => true, 'data' => $mainboard]);
        }catch(Exception $e){
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
