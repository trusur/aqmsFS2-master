#!/bin/sh
while true
    do
        python drivers/pump_efs2.py
        python drivers/mainboard_efs2.py
        php gui/spark command:formula_measurement_logs
        # sleep 1s
    done