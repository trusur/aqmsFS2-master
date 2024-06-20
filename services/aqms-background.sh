#!/bin/sh
while true
    do
        python drivers/mainboard_efs2.py
        python drivers/pump_efs2.py
        # sleep 1s
    done