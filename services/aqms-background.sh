#!/bin/sh
while true
    do
        # Replace drivers with full path to drivers folder
        python drivers/pump_efs2.py
        python drivers/mainboard_efs2.py
    done