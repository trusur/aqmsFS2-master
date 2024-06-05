<?php

namespace App\Models;

use CodeIgniter\Model;

class m_a_user extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'a_users';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = false;
    protected $protectFields = false;
}
