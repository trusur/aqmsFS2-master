from __future__ import print_function
import sys
import serial
import time
import db_connect

is_PSU_connect = False

try:
    mydb = db_connect.connecting()
    mycursor = mydb.cursor()
    
    mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
    sensor_reader = mycursor.fetchone()
except Exception as e: 
    print("[X]  PSU Module ID: " + str(sys.argv[1]) + " " + e)
    
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
        
def connect_psu():
    global is_PSU_connect
    try:
        mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
        sensor_reader = mycursor.fetchone()
        
        COM_PSU = serial.Serial(sensor_reader[0], sensor_reader[1])
        PSU = str(COM_PSU.readline())
        if(PSU.count("FS2_PSU") > 0):
            is_PSU_connect = True
            print("[V] PSU Module " + sensor_reader[0] + " CONNECTED")
            return COM_PSU
        else:
            is_PSU_connect = False
            return None
            
    except Exception as e: 
        return None
    
connect_psu()

try:
    while True :
        try:
            if(not is_PSU_connect):
                COM_PSU = connect_psu()
                
            PSU = str(COM_PSU.readline())
            if(PSU.count("FS2_PSU") <= 0):
                PSU = "FS2_PSU;0.00;0.00;\\r\\n'"
                
            update_sensor_value(str(sys.argv[1]),PSU.replace("'","''"))
            
        except Exception as e2:
            print(e2)
            is_PSU_connect = False
            print("Reconnect PSU Module ID: " + str(sys.argv[1]));
            update_sensor_value(str(sys.argv[1]),0)
        
        time.sleep(1)
        
except Exception as e: 
    print(e)