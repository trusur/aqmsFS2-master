#!/bin/bash

app_name="AQMS_EFS2"

while true; do
  if ! pgrep -x "$app_name" > /dev/null; then
    echo "Application $app_name closed"
    systemctl stop aqms-averaging
    systemctl stop aqms-driver-alpha
    echo "AQMS services stopped" 
    break
  fi
  sleep 1
done