<?php

namespace App\Models;

use CodeIgniter\Model;

class m_calibration extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'calibrations';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = false;
    protected $protectFields = false;
}
