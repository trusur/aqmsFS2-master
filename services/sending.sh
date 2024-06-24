#!/bin/sh
while true
    do
        # Replace gui with full path to spark folder
        /usr/bin/php  /home/mx/aqms-efs2/gui/spark command:sentdata # Sending {interval}min data to Trusur Server
        /usr/bin/php  /home/mx/aqms-efs2/gui/spark command:sentdata1min # Sending 1min data to Trusur Server
        /usr/bin/php  /home/mx/aqms-efs2/gui/spark command:sentdata1sec # Sending 1sec data to Trusur Server
        # /usr/bin/php  /home/mx/aqms-efs2/gui/spark command:sentdata_klhk # Sending {interval}min data  to KLHK Server
        sleep 10 # Sleep every 30 second
    done