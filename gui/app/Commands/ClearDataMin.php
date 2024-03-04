<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Exception;

class ClearDataMin extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'CodeIgniter';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'command:cleardatamin';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = '';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'command:cleardatamin';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        try{
            $Mmeasurement1min = new \App\Models\m_measurement_1min();
            $lastValue = $Mmeasurement1min->orderBy("id","desc")->first();
            if($lastValue->is_sent_cloud == 1){
                $this->db->table('measurement_1mins')->truncate();
            }else{
                CLI::error("Please send data to cloud first");
            }
        }catch(Exception $e){
            CLI::error($e->getMessage());
        }
    }
}
