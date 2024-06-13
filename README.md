# AQMS EFS2-2024
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