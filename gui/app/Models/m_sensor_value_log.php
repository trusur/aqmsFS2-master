<?php

namespace App\Models;

use CodeIgniter\Model;

class m_sensor_value_log extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'sensor_value_logs';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = false;
    protected $protectFields = false;
}
