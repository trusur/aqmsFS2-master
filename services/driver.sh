#!/bin/sh
while true
    do
        # Replace drivers with full path to drivers folder
        /usr/bin/python3  /home/mx/aqms-efs2/drivers/pump_efs2.py
        /usr/bin/python3  /home/mx/aqms-efs2/drivers/mainboard_efs2.py
    done