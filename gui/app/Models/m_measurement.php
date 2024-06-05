<?php

namespace App\Models;

use CodeIgniter\Model;

class m_measurement extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'measurements';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = false;
    protected $protectFields = false;
}
