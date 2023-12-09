from __future__ import print_function
from pymodbus.client.sync import ModbusSerialClient
from pyvantagepro import VantagePro2
import sys
import minimalmodbus
import serial
import serial.rs485
import time
import subprocess
import glob
import db_connect

mydb = db_connect.connecting()
mycursor = mydb.cursor()
mycursor.execute("TRUNCATE sensor_values")
mydb.commit()
mycursor.execute("TRUNCATE measurement_logs")
mydb.commit()


subprocess.Popen("php gui/spark serve > /dev/null 2>&1", shell=True)
time.sleep(1)

mycursor.execute(
    "UPDATE configurations SET content=NOW() WHERE name LIKE 'pump_last'")
mydb.commit()

mycursor.execute(
    "SELECT id,driver FROM sensor_readers WHERE sensor_code <> ''")
sensor_readers = mycursor.fetchall()
for sensor_reader in sensor_readers:
    time.sleep(3)
    command = "python drivers/" + \
        sensor_reader[1] + " " + str(sensor_reader[0])
    if sys.platform.startswith('win') == False:
        command = command.replace("python", "python3")
    subprocess.Popen(command, shell=True)


subprocess.Popen("php gui/spark command:formula_measurement_logs", shell=True)
print("php gui/spark command:formula_measurement_logs")
time.sleep(1)
subprocess.Popen("php gui/spark command:sentdata", shell=True)
print("php gui/spark command:sentdata")
time.sleep(1)
subprocess.Popen("php gui/spark command:sentdata_klhk", shell=True)
print("php gui/spark command:sentdata_klhk")
time.sleep(1)
subprocess.Popen("php gui/spark command:measurement_averaging", shell=True)
print("php gui/spark command:measurement_averaging")
time.sleep(1)
subprocess.Popen("php gui/spark command:zero_calibration", shell=True)
print("php gui/spark command:zero_calibration")
time.sleep(1)
subprocess.Popen("php gui/spark command:task_scheduler", shell=True)
print("php gui/spark command:task_scheduler")

time.sleep(1)
subprocess.Popen("python3 gui_start.py > /dev/null 2>&1", shell=True)
