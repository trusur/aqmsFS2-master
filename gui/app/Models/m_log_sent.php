<?php

namespace App\Models;

use CodeIgniter\Model;

class m_log_sent extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'log_sent';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = false;
    protected $protectFields = false;
}
