<?php

namespace App\Controllers;

use App\Models\m_sensor_value;
use App\Models\m_configuration;
use App\Models\m_sensor_value_log;

class Rht extends BaseController
{

	protected $sensor_values;
	protected $configurations;
	protected $sensor_value_logs;
	public function __construct()
	{
		parent::__construct();
		$this->sensor_values = new m_sensor_value();
		$this->configurations = new m_configuration();
		$this->sensor_value_logs = new m_sensor_value_log();
	}

	public function index()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			return $this->saving_edit();
		}
		$this->sensor_value_logs->truncate();
		$data['__this'] = $this;
		$data['__modulename'] = 'RHT'; /* Title */
		$data['__routename'] = 'rht'; /* Route for check menu */
		$data["sensor_values"] = $this->sensor_values->orderBy('sensor_reader_id ASC, pin ASC')->findAll();
		$linechartcolors[0] = "#000000";
		$linechartcolors[1] = "#0000ff";
		$linechartcolors[2] = "#00ff00";
		$linechartcolors[3] = "#00ffff";
		$linechartcolors[4] = "#ff0000";
		$linechartcolors[5] = "#ff00ff";
		$linechartcolors[6] = "#ffff00";
		$linechartcolors[7] = "#888888";
		$data["linechartcolors"] = $linechartcolors;
		echo view("rht/v_index", $data);
	}


	public function sensor_values()
	{
		$analyzer = @$this->sensor_values->where("value LIKE '%FS2_ANALYZER%'")->findAll()[0]->value;
		$pump = @$this->sensor_values->where("value LIKE '%FS2_PUMP%'")->findAll()[0]->value;
		$psu = @$this->sensor_values->where("value LIKE '%FS2_PSU%'")->findAll()[0]->value;
		$is_motherboard = @$this->configuration->where(["name" => "is_motherboard"])->findAll()[0]->content;
		if ($is_motherboard == "1") {
			$membrasens_0 = @$this->sensor_values->where("value LIKE '%MEMBRASENS_PPM%'")->findAll()[0]->value;
			$membrasens_1 = @$this->sensor_values->where("value LIKE '%MEMBRASENS_TEMP%'")->findAll()[0]->value;
		} else {
			$membrasens_0 = @$this->sensor_values->where("value LIKE '%FS2_MEMBRASENS%'")->findAll()[0]->value;
			$membrasens_1 = @$this->sensor_values->where("value LIKE '%FS2_MEMBRASENS%'")->findAll()[1]->value;
		}
		$semeatechs = $this->sensor_values->where("value LIKE '%SEMEATECH%'")->findAll();
		$setSpan = @$this->configuration->where(["name" => "setSpan"])->findAll()[0]->content;

		try {
			$this->sensor_value_logs->save(["sensor_value_id " => 1, "value" => $membrasens_0]);
			$this->sensor_value_logs->save(["sensor_value_id " => 2, "value" => $membrasens_1]);
		} catch (Exception $e) {
		}

		$analyzers = explode(";", $analyzer);
		$num_analyzers = count(@$analyzers);
		$data["vacuum"] = round((-0.0009765625 * @$analyzers[$num_analyzers - 6]) + 1, 6);
		$data["temp_analyzer"] = @$analyzers[$num_analyzers - 5] * 1;
		$data["rh_analyzer"] = @$analyzers[$num_analyzers - 4] * 1;
		$data["temp_sensor"] = @$analyzers[$num_analyzers - 3] * 1;
		$data["rh_sensor"]  = @$analyzers[$num_analyzers - 2] * 1;
		$data["pressure"] = @round((0.001953125 * explode(";", $pump)[3]) + 0, 6);
		$data["temp_pump"] = @explode(";", $pump)[4] * 1;
		$data["rh_pump"] = @explode(";", $pump)[5] * 1;
		$data["temp_psu"] = @explode(";", $psu)[1] * 1;
		$data["rh_psu"] = @explode(";", $psu)[2] * 1;
		if ($is_motherboard == "1") {
			$data["con_membrasens_0_0"] = explode(";", $membrasens_0)[1] * 1;
			$data["con_membrasens_0_1"] = explode(";", $membrasens_0)[2] * 1;
			$data["con_membrasens_0_2"] = explode(";", $membrasens_0)[3] * 1;
			$data["con_membrasens_0_3"] = explode(";", $membrasens_0)[4] * 1;
			$data["con_membrasens_1_0"] = explode(";", $membrasens_0)[5] * 1;
			$data["con_membrasens_1_1"] = explode(";", $membrasens_0)[6] * 1;
			$data["con_membrasens_1_2"] = explode(";", $membrasens_0)[7] * 1;
			$data["con_membrasens_1_3"] = explode(";", $membrasens_0)[8] * 1;
			$data["temp_membrasens_0_0"] = @round(explode(";", $membrasens_1)[1] * 1, 1);
			$data["temp_membrasens_0_1"] = @round(explode(";", $membrasens_1)[2] * 1, 1);
			$data["temp_membrasens_0_2"] = @round(explode(";", $membrasens_1)[3] * 1, 1);
			$data["temp_membrasens_0_3"] = @round(explode(";", $membrasens_1)[4] * 1, 1);
			$data["temp_membrasens_1_0"] = @round(explode(";", $membrasens_1)[5] * 1, 1);
			$data["temp_membrasens_1_1"] = @round(explode(";", $membrasens_1)[6] * 1, 1);
			$data["temp_membrasens_1_2"] = @round(explode(";", $membrasens_1)[7] * 1, 1);
			$data["temp_membrasens_1_3"] = @round(explode(";", $membrasens_1)[8] * 1, 1);
		} else {
			$data["con_membrasens_0_0"] = explode(";", $membrasens_0)[1] * 1;
			$data["con_membrasens_0_1"] = explode(";", $membrasens_0)[2] * 1;
			$data["con_membrasens_0_2"] = explode(";", $membrasens_0)[3] * 1;
			$data["con_membrasens_0_3"] = explode(";", $membrasens_0)[4] * 1;
			$data["volt_membrasens_0_0"] = round(explode(";", $membrasens_0)[5] * 1, 2);
			$data["volt_membrasens_0_1"] = round(explode(";", $membrasens_0)[6] * 1, 2);
			$data["volt_membrasens_0_2"] = round(explode(";", $membrasens_0)[7] * 1, 2);
			$data["volt_membrasens_0_3"] = round(explode(";", $membrasens_0)[8] * 1, 2);
			$data["temp_membrasens_0_0"] = round(explode(";", $membrasens_0)[9] * 1, 1);
			$data["temp_membrasens_0_1"] = round(explode(";", $membrasens_0)[10] * 1, 1);
			$data["temp_membrasens_0_2"] = round(explode(";", $membrasens_0)[11] * 1, 1);
			$data["temp_membrasens_0_3"] = round(explode(";", $membrasens_0)[12] * 1, 1);
			$data["con_membrasens_1_0"] = explode(";", $membrasens_1)[1] * 1;
			$data["con_membrasens_1_1"] = explode(";", $membrasens_1)[2] * 1;
			$data["con_membrasens_1_2"] = explode(";", $membrasens_1)[3] * 1;
			$data["con_membrasens_1_3"] = explode(";", $membrasens_1)[4] * 1;
			$data["volt_membrasens_1_0"] = round(explode(";", $membrasens_1)[5] * 1, 2);
			$data["volt_membrasens_1_1"] = round(explode(";", $membrasens_1)[6] * 1, 2);
			$data["volt_membrasens_1_2"] = round(explode(";", $membrasens_1)[7] * 1, 2);
			$data["volt_membrasens_1_3"] = round(explode(";", $membrasens_1)[8] * 1, 2);
			$data["temp_membrasens_1_0"] = round(explode(";", $membrasens_1)[9] * 1, 1);
			$data["temp_membrasens_1_1"] = round(explode(";", $membrasens_1)[10] * 1, 1);
			$data["temp_membrasens_1_2"] = round(explode(";", $membrasens_1)[11] * 1, 1);
			$data["temp_membrasens_1_3"] = round(explode(";", $membrasens_1)[12] * 1, 1);
		}
		foreach ($semeatechs as $semeatech) {
			$data["con_semeatech"][$semeatech->sensor_reader_id] = explode(";", $semeatech->value)[4] * 1;
			$data["volt_semeatech"][$semeatech->sensor_reader_id] = explode(";", $semeatech->value)[2] * 1;
			$data["temp_semeatech"][$semeatech->sensor_reader_id] = explode(";", $semeatech->value)[5] * 1;
		}

		$data["setSpan"] = $setSpan;
		$data["sensor_values"] =  $this->sensor_values->findAll();

		echo json_encode($data);
	}

	public function savingSetSpan($board, $port, $span)
	{
		if ($board != "semeatech") {
			$is_motherboard = @$this->configuration->where(["name" => "is_motherboard"])->findAll()[0]->content;
			if ($is_motherboard == "1") {
				$sensor_reader_id = @$this->sensor_values->where("value LIKE '%MEMBRASENS_PPM%'")->findAll()[0]->sensor_reader_id;
				if ($board > 0) $port += 4;
			} else {
				$sensor_reader_id = @$this->sensor_values->where("value LIKE '%FS2_MEMBRASENS%'")->findAll()[$board]->sensor_reader_id;
			}

			if ($sensor_reader_id > 0) {
				$configuration_id = @$this->configurations->where('name', 'setSpan')->first()->id;
				if ($configuration_id > 0) {
					$this->configuration->set('content', $sensor_reader_id . ";" . $port . ";" . $span)->where('name', 'setSpan')->update();
				} else
					$this->configuration->save(["name" => "setSpan", "content" => $sensor_reader_id . ";" . $port . ";" . $span]);

				echo json_encode(["response" => "OK", "board" => $board, "sensor_reader_id" => $sensor_reader_id, "port" => $port, "span" => $span]);
			} else
				echo json_encode(["response" => "Error", "board" => $board, "port" => $port, "span" => $span]);
		} else {
			$configuration_id = @$this->configurations->where('name', 'setSpan')->first()->id;
			if ($configuration_id > 0)
				$this->configuration->set('content', $port . ";" . $span)->where('name', 'setSpan')->update();
			else
				$this->configuration->save(["name" => "setSpan", "content" => $port . ";" . $span]);

			echo json_encode(["response" => "OK", "board" => $board, "sensor_reader_id" => $port, "port" => $port, "span" => $span]);
		}
	}

	public function savingSetZero($board, $sensor_reader_id)
	{
		$configuration_id = @$this->configurations->where('name', 'is_zerocal')->first()->id;
		if ($configuration_id > 0)
			$this->configuration->set('content', $sensor_reader_id)->where('name', 'is_zerocal')->update();
		else
			$this->configuration->save(["name" => "is_zerocal", "content" => $sensor_reader_id]);

		echo json_encode(["response" => "OK", "board" => $board, "sensor_reader_id" => $sensor_reader_id]);
	}

	public function sensor_value_logs()
	{
		$is_motherboard = @$this->configuration->where(["name" => "is_motherboard"])->findAll()[0]->content;
		if ($is_motherboard == "1") {
			$sensor_value_logs0 = $this->sensor_value_logs->where("value LIKE 'MEMBRASENS_PPM%'")->orderBy('id', 'DESC')->limit(30)->find();
		} else {
			$sensor_value_logs0 = $this->sensor_value_logs->where("sensor_value_id", 1)->orderBy('id', 'DESC')->limit(30)->find();
			$sensor_value_logs1 = $this->sensor_value_logs->where("sensor_value_id", 2)->orderBy('id', 'DESC')->limit(30)->find();
		}
		$this->sensor_value_logs->where("id < " . @$sensor_value_logs0[29]->id)->delete();
		$this->sensor_value_logs->where("id < " . @$sensor_value_logs1[29]->id)->delete();
		foreach ($sensor_value_logs0 as $key => $sensor_value_log0) {
			$labels[$key] = substr($sensor_value_log0->xtimestamp, -8);
			if ($is_motherboard == "1") {
				$data0[0][$key] = explode(";", $sensor_value_log0->value)[1] * 1;
				$data0[1][$key] = explode(";", $sensor_value_log0->value)[2] * 1;
				$data0[2][$key] = explode(";", $sensor_value_log0->value)[3] * 1;
				$data0[3][$key] = explode(";", $sensor_value_log0->value)[4] * 1;
				$data1[0][$key] = explode(";", $sensor_value_log0->value)[5] * 1;
				$data1[1][$key] = explode(";", $sensor_value_log0->value)[6] * 1;
				$data1[2][$key] = explode(";", $sensor_value_log0->value)[7] * 1;
				$data1[3][$key] = explode(";", $sensor_value_log0->value)[8] * 1;
			} else {
				$data0[0][$key] = explode(";", $sensor_value_log0->value)[1] * 1;
				$data0[1][$key] = explode(";", $sensor_value_log0->value)[2] * 1;
				$data0[2][$key] = explode(";", $sensor_value_log0->value)[3] * 1;
				$data0[3][$key] = explode(";", $sensor_value_log0->value)[4] * 1;
				$data1[0][$key] = explode(";", $sensor_value_logs1[$key]->value)[1] * 1;
				$data1[1][$key] = explode(";", $sensor_value_logs1[$key]->value)[2] * 1;
				$data1[2][$key] = explode(";", $sensor_value_logs1[$key]->value)[3] * 1;
				$data1[3][$key] = explode(";", $sensor_value_logs1[$key]->value)[4] * 1;
			}
		}

		$datasets[0] = json_encode(["borderColor" => "#000000", "pointRadius" => false, "data" => json_encode($data0[0])]);
		$datasets[1] = json_encode(["borderColor" => "#0000ff", "pointRadius" => false, "data" => json_encode($data0[1])]);
		$datasets[2] = json_encode(["borderColor" => "#00ff00", "pointRadius" => false, "data" => json_encode($data0[2])]);
		$datasets[3] = json_encode(["borderColor" => "#00ffff", "pointRadius" => false, "data" => json_encode($data0[3])]);
		$datasets[4] = json_encode(["borderColor" => "#ff0000", "pointRadius" => false, "data" => json_encode($data1[0])]);
		$datasets[5] = json_encode(["borderColor" => "#ff00ff", "pointRadius" => false, "data" => json_encode($data1[1])]);
		$datasets[6] = json_encode(["borderColor" => "#ffff00", "pointRadius" => false, "data" => json_encode($data1[2])]);
		$datasets[7] = json_encode(["borderColor" => "#888888", "pointRadius" => false, "data" => json_encode($data1[3])]);
		$return = ["labels" => $labels, "datasets" => $datasets];
		echo json_encode($return);
	}
}
