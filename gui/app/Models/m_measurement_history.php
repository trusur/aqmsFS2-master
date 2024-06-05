<?php

namespace App\Models;

use CodeIgniter\Model;

class m_measurement_history extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'measurement_histories';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = false;
    protected $protectFields = false;
}
