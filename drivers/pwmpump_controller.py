from __future__ import print_function
import sys
import serial
import time
import db_connect

is_PUMP_connect = False
pump_speed = 0
cur_pump_state = 0

try:
    mydb = db_connect.connecting()
    mycursor = mydb.cursor()
    
    mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
    sensor_reader = mycursor.fetchone()
except Exception as e: 
    print("[X]  [V] PUMP PWM Sensor ID: " + str(sys.argv[1]) + " " + e)
        
def connect_pump():
    global is_PUMP_connect
    try:
        mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
        sensor_reader = mycursor.fetchone()
        COM_PUMP = serial.Serial(sensor_reader[0], sensor_reader[1])
        is_PUMP_connect = True
        print("[V] PUMP PWM " + sensor_reader[0] + " CONNECTED")
        
        mycursor.execute("SELECT content FROM configurations WHERE name = 'pump_speed'")
        rec = mycursor.fetchone()
        pump_speed = int(rec[0])
        
        mycursor.execute("SELECT content FROM configurations WHERE name = 'pump_state'")
        rec = mycursor.fetchone()
        pump_state = int(rec[0])
        
        time.sleep(2)
        
        speed = (pump_state * 100) + pump_speed;
        cur_pump_state = pump_state
        #print(str(cur_pump_state) + ":" + str(pump_state))
            
        COM_PUMP.write(str(speed).encode());
            
    except Exception as e: 
        return None
    
connect_pump()

try:
    while False :
        try:
            if(not is_PUMP_connect):
                COM_PUMP = connect_pump()
            
            mycursor.execute("SELECT content FROM configurations WHERE name = 'pump_state'")
            rec = mycursor.fetchone()
            pump_state = int(rec[0])
            time.sleep(2)
            #print(str(cur_pump_state) + ":" + str(pump_state))
            
            if pump_state != cur_pump_state and is_PUMP_connect:
                mycursor.execute("SELECT content FROM configurations WHERE name = 'pump_speed'")
                rec = mycursor.fetchone()
                pump_speed = int(rec[0])
                speed = (pump_state * 100) + pump_speed;
                cur_pump_state = pump_state
                COM_PUMP.write(str(speed).encode());
        
        except Exception as e2:
            print(e2)
            is_PUMP_connect = False
            print("Reconnect PUMP Sensor ID: " + str(sys.argv[1]));
        
        time.sleep(1)
        
except Exception as e: 
    print(e)
