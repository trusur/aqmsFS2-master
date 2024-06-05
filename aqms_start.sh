#!/bin/bash
echo "Starting AQMS EFS-1 2023"
sleep 1s
clear
cd ~/aqms-efs1/ && python3 aqms_start.py
$SHELL