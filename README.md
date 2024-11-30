# AQMS EFS2-HYBRID ARTHUR
## Requirements
`php8.0` or newest, `python3`, `pip3`, `composer`

## Installation
1. Clone Repository
```bash
git clone https://github.com/trusur/aqms-efs1.git && cd aqms-efs1 && git checkout efs2
```
2. Install Python3 Library
```bash
pip3 install -r requirements.txt --break-system-packages
```
3. Install Dependency PHP
```bash
cd gui && composer install
```
4. Add Launcher
```bash
cd .. && chmod a+x launch_aqms.desktop && cp launch_aqms.desktop ~/Desktop
```

5. Create Services
```bash
sudo cp services/*.service /etc/systemd/system/
```
6. Start Services
```bash
systemctl start aqms-driver-alpha
systemctl start aqms-averaging
```
7. Stop Services
```bash
systemctl stop aqms-driver-alpha
systemctl stop aqms-averaging
```
8. Enable automaticly on boot:
```bash
systemctl enable aqms-driver-alpha
systemctl enable aqms-averaging
```
9. Disable service:
```bash
systemctl disable aqms-driver-alpha
systemctl disable aqms-averaging
```
10. Check service:
```bash
systemctl status aqms-driver-alpha
systemctl status aqms-averaging
```

## Setup Crontab
`sudo crontab -e` then choose `nano`
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