#!/bin/bash
echo "Reading devices... Please wait!!"
sleep 1s
cd ~/aqms-efs1/ && python3 aqms_start.py
$SHELL