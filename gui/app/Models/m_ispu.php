<?php

namespace App\Models;

use CodeIgniter\Model;

class m_ispu extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'ispu';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = false;
    protected $protectFields = false;
}
