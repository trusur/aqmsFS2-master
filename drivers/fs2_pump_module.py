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
    print("[X]  PUMP Module ID: " + str(sys.argv[1]) + " " + e)
    
def update_sensor_value(sensor_reader_id,value):
    try:
        try:
            mycursor.execute("SELECT id FROM sensor_values WHERE sensor_reader_id = '"+ sensor_reader_id +"' AND pin = '0'")
            sensor_value_id = mycursor.fetchone()[0]
            mycursor.execute("UPDATE sensor_values SET value = '" + value + "' WHERE id = '" + str(sensor_value_id) + "'")
            mydb.commit()
        except Exception as e:
            mycursor.execute("INSERT INTO sensor_values (sensor_reader_id,pin,value) VALUES ('" + sensor_reader_id + "','0','" + value + "')")
            mydb.commit()
    except Exception as e2:
        return None
        
def connect_pump():
    global is_PUMP_connect
    try:
        mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
        sensor_reader = mycursor.fetchone()
        
        COM_PUMP = serial.Serial(sensor_reader[0], sensor_reader[1])
        PUMP = str(COM_PUMP.readline())
        if(PUMP.count("FS2_PUMP") > 0):
            is_PUMP_connect = True
            print("[V] PUMP Module " + sensor_reader[0] + " CONNECTED")
            returnval = COM_PUMP
        else:
            is_PUMP_connect = False
            returnval = None
        
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
        
        return returnval
            
    except Exception as e: 
        return None
    
connect_pump()

try:
    while True :
        try:
            if(not is_PUMP_connect):
                COM_PUMP = connect_pump()
                
            PUMP = str(COM_PUMP.readline())
            if(PUMP.count("FS2_PUMP") <= 0):
                PUMP = "FS2_PUMP;0;0;0;0.00;0.00;\\r\\n'"
                
            update_sensor_value(str(sys.argv[1]),PUMP.replace("'","''"))
            
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
            print("Reconnect PUMP Module ID: " + str(sys.argv[1]));
            update_sensor_value(str(sys.argv[1]),0)
        
        time.sleep(1)
        
except Exception as e: 
    print(e)
