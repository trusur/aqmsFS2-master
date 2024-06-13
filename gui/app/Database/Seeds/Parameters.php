<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Parameters extends Seeder
{
	public function run()
	{
		$this->db->query("TRUNCATE TABLE parameters");
		$data = [
			['p_type' => 'gas', 'code' => 'no2', 'caption_id' => 'NO<sub>2</sub>', 'caption_en' => 'NO<sub>2</sub>',	'default_unit' => 'µg/m<sup>3</sup>', 'molecular_mass' => '46.01', 	'formula' => 'round(explode(";",$sensor[1][2])[1] ,2)', 'is_view' => '1', 'is_graph' => '1'],
			['p_type' => 'gas', 'code' => 'o3', 'caption_id' => 'O<sub>3</sub>', 'caption_en' => 'O<sub>3</sub>', 		'default_unit' => 'µg/m<sup>3</sup>', 'molecular_mass' => '48', 		'formula' => 'round(explode(";",$sensor[1][4])[1] ,2)', 'is_view' => '1', 'is_graph' => '1'],
			['p_type' => 'gas', 'code' => 'co', 'caption_id' => 'CO', 'caption_en' => 'CO', 							'default_unit' => 'µg/m<sup>3</sup>', 'molecular_mass' => '28.01', 	'formula' => 'round(explode(";",$sensor[1][5])[1] ,2)', 'is_view' => '1', 'is_graph' => '1'],
			['p_type' => 'gas', 'code' => 'so2', 'caption_id' => 'SO<sub>2</sub>', 'caption_en' => 'SO<sub>2</sub>', 	'default_unit' => 'µg/m<sup>3</sup>', 'molecular_mass' => '64.06', 	'formula' => 'round(explode(";",$sensor[1][3])[1] ,2)', 'is_view' => '1', 'is_graph' => '1'],
			['p_type' => 'gas', 'code' => 'hc', 'caption_id' => 'HC', 'caption_en' => 'HC', 							'default_unit' => 'µg/m<sup>3</sup>', 'molecular_mass' => '13.0186', 	'formula' => 'round(explode(";",$sensor[1][6])[1] ,2)', 'is_view' => '1', 'is_graph' => '1'],
			['p_type' => 'particulate', 'code' => 'pm25', 'caption_id' => 'PM2.5', 'caption_en' => 'PM2.5',					'default_unit' => 'µg/m<sup>3</sup>', 'molecular_mass' => '', 			'formula' => 'round(explode(";",$sensor[1][1])[1], 2)', 'is_view' => '1', 'is_graph' => '1'],
			['p_type' => 'particulate_flow', 'code' => 'pm25_flow', 'caption_id' => 'PM2.5 Flow', 'caption_en' => 'PM2.5 Flow',		'default_unit' => 'l/mnt', 'molecular_mass' => '', 			'formula' => 'round(explode(";",$sensor[1][16])[1], 2)', 'is_view' => '1', 'is_graph' => '1'],
			['p_type' => 'particulate', 'code' => 'pm10', 'caption_id' => 'PM10', 'caption_en' => 'PM10',						'default_unit' => 'µg/m<sup>3</sup>', 'molecular_mass' => '', 			'formula' => 'round(explode(";",$sensor[1][0])[1], 2)', 'is_view' => '1', 'is_graph' => '1'],
			['p_type' => 'particulate_flow', 'code' => 'pm10_flow', 'caption_id' => 'PM10 Flow', 'caption_en' => 'PM10 Flow',		'default_unit' => 'l/mnt', 'molecular_mass' => '', 			'formula' => 'round(explode(";",$sensor[1][16])[17], 2)', 'is_view' => '1', 'is_graph' => '1'],
			['p_type' => 'weather', 'code' => 'pressure', 'caption_id' => 'Tekanan', 'caption_en' => 'Barometer',			'default_unit' => 'MBar', 'molecular_mass' => '', 			'formula' => 'round((explode(";",$sensor[1][11])[1] * 33.8639),2)', 'is_view' => '1', 'is_graph' => '0'],
			['p_type' => 'weather', 'code' => 'wd', 'caption_id' => 'Arah angin', 'caption_en' => 'Wind Direction',		'default_unit' => '°', 'molecular_mass' => '', 				'formula' => 'explode(";",$sensor[1][10])[1]', 'is_view' => '1', 'is_graph' => '0'],
			['p_type' => 'weather', 'code' => 'ws', 'caption_id' => 'Kec. Angin', 'caption_en' => 'Wind Speed',			'default_unit' => 'Km/h', 'molecular_mass' => '', 			'formula' => 'explode(";",$sensor[1][9])[1]', 'is_view' => '1', 'is_graph' => '0'],
			['p_type' => 'weather', 'code' => 'temperature', 'caption_id' => 'Suhu', 'caption_en' => 'Temperature',		'default_unit' => '°C', 'molecular_mass' => '', 			'formula' => 'explode(";",$sensor[1][12])[1]', 'is_view' => '1', 'is_graph' => '0'],
			['p_type' => 'weather', 'code' => 'humidity', 'caption_id' => 'Kelembaban', 'caption_en' => 'Humidity',		'default_unit' => '%', 'molecular_mass' => '', 				'formula' => 'explode(";",$sensor[1][13])[1]', 'is_view' => '1', 'is_graph' => '0'],
			['p_type' => 'weather', 'code' => 'sr', 'caption_id' => 'Solar Radiasi', 'caption_en' => 'Solar Radiation',	'default_unit' => 'watt/m2', 'molecular_mass' => '', 		'formula' => 'explode(";",$sensor[1][14])[1]', 'is_view' => '1', 'is_graph' => '0'],
			['p_type' => 'weather', 'code' => 'rain_intensity', 'caption_id' => 'Curah Hujan', 'caption_en' => 'Rain Rate', 'default_unit' => 'mm/h', 'molecular_mass' => '', 			'formula' => 'explode(";",$sensor[1][15])[1]', 'is_view' => '1', 'is_graph' => '0'],
		];
		$this->db->table('parameters')->insertBatch($data);
	}
}
