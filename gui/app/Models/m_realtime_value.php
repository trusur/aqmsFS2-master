<?php

namespace App\Models;

use CodeIgniter\Model;

class m_realtime_value extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'realtime_values';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = false;
    protected $protectFields = false;
}
