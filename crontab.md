# Average 1 Minute
* * * * * /usr/bin/php /home/mx/aqms-efs1/gui/spark command:avg1min >/dev/null 2>&1
# Send Data 1 Second Every Minute
* * * * * /usr/bin/php /home/mx/aqms-efs1/gui/spark command:sentdata1sec >/dev/null 2>&1
* * * * * sleep 5; /usr/bin/php /home/mx/aqms-efs1/gui/spark command:sentdata1sec >/dev/null 2>&1
* * * * * sleep 10; /usr/bin/php /home/mx/aqms-efs1/gui/spark command:sentdata1sec >/dev/null 2>&1
* * * * * sleep 15; /usr/bin/php /home/mx/aqms-efs1/gui/spark command:sentdata1sec >/dev/null 2>&1
* * * * * sleep 20; /usr/bin/php /home/mx/aqms-efs1/gui/spark command:sentdata1sec >/dev/null 2>&1
* * * * * sleep 25; /usr/bin/php /home/mx/aqms-efs1/gui/spark command:sentdata1sec >/dev/null 2>&1
* * * * * sleep 30; /usr/bin/php /home/mx/aqms-efs1/gui/spark command:sentdata1sec >/dev/null 2>&1
* * * * * sleep 35; /usr/bin/php /home/mx/aqms-efs1/gui/spark command:sentdata1sec >/dev/null 2>&1
* * * * * sleep 40; /usr/bin/php /home/mx/aqms-efs1/gui/spark command:sentdata1sec >/dev/null 2>&1
* * * * * sleep 45; /usr/bin/php /home/mx/aqms-efs1/gui/spark command:sentdata1sec >/dev/null 2>&1
* * * * * sleep 50; /usr/bin/php /home/mx/aqms-efs1/gui/spark command:sentdata1sec >/dev/null 2>&1
* * * * * sleep 55; /usr/bin/php /home/mx/aqms-efs1/gui/spark command:sentdata1sec >/dev/null 2>&1
#* * * * * /usr/bin/php /home/mx/aqms-efs1/gui/spark command:sentdata1sec_old >/dev/null 2&1
# Send Data 1 Minutes
1 * * * * /usr/bin/php /home/mx/aqms-efs1/gui/spark command:sentdata1min >/dev/null 2>&1
31 * * * * /usr/bin/php /home/mx/aqms-efs1/gui/spark command:sentdata1min >/dev/null 2>&1
# Average 30 Minute every Hour
0 * * * * /usr/bin/php /home/mx/aqms-efs1/gui/spark command:avg30min >/dev/null 2>&1
30 * * * * /usr/bin/php /home/mx/aqms-efs1/gui/spark command:avg30min >/dev/null 2>&1
# Send Data 1 Hour
5 * * * * /usr/bin/php /home/mx/aqms-efs1/gui/spark command:sentdata >/dev/null 2>&1
35 * * * * /usr/bin/php /home/mx/aqms-efs1/gui/spark command:sentdata >/dev/null 2>&1
# Send Data WS
* * * * * /usr/bin/php /home/mx/aqms-efs1/gui/spark command:sentdataws >/dev/null 2>&1
# Send Data EWS
* * * * * /usr/bin/php /home/mx/aqms-efs1/gui/spark command:sentdataews >/dev/null 2>&1
