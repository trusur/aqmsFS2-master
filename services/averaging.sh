#!/bin/sh
while true
    do
        # Replace gui with full path to spark folder
        /usr/bin/php /home/mx/aqms-efs2/gui/spark command:formula_measurement_logs
        # /usr/bin/php /home/mx/aqms-efs2/gui/spark command:avg1min
        # /usr/bin/php /home/mx/aqms-efs2/gui/spark command:avg30min
    done