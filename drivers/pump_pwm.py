import serial
import db_connect
import sys
import time
current_state = 0
pump_state = 0
is_connect = False
try:
    mydb = db_connect.connecting()
    mycursor = mydb.cursor()
    
    mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
    sensor_reader = mycursor.fetchone()
    print(sensor_reader[0],sensor_reader[1])
except Exception as e: 
    print("[X] PUMP PWM Sensor ID: " + str(sys.argv[1]) + " " + e)
try:
    COM_PUMP = serial.Serial(str(sensor_reader[0]),int(sensor_reader[1]))
    is_connect = True
    time.sleep(2)
except Exception as e2:
    is_connect = False
    print(e2)
print('Pump Connect:', is_connect)
def get_pump_state():
    mydb = db_connect.connecting()
    mycursor = mydb.cursor()
    mycursor.execute("SELECT content FROM configurations WHERE name = 'pump_state'")
    rec = mycursor.fetchone()
    return int(rec[0])

while is_connect:
    time.sleep(2)
    pump_state = get_pump_state()
    if pump_state != current_state and is_connect:
        current_state = pump_state
        if(pump_state == 0):
            COM_PUMP.write(b'i')
        if(pump_state == 1):
            COM_PUMP.write(b'j')
        time.sleep(2)
