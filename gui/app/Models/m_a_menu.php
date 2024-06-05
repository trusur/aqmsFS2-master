<?php

namespace App\Models;

use CodeIgniter\Model;

class m_a_menu extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'a_menu';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = false;
    protected $protectFields = false;
}
