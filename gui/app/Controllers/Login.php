<?php

namespace App\Controllers;

class Login extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			return $this->login_action();
		}
		$data['__this'] = $this;
		$data['__modulename'] = 'Login'; /* Title */
		$data['__routename'] = 'login'; /* Route for check menu */
		$data['url_direction'] = @$_GET["url_direction"]; /* Route for check menu */
		echo view("v_login", $data);
	}

	public function login_action()
	{
		$url_direction = request()->getPost('url_direction');
		$username = request()->getPost('username');
		$password = (string) request()->getPost('password');
		if ($userlogin = $this->users->where("email", $username)->first()) {
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
		return response()->setJSON($data)->setStatusCode($statusCode);
	}

	public function logout()
	{
		$this->session->destroy();
		$this->session->setFlashdata("flash_message", ["success", "Logout Success"]);
		return redirect()->to(base_url());
	}

	public function status()
	{
		$data = [
			"loggedin" => $this->session->get("loggedin"),
			"user_id" => $this->session->get("user_id"),
			"username" => $this->session->get("username"),
			"user" => $this->session->get("user"),
		];
		return $this->response->setJSON($data)->setStatusCode(200);
	}
}
