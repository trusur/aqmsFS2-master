<?php

namespace App\Controllers;

class Login extends BaseController
{

	public function __construct()
	{
		parent::__construct();
		$this->password = "qweasd";
	}

	public function index()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			return $this->login_action();
		}
		$data['__this'] = $this;
		$data['__modulename'] = 'Login'; /* Title */
		$data['__routename'] = 'login'; /* Route for check menu */
		$data['url_direction'] = $_GET["url_direction"]; /* Route for check menu */
		echo view("v_login", $data);
	}

	public function login_action()
	{
		$url_direction = $this->request->getPost('url_direction');
		$username = $this->request->getPost('username');
		$password = $this->request->getPost('password');
		if ($userlogin = @$this->users->where("email", $username)->findAll()[0]) {
			if (password_verify($password, $userlogin->password)) {
				$data['success'] = true;
				$data['message'] = "Login Success";
				$data["url_direction"] = $url_direction;
				$statusCode = 200;

				$logindata = [
					"loggedin" => true,
					"user_id" => $userlogin->id,
					"username" => $userlogin->email,
					"user" => $userlogin,
				];
				$this->session->set($logindata);
				$this->session->setFlashdata("flash_message", ["success", "Login Success"]);
			} else {
				$data['success'] = false;
				$data['message'] = 'Wrong Password';
				$statusCode = 401;
			}
		} else {
			$data['success'] = false;
			$data['message'] = 'Wrong Username';
			$statusCode = 401;
		}
		return $this->response->setJSON($data)->setStatusCode($statusCode);
	}

	public function logout()
	{
		$this->session->destroy();
		$this->session->setFlashdata("flash_message", ["success", "Logout Success"]);
		return redirect()->to(base_url() . '/login');
	}
}
