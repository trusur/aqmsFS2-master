#!/bin/sh
while true
    do
        # Replace gui with full path to spark folder
        php gui/spark command:sentdata1sec # Sending 1sec data to Trusur Server
        php gui/spark command:sentdata1min # Sending 1min data to Trusur Server
        php gui/spark command:sentdata # Sending {interval}min data to Trusur Server
        # php gui/spark command:sentdata_klhk # Sending {interval}min data  to KLHK Server
        sleep 10 # Sleep every 30 second
    done