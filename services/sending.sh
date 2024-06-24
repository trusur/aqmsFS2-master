#!/bin/sh
while true
    do
        # Replace gui with full path to spark folder
        php gui/spark command:senddata1sec # Sending 1sec data to Trusur Server
        php gui/spark command:senddata1min # Sending 1min data to Trusur Server
        php gui/spark command:senddata # Sending {interval}min data to Trusur Server
        php gui/spark command:senddata_klhk # Sending {interval}min data  to KLHK Server
        sleep 1 # Sleep every second
    done