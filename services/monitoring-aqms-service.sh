#!/bin/bash

app_name="AQMS_EFS2"
sleep 60s
while true; do
  if ! pgrep -x "$app_name" > /dev/null; then
    echo "Application $app_name closed"
    echo mx | sudo -S systemctl stop aqms-averaging
    echo mx | sudo -S systemctl stop aqms-driver-alpha
    echo "AQMS services stopped"
    break
  fi
  sleep 1
done