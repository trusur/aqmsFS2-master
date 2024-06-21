<?php

namespace App\Models;

use CodeIgniter\Model;

class m_calibration_log extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'calibration_logs';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = false;
    protected $protectFields = false;
}
