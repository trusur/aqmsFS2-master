# AQMS EFS1-2023
## Requirements
`php8.0` or newest, `python3`, `pip3`, `composer`

## Installation
1. Clone Repository
```bash
git clone https://github.com/trusur/aqms-efs1.git && cd aqms-efs1 && git checkout efs1
```
2. Install Python3 Library
```bash
pip3 install -r requirements.txt --break-system-packages
```
3. Install Dependency PHP
```bash
cd && composer install
```
4. Add Launcher
```bash
cd .. && chmod a+x launch_aqms.desktop && cp launch_aqms.desktop ~/Desktop
```