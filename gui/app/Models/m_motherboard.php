<?php

namespace App\Models;

use CodeIgniter\Model;

class m_motherboard extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'motherboard';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = false;
    protected $protectFields = false;
}
