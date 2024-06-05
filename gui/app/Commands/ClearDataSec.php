<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Exception;

class ClearDataSec extends BaseCommand
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
    protected $name = 'command:cleardatasec';

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
    protected $usage = 'command:cleardatasec';

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
            $MmeasurementLog = new \App\Models\m_measurement_log();
            $lastValue = $MmeasurementLog->orderBy("id","desc")->first();
            if($lastValue->is_sent_cloud == 1){
                $this->db->table('measurement_logs')->truncate();
            }else{
                CLI::error("Please send data to cloud first");
            }
        }catch(Exception $e){
            CLI::error($e->getMessage());
        }
    }
}
