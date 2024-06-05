<?php

namespace App\Controllers;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 *
 * @package CodeIgniter
 */

use CodeIgniter\Controller;
use App\Models\m_configuration;
use App\Models\m_a_user;

class BaseController extends Controller
{
	protected $users;
	protected $groups;
	protected $menus;
	protected $configuration;
	protected $session;
	protected $_form;

	public function __construct()
	{
		/* Setup language */
		$this->language = \Config\Services::language();
		$this->language->setlocale(session()->has('web_lang') ? session()->get('web_lang') : 'en');
		$this->configuration = new m_configuration();
		$this->users =  new m_a_user();
		// $this->groups =  new m_a_group();
		// $this->menus =  new m_a_menu();
		$this->session = \Config\Services::session();
		// if ($_SERVER["REQUEST_URI"] != "/" && $_SERVER["REQUEST_URI"] != "/login" && !isset($this->session->get("user")->group_id)) {
		// 	// echo "<script> window.location='" . base_url() . "/login'; </script>";
		// 	// exit();
		// }

		// if ($_SERVER["REQUEST_URI"] != "/" && $_SERVER["REQUEST_URI"] != "/login" && !$this->session->get("loggedin")) {
		// 	echo "<script> window.location='" . base_url() . "/login'; </script>";
		// 	exit();
		// }

		// $this->_form =  new A_form();
	}
	/**
	 * An array of helpers to be loaded automatically upon
	 * class instantiation. These helpers will be available
	 * to all other controllers that extend BaseController.
	 *
	 * @var array
	 */
	protected $helpers = [];

	protected $request;

	/**
	 * Constructor.
	 */
	public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
		// Do Not Edit This Line
		parent::initController($request, $response, $logger);

