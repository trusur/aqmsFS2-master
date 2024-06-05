<?php

namespace App\Models;

use CodeIgniter\Model;

class m_sensor_value extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'sensor_values';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = false;
    protected $protectFields = false;
}
