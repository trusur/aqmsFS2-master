<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FormulaReferences extends Migration
{
	public function up()
	{
		$this->forge->dropTable('formula_references',true);
		$this->forge->addField([
			'id'			=> ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
			'parameter_id'	=> ['type' => 'INT', 'default' => 0],
			'min_value'		=> ['type' => 'DOUBLE', 'default' => 0],
			'max_value'		=> ['type' => 'DOUBLE', 'default' => 0],
			'formula'		=> ['type' => 'VARCHAR', 'constraint' => 255],
			'xtimestamp timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()'
		]);
		$this->forge->addKey('id', TRUE);
		$this->forge->addKey('parameter_id');
		$this->forge->createTable('formula_references', TRUE);


		$data = [
			['parameter_id' => '31', 'min_value' => '-9999999999', 'max_value' => '10.650969', 'formula' => '(0.43899185345258 * $x) + 0.32431137762404'],
			['parameter_id' => '31', 'min_value' => '10.650970', 'max_value' => '11.296878', 'formula' => '(7.5862197093084 * $x) - 75.700598537253'],
			['parameter_id' => '31', 'min_value' => '11.296879	', 'max_value' => '9999999', 'formula' => '(12.60501713521 * $x) - 132.29735336939'],
		];
		$this->db->table('formula_references')->insertBatch($data);
	}

	public function down()
	{
		$this->forge->dropTable('formula_references');
	}
}
