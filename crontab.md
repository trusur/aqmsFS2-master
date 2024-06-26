# Crontab

1. Average Data 1 Minute Every Minute
```bash
* * * * * /user/bin/php /home/mx/aqms-efs1/gui/spark command:average1min >/dev/null 2>&1
```
2. Sent Data 1 Seconds Every 30sec
```bash
* * * * * /user/bin/php /home/mx/aqms-efs1/gui/spark command:sentdata1sec >/dev/null 2>&1
* * * * * sleep 30; /user/bin/php /home/mx/aqms-efs1/gui/spark command:sentdata1sec >/dev/null 2>&1
```
3. Average Data 30 Mins
```bash
*/30 * * * * /user/bin/php /home/mx/aqms-efs1/gui/spark command:sentdata1min >/dev/null 2>&1
```
4. Sent Data 1 Minutes Every Half Hour
```bash
*/30 * * * * sleep 60; /user/bin/php /home/mx/aqms-efs1/gui/spark command:sentdata1min >/dev/null 2>&1
```