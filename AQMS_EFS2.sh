#!/bin/bash
echo "Waiting 5 minutes before running AQMS"
date
echo "Will start AQMS at:"
date -d "+5 minutes"
sleep 5m
echo "5 minutes have passed.Starting ISPUTEK EFS2"
date
clear
cd ~/aqms-efs2/ && /usr/bin/python3 AQMS_EFS2.py