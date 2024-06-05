<?php

namespace App\Models;

use CodeIgniter\Model;

class m_formula_reference extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'formula_references';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = false;
    protected $protectFields = false;
}
