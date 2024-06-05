<?php

namespace App\Models;

use CodeIgniter\Model;

class m_serial_port extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'serial_ports';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = false;
    protected $protectFields = false;
}
