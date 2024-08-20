from drivers import db
import subprocess
import time
import atexit
def exit_handler():
    print("Stopping AQMS Driver Service...\n")
    subprocess.Popen("echo mx | sudo -S systemctl stop aqms-driver-alpha", shell=True)
    time.sleep(1)
    print("Stopping AQMS Averaging Service...\n")
    subprocess.Popen("echo mx | sudo -S systemctl stop aqms-averaging", shell=True)
    print("AQMS should be stopped...")
def init_pump():
    try:
        cnx = db.connect()
        cursor = cnx.cursor(buffered=True)
        cursor.execute("UPDATE configurations SET content=NOW() WHERE name LIKE 'pump_last'")
        cnx.commit()
        cursor.close()
        cnx.close()
    except Exception as e:
        print('Init Pump Error: ',e)

print("Starting Pump...")
init_pump()
print("Checking AQMS Driver Service...\n")
subprocess.Popen("echo mx | sudo -S systemctl restart aqms-driver-alpha", shell=True)
time.sleep(1)
print("Checking AQMS Averaging Service...\n")
subprocess.Popen("echo mx | sudo -S systemctl restart aqms-averaging", shell=True)
time.sleep(1)
print("Checking Cronjob Service...\n")
subprocess.Popen("echo mx | sudo -S crontab -l", shell=True)
time.sleep(1)
print("Starting Web Server...")
subprocess.Popen("php gui/spark serve", shell=True, stdout=subprocess.DEVNULL,stderr=subprocess.STDOUT)
print("Ready..")
time.sleep(1)
print("Trying to open application...")
subprocess.Popen("firefox --kiosk=http://localhost:8080", shell=True)
print("CTRL+C to exit")

atexit.register(exit_handler)
while True:
    # Running Loop
    time.sleep(1)
