<?php

namespace App\Models;

use CodeIgniter\Model;

class m_device_id extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'device_id';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = false;
    protected $protectFields = false;
}
