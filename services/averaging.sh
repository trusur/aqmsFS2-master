#!/bin/sh
while true
    do
        # Replace gui with full path to spark folder
        php gui/spark command:formula_measurement_logs
        php gui/spark command:avg1min
        php gui/spark command:avg30min
        sleep 1
    done