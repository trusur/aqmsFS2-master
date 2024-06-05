<?php

namespace App\Models;

use CodeIgniter\Model;

class m_sensor_reader extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'sensor_readers';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = false;
    protected $protectFields = false;
}
