from drivers import db
import subprocess
import time
import atexit
import signal
import psutil
import os
def is_process_running(pid):
    try:
        return psutil.Process(pid).is_running()
    except psutil.NoSuchProcess:
        return False
def exit_handler():
    print("Stopping AQMS Driver Service...\n")
    subprocess.Popen("echo mx | sudo -S systemctl stop aqms-driver-alpha", shell=True)
    print("AQMS Driver should be stopped...")
    time.sleep(1)
    print("Stopping AQMS Averaging Service...\n")
    subprocess.Popen("echo mx | sudo -S systemctl stop aqms-averaging", shell=True)
    print("AQMS Averaging should be stopped...")
    time.sleep(1)
    print("Stopping AQMS HC Service...\n")
    subprocess.Popen("echo mx | sudo -S systemctl stop aqms-hc", shell=True)
    print("AQMS HC should be stopped...")
    time.sleep(1)
    print("Stopping AQMS Sending Service...\n")
    subprocess.Popen("echo mx | sudo -S systemctl stop aqms-sending", shell=True)
    print("AQMS Sending should be stopped...")
    file = open('test.txt','w')
    file.write('1')
    file.close()
def exit_handler_signal(signum,frame):
    print("Stopping AQMS Driver Service...\n")
    subprocess.Popen("echo mx | sudo -S systemctl stop aqms-driver-alpha", shell=True)
    print("AQMS Driver should be stopped...")
    time.sleep(1)
    print("Stopping AQMS Averaging Service...\n")
    subprocess.Popen("echo mx | sudo -S systemctl stop aqms-averaging", shell=True)
    print("AQMS Averaging should be stopped...")
    time.sleep(1)
    print("Stopping AQMS HC Service...\n")
    subprocess.Popen("echo mx | sudo -S systemctl stop aqms-hc", shell=True)
    print("AQMS HC should be stopped...")
    time.sleep(1)
    print("Stopping AQMS Sending Service...\n")
    subprocess.Popen("echo mx | sudo -S systemctl stop aqms-sending", shell=True)
    print("AQMS Sending should be stopped...")
    file = open('test.txt','w')
    file.write('1')
    file.close()

def truncate_sensor_values():
    try:
        cnx = db.connect()  
        cursor = cnx.cursor(buffered=True)
        cursor.execute("TRUNCATE TABLE sensor_values")
        cnx.commit()
        cursor.close()
        cnx.close()
        print("Table sensor_values truncated successfully!")
    except Exception as e:
        if cnx :
            cnx.close()
        if cursor:
            cursor.close()
        print('Truncate Table Error: ', e)

truncate_sensor_values()
time.sleep(1)

# print("Checking AQMS DM Service...\n")
# subprocess.Popen("echo mx | sudo -S systemctl restart aqms-dm", shell=True)
# time.sleep(2)

print("Checking AQMS HC Service...\n")
subprocess.Popen("echo mx | sudo -S systemctl restart aqms-hc", shell=True)
time.sleep(2)

print("Checking AQMS Averaging Service...\n")
subprocess.Popen("echo mx | sudo -S systemctl restart aqms-averaging", shell=True)
time.sleep(2)

print("Checking AQMS Averaging Sending...\n")
subprocess.Popen("echo mx | sudo -S systemctl restart aqms-sending", shell=True)
time.sleep(2)

print("Checking Cronjob Service...\n")
subprocess.Popen("echo mx | sudo -S crontab -l", shell=True)
time.sleep(2)

print("Starting Web Server...")
subprocess.Popen("php gui/spark serve", shell=True, stdout=subprocess.DEVNULL,stderr=subprocess.STDOUT)
print("Ready..")
time.sleep(2)
print("Trying to open application...")
subprocess.Popen("firefox --kiosk=http://localhost:8080", shell=True)
print("CTRL+C to exit")

pid = os.getpid()
signal.signal(signal.SIGTERM, exit_handler_signal)
atexit.register(exit_handler)
while True:
    # Running Loop
    time.sleep(1)
