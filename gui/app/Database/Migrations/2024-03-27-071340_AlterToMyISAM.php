<?php

namespace App\Database\Migrations;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\Migration;
use Exception;

class AlterToMyISAM extends Migration
{
    public function up()
    {
        $tables=[
            "configurations",
            "measurements",
            "measurement_logs",
            "measurement_1mins",
            "realtime_values",
            "sensor_value_logs",
            "log_sent",
            "parameters",
            "motherboard",
        ];
        $db = db_connect();
        foreach ($tables as $table) {
            try{
                $db->query("ALTER TABLE {$table} ENGINE='MyISAM';");
            }catch(Exception $e){
                CLI::write($e->getMessage(),"red");
            }
        }
    }

    public function down()
    {
        $tables=[
            "configurations",
            "measurements",
            "measurement_logs",
            "measurement_1mins",
            "realtime_values",
            "sensor_value_logs",
            "log_sent",
            "parameters",
            "motherboard",
        ];
        $db = db_connect();
        foreach ($tables as $table) {
            try{
                $db->query("ALTER TABLE {$table} ENGINE='InnoDB';");
            }catch(Exception $e){
                CLI::write($e->getMessage(),"red");
            }
        }
    }
}
