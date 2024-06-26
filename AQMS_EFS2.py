from drivers import db
import subprocess
import time
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
print("Checking AQMS Driver Service...")
subprocess.Popen("echo mx | sudo -S systemctl restart aqms-driver-alpha", shell=True)
time.sleep(1)
print("Checking AQMS Averaging Service...")
subprocess.Popen("echo mx | sudo -S systemctl restart aqms-averaging", shell=True)
time.sleep(1)
print("Checking Cronjob Service...")
subprocess.Popen("echo mx | sudo -S crontab -l", shell=True)
time.sleep(1)
print("Starting Web Server...")
subprocess.Popen("php gui/spark serve", shell=True)
print("Ready..")
time.sleep(1)
print("Trying to open application...")
subprocess.Popen("firefox --kiosk=http://localhost:8080", shell=True)
print("CTRL+C to exit")

