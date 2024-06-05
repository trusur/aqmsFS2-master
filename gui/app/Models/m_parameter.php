<?php

namespace App\Models;

use CodeIgniter\Model;

class m_parameter extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'parameters';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = false;
    protected $protectFields = false;
}