		//--------------------------------------------------------------------
		// Preload any models, libraries, etc, here.
		//--------------------------------------------------------------------
		// E.g.:
		// $this->session = \Config\Services::session();
	}

	public function common()
	{
		if ($this->session->get("loggedin")) {
			$__submenu = [];
			$grouplogin = @$this->groups->where("id", $this->session->get("user")->group_id)->where("is_deleted", 0)->findAll()[0];
			$menu_ids = substr("0," . @$grouplogin->menu_ids, 0, -1);
			if (@$grouplogin->id > 0)
				$__mainmenu = $this->menus->where("parent_id", 0)->where("is_deleted", 0)->where("id IN (" . $menu_ids . ")")->orderBy('seqno', 'asc')->findAll();
			else
				$__mainmenu = $this->menus->where("parent_id", 0)->where("is_deleted", 0)->orderBy('seqno', 'asc')->findAll();

			foreach ($__mainmenu as $mainmenu) {
				if (@$grouplogin->id > 0) {
					if ($_submenu = $this->menus->where("parent_id", $mainmenu->id)->where("is_deleted", 0)->where("id IN (" . $menu_ids . ")")->orderBy('seqno', 'asc')->findAll()) {
						$__submenu[$mainmenu->id] = $_submenu;
					}
				} else {
					if ($_submenu = $this->menus->where("parent_id", $mainmenu->id)->where("is_deleted", 0)->orderBy('seqno', 'asc')->findAll()) {
						$__submenu[$mainmenu->id] = $_submenu;
					}
				}
			}
		} else {
			$__mainmenu = $this->menus->where("id", 1)->orderBy('seqno', 'asc')->findAll();
			$__submenu = [];
		}
		$data["__session"] = $this->session;
		$data["__users"]["id"] = $this->session->get("user_id");
		$data["__users"]["name"] = $this->session->get("username");
		$data["__users"]["photo"] = @$this->users->where(["is_deleted" => 0, "id" => $this->session->get("user_id")])->findAll()[0]->photo;
		$data["__mainmenu"] = $__mainmenu;
		$data["__submenu"] = $__submenu;
		$data["__menu_ids"] = @$this->get_menu_ids(explode("/", $_SERVER["PATH_INFO"])[1]);
		$data["_form"] = $this->_form;
		return $data;
	}

	public function get_menu_ids($url)
	{
		$return = [];
		foreach (@$this->menus->where(["is_deleted" => 0, "url" => $url])->findAll() as $menus) {
			$return[] = $menus->id;
		}
		return $return;
	}

	public function privilege_check($menu_ids, $mode = "0", $return_url = "")
	{
		$allowed = false;
		$grouplogin = @$this->groups->where("id", $this->session->get("user")->group_id)->where("is_deleted", 0)->findAll()[0];
		$_menu_ids = explode(",", @$grouplogin->menu_ids);
		$_privileges = explode(",", @$grouplogin->privileges);
		$privileges = [];

		foreach ($_privileges as $key => $privilege) {
			$privileges[$_menu_ids[$key]] = $privilege;
		}

		if ($this->session->get("user")->group_id == 0) $allowed = true;

		foreach ($menu_ids as $menu_id) {
			if ($mode == "0") {
				if (isset($privileges[$menu_id])) $allowed = true;
			} else {
				if (@$privileges[$menu_id] & $mode) $allowed = true;
			}
		}

		if (!$allowed) {
			$this->session->setFlashdata("flash_message", ["error", "Sorry, you don`t have the privilege!"]);
			echo "<script> window.location = '" . base_url() . "/" . $return_url . "'; </script>";
			exit();
		}
	}

	public function numberToRoman($num)
	{
		$n = intval($num);
		$result = '';

		$lookup = array(
			'M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400,
			'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40,
			'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1
		);

		foreach ($lookup as $roman => $value) {
			$matches = intval($n / $value);
			$result .= str_repeat($roman, $matches);
			$n = $n % $value;
		}
		return $result;
	}

	public function namabulan($bulan)
	{
		$arr["01"] = "Januari";
		$arr["02"] = "Februari";
		$arr["03"] = "Maret";
		$arr["04"] = "April";
		$arr["05"] = "Mei";
		$arr["06"] = "Juni";
		$arr["07"] = "Juli";
		$arr["08"] = "Agustus";
		$arr["09"] = "September";
		$arr["10"] = "Oktober";
		$arr["11"] = "November";
		$arr["12"] = "Desember";
		return $arr[$bulan];
	}

	public function format_tanggal($tanggal, $mode = "d Fi Y")
	{
		if ($tanggal == "") return "";
		if ($tanggal == "0000-00-00") return "";
		if ($tanggal == "0000-00-00 00:00:00") return "";
		if ($mode == "d Fi Y")
			return date("d", strtotime($tanggal)) . " " . $this->namabulan(date("m", strtotime($tanggal))) . " " . date("Y", strtotime($tanggal));
		else return date("d", strtotime($tanggal)) . "-" . $this->namabulan(date("m", strtotime($tanggal))) . "-" . date("Y", strtotime($tanggal));
	}
	function number_to_words($number, $numdecimal = 0)
	{
		$number = number_format($number, $numdecimal, ".", "") * 1;

		$hyphen      = '-';
		$conjunction = ' and ';
		$separator   = ', ';
		$negative    = 'negative ';
		$decimal     = ' point ';
		$dictionary  = array(
			0                   => 'zero',
			1                   => 'one',
			2                   => 'two',
			3                   => 'three',
			4                   => 'four',
			5                   => 'five',
			6                   => 'six',
			7                   => 'seven',
			8                   => 'eight',
			9                   => 'nine',
			10                  => 'ten',
			11                  => 'eleven',
			12                  => 'twelve',
			13                  => 'thirteen',
			14                  => 'fourteen',
			15                  => 'fifteen',
			16                  => 'sixteen',
			17                  => 'seventeen',
			18                  => 'eighteen',
			19                  => 'nineteen',
			20                  => 'twenty',
			30                  => 'thirty',
			40                  => 'fourty',
			50                  => 'fifty',
			60                  => 'sixty',
			70                  => 'seventy',
			80                  => 'eighty',
			90                  => 'ninety',
			100                 => 'hundred',
			1000                => 'thousand',
			1000000             => 'million',
			1000000000          => 'billion',
			1000000000000       => 'trillion',
			1000000000000000    => 'quadrillion',
			1000000000000000000 => 'quintillion'
		);

		if (!is_numeric($number)) {
			return false;
		}

		if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
			// overflow
			trigger_error(
				'number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
				E_USER_WARNING
			);
			return false;
		}

		if ($number < 0) {
			return $negative . $this->number_to_words(abs($number));
		}

		$string = $fraction = null;

		if (strpos($number, '.') !== false) {
			list($number, $fraction) = explode('.', $number);
		}

		switch (true) {
			case $number < 21:
				$string = $dictionary[$number];
				break;
			case $number < 100:
				$tens   = ((int) ($number / 10)) * 10;
				$units  = $number % 10;
				$string = $dictionary[$tens];
				if ($units) {
					$string .= $hyphen . $dictionary[$units];
				}
				break;
			case $number < 1000:
				$hundreds  = $number / 100;
				$remainder = $number % 100;
				$string = $dictionary[$hundreds] . ' ' . $dictionary[100];
				if ($remainder) {
					$string .= $conjunction . $this->number_to_words($remainder);
				}
				break;
			default:
				$baseUnit = pow(1000, floor(log($number, 1000)));
				$numBaseUnits = (int) ($number / $baseUnit);
				$remainder = $number % $baseUnit;
				$string = $this->number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
				if ($remainder) {
					$string .= $remainder < 100 ? $conjunction : $separator;
					$string .= $this->number_to_words($remainder);
				}
				break;
		}

		if (null !== $fraction && is_numeric($fraction)) {
			//$string .= $decimal;
			$number = $fraction . substr("000000000000000000000000000000000000000000000", 0, $numdecimal - strlen($fraction));
			/* $words = array();
			foreach (str_split((string) $fraction) as $number) {
				$words[] = $dictionary[$number];
			}
			$string .= implode(' ', $words); */
			if (substr($number, 0, 1) == "0") {
				$numberX = $number;
				$_string = "";
				for ($xx = 0; $xx < strlen($numberX); $xx++) {
					$number = substr($numberX, $xx, 1);
					switch (true) {
						case $number < 21:
							$_string .= $dictionary[$number] . " ";
							break;
						case $number < 100:
							$tens   = ((int) ($number / 10)) * 10;
							$units  = $number % 10;
							$_string .= $dictionary[$tens] . " ";
							if ($units) {
								$_string .= $hyphen . $dictionary[$units];
							}
							break;
						case $number < 1000:
							$hundreds  = $number / 100;
							$remainder = $number % 100;
							$_string .= $dictionary[$hundreds] . ' ' . $dictionary[100] . " ";
							if ($remainder) {
								$_string .= $conjunction . $this->number_to_words($remainder);
							}
							break;
						default:
							$baseUnit = pow(1000, floor(log($number, 1000)));
							$numBaseUnits = (int) ($number / $baseUnit);
							$remainder = $number % $baseUnit;
							$_string .= $this->number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit] . " ";
							if ($remainder) {
								$_string .= $remainder < 100 ? $conjunction : $separator;
								$_string .= $this->number_to_words($remainder);
							}
							break;
					}
				}
			} else {
				switch (true) {
					case $number < 21:
						$_string .= $dictionary[$number] . " ";
						break;
					case $number < 100:
						$tens   = ((int) ($number / 10)) * 10;
						$units  = $number % 10;
						$_string .= $dictionary[$tens] . " ";
						if ($units) {
							$_string .= $hyphen . $dictionary[$units];
						}
						break;
					case $number < 1000:
						$hundreds  = $number / 100;
						$remainder = $number % 100;
						$_string .= $dictionary[$hundreds] . ' ' . $dictionary[100] . " ";
						if ($remainder) {
							$_string .= $conjunction . $this->number_to_words($remainder);
						}
						break;
					default:
						$baseUnit = pow(1000, floor(log($number, 1000)));
						$numBaseUnits = (int) ($number / $baseUnit);
						$remainder = $number % $baseUnit;
						$_string .= $this->number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit] . " ";
						if ($remainder) {
							$_string .= $remainder < 100 ? $conjunction : $separator;
							$_string .= $this->number_to_words($remainder);
						}
						break;
				}
			}
			$string .= $decimal . " " . $_string;
		}

		return $string;
	}

	// public function angka_kalimatX($number, $numdecimal = 0)
	// {
	// 	$number = str_replace(",", "", $number);
	// 	$number = number_format(($number * 1), $numdecimal, ".", "");

	// 	$hyphen      = ' ';
	// 	$conjunction = ' ';
	// 	$separator   = ' ';
	// 	$negative    = 'min ';
	// 	$decimal     = ' koma ';
	// 	$dictionary  = array(
	// 		0                   => 'nol',
	// 		1                   => 'satu',
	// 		2                   => 'dua',
	// 		3                   => 'tiga',
	// 		4                   => 'empat',
	// 		5                   => 'lima',
	// 		6                   => 'enam',
	// 		7                   => 'tujuh',
	// 		8                   => 'delapan',
	// 		9                   => 'sembilan',
	// 		10                  => 'sepuluh',
	// 		11                  => 'sebelas',
	// 		12                  => 'dua belas',
	// 		13                  => 'tiga belas',
	// 		14                  => 'empat belas',
	// 		15                  => 'lima belas',
	// 		16                  => 'enam belas',
	// 		17                  => 'tujuh belas',
	// 		18                  => 'delapan belas',
	// 		19                  => 'sembilan belas',
	// 		20                  => 'dua puluh',
	// 		30                  => 'tiga puluh',
	// 		40                  => 'empat puluh',
	// 		50                  => 'lima puluh',
	// 		60                  => 'enam puluh',
	// 		70                  => 'tujuh puluh',
	// 		80                  => 'delapan puluh',
	// 		90                  => 'sembilan puluh',
	// 		100                 => 'ratus',
	// 		1000                => 'ribu',
	// 		1000000             => 'juta',
	// 		1000000000          => 'miliyar',
	// 		1000000000000       => 'triliun',
	// 		1000000000000000    => 'quatriliun',
	// 		1000000000000000000 => 'quintriliun'
	// 	);

	// 	if (!is_numeric($number)) {
	// 		return false;
	// 	}

	// 	if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
	// 		// overflow
	// 		trigger_error(
	// 			'number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
	// 			E_USER_WARNING
	// 		);
	// 		return false;
	// 	}

	// 	if ($number < 0) {
	// 		return $negative . $this->angka_kalimatX(abs($number));
	// 	}

	// 	$string = $fraction = null;

	// 	if (strpos($number, '.') !== false) {
	// 		list($number, $fraction) = explode('.', $number);
	// 	}

	// 	switch (true) {
	// 		case $number < 21:
	// 			$string = $dictionary[$number];
	// 			break;
	// 		case $number < 100:
	// 			$tens   = ((int) ($number / 10)) * 10;
	// 			$units  = $number % 10;
	// 			$string = $dictionary[$tens];
	// 			if ($units) {
	// 				$string .= $hyphen . $dictionary[$units];
	// 			}
	// 			break;
	// 		case $number < 1000:
	// 			$hundreds  = $number / 100;
	// 			$remainder = $number % 100;
	// 			$string = $dictionary[$hundreds] . ' ' . $dictionary[100];
	// 			if ($remainder) {
	// 				$string .= $conjunction . $this->angka_kalimatX($remainder);
	// 			}
	// 			break;
	// 		default:
	// 			$baseUnit = pow(1000, floor(log($number, 1000)));
	// 			$numBaseUnits = (int) ($number / $baseUnit);
	// 			$remainder = $number % $baseUnit;
	// 			$string = $this->angka_kalimatX($numBaseUnits) . ' ' . $dictionary[$baseUnit];
	// 			if ($remainder) {
	// 				$string .= $remainder < 100 ? $conjunction : $separator;
	// 				$string .= $this->angka_kalimatX($remainder);
	// 			}
	// 			break;
	// 	}

	// 	if (null !== $fraction && is_numeric($fraction)) {
	// 		$string .= $decimal;
	// 		$words = array();
	// 		foreach (str_split((string) $fraction) as $number) {
	// 			$words[] = $dictionary[$number];
	// 		}
	// 		$string .= implode(' ', $words);
	// 	}
	// 	$arr1 = array("seratus", "seribu", "satu juta", "satu miliyar", "satu triliun", "satu quatriliun", "satu quintriliun");
	// 	$arr2 = array("seratus", "seribu", "satu juta", "satu miliyar", "satu triliun", "satu quatriliun", "satu quintriliun");
	// 	return str_replace($arr1, $arr2, $string);
	// }

	//ANGKA KALIMAT NEW
	function angka_kalimat($number, $numdecimal = 0)
	{
		$number = number_format(($number * 1), $numdecimal, ".", "");

		$hyphen      = ' ';
		$conjunction = ' ';
		$separator   = ' ';
		$negative    = 'min ';
		$decimal     = ' koma ';
		$dictionary  = array(
			0                   => 'nol',
			1                   => 'satu',
			2                   => 'dua',
			3                   => 'tiga',
			4                   => 'empat',
			5                   => 'lima',
			6                   => 'enam',
			7                   => 'tujuh',
			8                   => 'delapan',
			9                   => 'sembilan',
			10                  => 'sepuluh',
			11                  => 'sebelas',
			12                  => 'dua belas',
			13                  => 'tiga belas',
			14                  => 'empat belas',
			15                  => 'lima belas',
			16                  => 'enam belas',
			17                  => 'tujuh belas',
			18                  => 'delapan belas',
			19                  => 'sembilan belas',
			20                  => 'dua puluh',
			30                  => 'tiga puluh',
			40                  => 'empat puluh',
			50                  => 'lima puluh',
			60                  => 'enam puluh',
			70                  => 'tujuh puluh',
			80                  => 'delapan puluh',
			90                  => 'sembilan puluh',
			100                 => 'ratus',
			1000                => 'ribu',
			1000000             => 'juta',
			1000000000          => 'miliyar',
			1000000000000       => 'triliun',
			1000000000000000    => 'quatriliun',
			1000000000000000000 => 'quintriliun'
		);

		if (!is_numeric($number)) {
			return false;
		}

		if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
			// overflow
			trigger_error(
				'number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
				E_USER_WARNING
			);
			return false;
		}

		if ($number < 0) {
			return $negative . $this->angka_kalimat(abs($number));
		}

		$string = $fraction = null;

		if (strpos($number, '.') !== false) {
			list($number, $fraction) = explode('.', $number);
		}

		switch (true) {
			case $number < 21:
				$string = $dictionary[$number];
				break;
			case $number < 100:
				$tens   = ((int) ($number / 10)) * 10;
				$units  = $number % 10;
				$string = $dictionary[$tens];
				if ($units) {
					$string .= $hyphen . $dictionary[$units];
				}
				break;
			case $number < 1000:
				$hundreds  = $number / 100;
				$remainder = $number % 100;
				$string = $dictionary[$hundreds] . ' ' . $dictionary[100];
				if ($remainder) {
					$string .= $conjunction . $this->angka_kalimat($remainder);
				}
				break;
			default:
				$baseUnit = pow(1000, floor(log($number, 1000)));
				$numBaseUnits = (int) ($number / $baseUnit);
				$remainder = $number % $baseUnit;
				$string = $this->angka_kalimat($numBaseUnits) . ' ' . $dictionary[$baseUnit];
				if ($remainder) {
					$string .= $remainder < 100 ? $conjunction : $separator;
					$string .= $this->angka_kalimat($remainder);
				}
				break;
		}

		if (null !== $fraction && is_numeric($fraction)) {
			$string .= $decimal;
			$words = array();
			foreach (str_split((string) $fraction) as $number) {
				$words[] = $dictionary[$number];
			}
			$string .= implode(' ', $words);
		}
		$arr1 = array("satu ratus", "satu ribu", "satu juta", "satu miliyar", "satu triliun", "satu quatriliun", "satu quintriliun");
		$arr2 = array("seratus", "seribu", "sejuta", "semiliyar", "setriliun", "sequatriliun", "sequintriliun");
		$data_1 = str_replace($arr1, $arr2, $string);

		$arr3 = array("sejuta", "semiliyar", "puluh seribu", "ratus seribu", "ratus sejuta", "ratus semiliyar", "ratus setriliun", "ratus sequatriliun", "ratus sequintriliun");
		$arr4 = array("satu juta", "satu miliyar", "puluh satu ribu", "ratus satu ribu", "ratus satu juta", "ratus satu miliyar", "ratus satu triliun", "ratus satu quatriliun", "ratus satu quintriliun");
		return str_replace($arr3, $arr4, $data_1);
	}

	public function format_amount($amount, $decimalnum = 0, $currency_id = "IDR", $isexport = false)
	{
		$isnegative = false;
		if ($isexport) return $amount;
		if ($amount < 0) {
			$amount  *= -1;
			$isnegative = true;
		}
		if ($currency_id == "IDR") $return = number_format($amount, $decimalnum, ",", ".");
		else $return = number_format($amount, $decimalnum);
		if ($isnegative) $return = "(" . $return . ")";
		return $return;
	}

	public function numberpad($number, $pad)
	{
		return sprintf("%0" . $pad . "d", $number);
	}

	public function letter_no_template($type, $sales_category_id = 1)
	{
		$division_id = $this->users->where("is_deleted", 0)->find([$this->session->get("user_id")])[0]->division_id;
		$div = @$this->divisions->where("is_deleted", 0)->find([$division_id])[0]->initial;
		$letter_no_variables = ["{seqno}", "{month}", "{month_roman}", "{year}", "{div}"];
		$letter_no_values = ["%", date("m"), $this->numberToRoman(date("m")), date("Y"), $div];
		return str_replace($letter_no_variables, $letter_no_values, @$this->sales_categories->where("is_deleted", 0)->find([$sales_category_id])[0]->$type);
	}

	public function supplier_invoice_no_template()
	{
		return "INV/{month}/{year}/{seqno}";
	}

	public function so_no_template()
	{
		return "{seqno}/PO-{div}/{month}/{year}";
	}

	public function invoice_no_template()
	{
		return "{seqno}/KL/TUT/{year}";
	}

	public function amountformat($value)
	{
		return number_format($value, 2, ",", ".");
	}

	public function created_values()
	{
		$data["created_at"] = date("Y-m-d H:i:s");
		$data["created_by"] = $this->session->get("username");
		$data["created_ip"] = $_SERVER["REMOTE_ADDR"];
		return $data;
	}

	public function updated_values()
	{
		$data["updated_at"] = date("Y-m-d H:i:s");
		$data["updated_by"] = $this->session->get("username");
		$data["updated_ip"] = $_SERVER["REMOTE_ADDR"];
		return $data;
	}

	public function deleted_values()
	{
		$data["is_deleted"] = 1;
		$data["deleted_at"] = date("Y-m-d H:i:s");
		$data["deleted_by"] = $this->session->get("username");
		$data["deleted_ip"] = $_SERVER["REMOTE_ADDR"];
		return $data;
	}

	public function resizeImage($filename)
	{
		list($width, $height) = getimagesize($filename);
		$percent = 512 / $width;
		$newwidth = $width * $percent;
		$newheight = $height * $percent;

		$thumb = imagecreatetruecolor($newwidth, $newheight);
		$source = imagecreatefromjpeg($filename);

		imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
		imagejpeg($thumb, $filename, 100);
		return 1;
	}

	public function insertTextImg($source, $dest, $text)
	{
		ob_start();
		header('Content-type: image/jpeg');
		$image = imagecreatefromjpeg($source);
		$color = imagecolorallocate($image, 255, 255, 255);
		$color2 = imagecolorallocate($image, 0, 0, 0);
		$font = str_replace("\\", "/", $_SERVER["DOCUMENT_ROOT"]) . "/dist/font/TruenoLt.otf";
		list($width, $height, $image_type) = getimagesize($source);
		$x = 10;
		$y = $height - 75;
		$arrtext = explode("<br>", $text);
		foreach ($arrtext as $text) {
			imagettftext($image, 25, 0, $x + 1, $y + 2, $color2, $font, $text);
			imagettftext($image, 25, 0, $x, $y, $color, $font, $text);
			$y += 34;
		}
		imagejpeg($image);
		$return = ob_get_contents();
		ob_clean();
		$fp = fopen($dest, "w");
		fwrite($fp, $return);
		fclose($fp);
	}

	public function stand_deviation($arr)
	{
		$n = count($arr);
		if ($n === 0) {
			trigger_error("The array has zero elements", E_USER_WARNING);
			return false;
		}
		if ($n === 1) {
			trigger_error("The array has only 1 element", E_USER_WARNING);
			return false;
		}
		$mean = array_sum($arr) / $n;
		$carry = 0.0;
		foreach ($arr as $val) {
			$d = ((float) $val) - $mean;
			$carry += $d * $d;
		};
		--$n;
		return sqrt($carry / $n);
	}

	public function match($references, $value)
	{
		$diff = 99999999999999999999;
		$result = -1;
		foreach ($references as $key => $reference) {
			$currdiff = $reference - $value;
			if ($currdiff < 0) $currdiff = $currdiff * -1;
			if ($diff > $currdiff) {
				$diff = $currdiff;
				$result = $key;
			}
		}
		return $result;
	}

	public function forecast($x, $arr_x, $arr_y)
	{
		$x_sum = array_sum($arr_x);
		$y_sum = array_sum($arr_y);
		$xx_sum = 0;
		$xy_sum = 0;
		for ($i = 0; $i < count($arr_x); $i++) {
			$xy_sum += ($arr_x[$i] * $arr_y[$i]);
			$xx_sum += ($arr_x[$i] * $arr_x[$i]);
		}
		$m = ((count($arr_x) * $xy_sum) - ($x_sum * $y_sum)) / ((count($arr_x) * $xx_sum) - ($x_sum * $x_sum));
		$b = ($y_sum - ($m * $x_sum)) / count($arr_x);
		return ($x * $m) + $b;
	}

	public function forecast_correction($x, $arr_x, $arr_y)
	{
		$match = $this->match($arr_x, $x);
		return @$this->forecast($x, [$arr_x[@$match], $arr_x[@$match + 1]], [$arr_y[@$match], $arr_y[@$match + 1]]);
	}

	public function calc_ci($mode, $intrument_type_id = 0, $item_type_id = 0)
	{
		$value_ci = @$this->constant_values->where(["is_deleted" => 0, "name" => $mode . "_ci"])->findAll()[0]->value;
		if ($intrument_type_id > 0 && $item_type_id > 0) $_value_ci = @$this->calibration_constant_values->where(["is_deleted" => 0, "intrument_type_id" => $intrument_type_id, "item_type_id" => $item_type_id, "name" => $mode . "_ci"])->findAll()[0]->value;
		if (@$_value_ci != "") $value_ci = $_value_ci;
		$ci = 0;
		eval("\$ci = $value_ci;");
		return $ci;
	}

	public function calc_pembagi($mode, $intrument_type_id = 0, $item_type_id = 0)
	{
		$value_pembagi = @$this->constant_values->where(["is_deleted" => 0, "name" => $mode . "_pembagi"])->findAll()[0]->value;
		if ($intrument_type_id > 0 && $item_type_id > 0) $_value_pembagi = @$this->calibration_constant_values->where(["is_deleted" => 0, "intrument_type_id" => $intrument_type_id, "item_type_id" => $item_type_id, "name" => $mode . "_pembagi"])->findAll()[0]->value;
		if (@$_value_pembagi != "") $value_pembagi = $_value_pembagi;
		$pembagi = 0;
		eval("\$pembagi = $value_pembagi;");
		return $pembagi;
	}

	public function calc_repitalisasi($stdev, $intrument_type_id = 0, $item_type_id = 0)
	{
		$ci = $this->calc_ci("repitalisasi", $intrument_type_id, $item_type_id);
		$pembagi = $this->calc_pembagi("repitalisasi", $intrument_type_id, $item_type_id);
		return $stdev * $ci / $pembagi;
	}

	public function calc_gasstd($concentration, $accuracy, $intrument_type_id = 0, $item_type_id = 0)
	{
		$ci = $this->calc_ci("gas_std", $intrument_type_id, $item_type_id);
		$pembagi = $this->calc_pembagi("gas_std", $intrument_type_id, $item_type_id);
		return ($concentration * $accuracy * $ci / $pembagi) / 100;
	}

	public function calc_flowmeter($concentration, $accuracy, $flow, $intrument_type_id = 0, $item_type_id = 0)
	{
		$flow_u95 = @$this->constant_values->where(["is_deleted" => 0, "name" => "flow_u95"])->findAll()[0]->value * 1;
		$ci = $concentration * $accuracy / $flow;
		$pembagi = $this->calc_pembagi("flowmeter", $intrument_type_id, $item_type_id);
		return ($flow_u95 * $ci / $pembagi) / 100;
	}

	public function calc_resolusi($resolusi_uut, $intrument_type_id = 0, $item_type_id = 0)
	{
		$ci = $this->calc_ci("resolusi", $intrument_type_id, $item_type_id);
		$pembagi = $this->calc_pembagi("resolusi", $intrument_type_id, $item_type_id);
		return (($resolusi_uut / 2) * $ci) / $pembagi;
	}

	public function calc_unc($repitalisasi, $gas_std, $flowmeter, $resolusi)
	{
		$cakupan = @$this->constant_values->where(["is_deleted" => 0, "name" => "cakupan"])->findAll()[0]->value * 1;
		return sqrt(pow($repitalisasi, 2) + pow($gas_std, 2) + pow($flowmeter, 2) + pow($resolusi, 2)) * $cakupan;
	}

	public function calc_cmc($intrument_type_id, $item_type_id, $item_scope_id)
	{
		$cmc_values = @$this->cmc_values->where(["is_deleted" => 0, "intrument_type_id" => $intrument_type_id, "item_type_id" => $item_type_id])->findAll();
		if (count($cmc_values) > 0) {
			if ($cmc_values[0]->item_scope_id == 0) return 0;
		}

		$cmc_values = @$this->cmc_values->where(["is_deleted" => 0, "intrument_type_id" => $intrument_type_id, "item_type_id" => $item_type_id, "item_scope_id" => $item_scope_id])->findAll();
		if (count($cmc_values) > 0) {
			return $cmc_values[0]->value;
		}

		return @$this->cmc_values->where(["is_deleted" => 0, "intrument_type_id" => "0", "item_type_id" => $item_type_id, "item_scope_id" => $item_scope_id])->findAll()[0]->value;
	}

	public function exec_calibration_form_calculation($calibration_form_id, $calibration_form_detail_id, $stdev, $concentration, $accuracy, $resolution_uut, $item_type_id, $flow)
	{

		$instrument_process_id = @$this->calibration_forms->where(["is_deleted" => 0, "id" => $calibration_form_id])->findAll()[0]->instrument_process_id;
		$item_scope_id = @$this->calibration_form_details->where(["is_deleted" => 0, "id" => $calibration_form_detail_id])->findAll()[0]->scope_id;
		$intrument_type_id = @$this->request_reviews->where(["is_deleted" => 0, "id" => $instrument_process_id])->findAll()[0]->intrument_type_id;

		$repitalisasi = @$this->calc_repitalisasi($stdev, $intrument_type_id, $item_type_id) * 1;
		$gasstd = @$this->calc_gasstd($concentration, $accuracy, $intrument_type_id, $item_type_id) * 1;
		$flowmeter = @$this->calc_flowmeter($concentration, $accuracy, $flow, $intrument_type_id, $item_type_id) * 1;
		$resolusi = @$this->calc_resolusi($resolution_uut, $intrument_type_id, $item_type_id) * 1;
		$unc = @$this->calc_unc($repitalisasi, $gasstd, $flowmeter, $resolusi) * 1;
		$cmc = @$this->calc_cmc($intrument_type_id, $item_type_id, $item_scope_id) * 1;
		if ($unc < $cmc) $ufinal = $cmc;
		else $ufinal = $unc;
		$data = [
			"calibration_form_id"   => $calibration_form_id,
			"calibration_form_detail_id"   => $calibration_form_detail_id,
			"repitalisasi"   => $repitalisasi,
			"gasstd"   => $gasstd,
			"flowmeter"   => $flowmeter,
			"resolusi"   => $resolusi,
			"unc"   => $unc,
			"ufinal"   => $ufinal,
		];

		$data = $data + $this->created_values() + $this->updated_values();
		$this->calibration_form_calculations->save($data);
	}

	public function to_double($val = 0)
	{
		return (float) str_replace(',', '', $val);
	}

	public function get_management_level($username)
	{
		$user_id = @$this->users->where(["is_deleted" => 0, "email" => $username])->findALL()[0]->id;
		$job_title_id = @$this->employees->where(["is_deleted" => 0, "user_id" => $user_id])->findALL()[0]->job_title_id;
		if ($level = @$this->job_titles->where(["is_deleted" => 0, "id" => $job_title_id])->findALL()[0]->management_level)
			return ["name" => $level, "value" => $this->management_level($level)];
		else
			return null;
	}

	public function management_level($level)
	{
		if (strtolower($level) == "low") return "1";
		if (strtolower($level) == "middle") return "2";
		if (strtolower($level) == "top") return "3";
	}

	public function lower_management_level_users($username)
	{
		$management_level = $this->get_management_level($username);
		if ($management_level["name"] == "low" || @$management_level["name"] == "") return "'nobody'";
		if ($management_level["name"] == "middle") $job_title_ids = array_column(@$this->job_titles->where(["is_deleted" => 0])->whereIn('management_level', ['low'])->findALL(), "id");
		if ($management_level["name"] == "top") $job_title_ids = array_column(@$this->job_titles->where(["is_deleted" => 0])->whereIn('management_level', ['low', 'middle'])->findALL(), "id");
		$user_ids = array_column(@$this->employees->where(["is_deleted" => 0])->whereIn('job_title_id', $job_title_ids)->findALL(), "user_id");
		return "'" . implode("','", array_column(@$this->users->where(["is_deleted" => 0])->whereIn('id', $user_ids)->findALL(), "email")) . "'";
	}

	public function finance_users()
	{
		$job_title_ids = array_column(@$this->job_titles->where(["is_deleted" => 0])->whereIn('id', [16, 26])->findALL(), "id");
		$user_ids = array_column(@$this->employees->where(["is_deleted" => 0])->whereIn('job_title_id', $job_title_ids)->findALL(), "user_id");
		return array_column(@$this->users->where(["is_deleted" => 0])->whereIn('id', $user_ids)->findALL(), "email");
	}

	public function hc_users()
	{
		$job_title_ids = array_column(@$this->job_titles->where(["is_deleted" => 0])->whereIn('id', [13, 21])->findALL(), "id");
		$user_ids = array_column(@$this->employees->where(["is_deleted" => 0])->whereIn('job_title_id', $job_title_ids)->findALL(), "user_id");
		return array_column(@$this->users->where(["is_deleted" => 0])->whereIn('id', $user_ids)->findALL(), "email");
	}

	public function add_notifications($users, $message, $mode = "")
	{
		if ($mode == "") $icon = "far fa-bell";
		if ($mode == "info") $icon = "fa fa-info-circle";
		if ($mode == "birthday") $icon = "fa fa-birthday-cake";
		if ($mode == "customer") $icon = "far fa-address-book";
		if ($mode == "item") $icon = "fas fa-barcode";
		if ($mode == "calls") $icon = "fas fa-phone";
		if ($mode == "quotation") $icon = "fas fa-file-invoice-dollar";
		if ($mode == "so") $icon = "fas fa-envelope-open-text";
		if ($mode == "btr") $icon = "fa fa-plane";
		if ($mode == "request_review") $icon = "fa fa-flask";
		if ($mode == "ticket") $icon = "fa fa-clipboard-list";
		if ($mode == "ticket_followups") $icon = "fa fa-clipboard-check";
		if ($mode == "lab") $icon = "fas fa-flask";
		if ($mode == "customer_followups") $icon = "fas fa-phone";
		if ($mode == "overtimes") $icon = "fas fa-business-time";
		if ($mode == "leave_early_forms") $icon = "fas fa-user-clock";

		foreach ($users as $user) {
			$notification = [
				"user_id" => $user->id,
				"icon" => $icon,
				"message" => $message,
				"status" => 0,
				"created_at" => date("Y-m-d H:i:s"),
			];
			$this->notifications->save($notification);
		}
	}

	//AQMS FS2
	public function findConfig($name)
	{
		return $this->configuration->where("name LIKE '" . $name . "'")->find()[0]->content;
	}
}
