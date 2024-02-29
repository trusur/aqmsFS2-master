<?php

namespace App\Models;

use CodeIgniter\Model;

class m_measurement_1min extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'measurement_1mins';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = false;
    protected $protectFields = false;
}
