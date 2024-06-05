<?php

namespace App\Controllers;

class Language extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}
	public function index()
	{
		$supportLang = ['id', 'en'];
		$code = $this->request->uri->getSegment(2);
		$lang = in_array($code, $supportLang) ? $code : 'id';
		session()->remove('web_lang');
		session()->set('web_lang', $lang);
		return redirect()->back();
	}
}
