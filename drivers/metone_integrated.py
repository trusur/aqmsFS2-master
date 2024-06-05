from __future__ import print_function
import sys
import serial
import time
import db_connect

is_PM_connect = False

try:
    mydb = db_connect.connecting()
    mycursor = mydb.cursor()
    
    mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
    sensor_reader = mycursor.fetchone()
except Exception as e: 
    print("[X]  [V] PM  Sensor ID: " + str(sys.argv[1]) + " " + e)
    
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
    
def connect_pm():
    global is_PM_connect
    try:
        mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
        sensor_reader = mycursor.fetchone()
        
        COM_PM = serial.Serial(sensor_reader[0], sensor_reader[1])
        PM = str(COM_PM.readline())
        if(PM.count("METONE") > 0):
            is_PM_connect = True
            print("[V] PM " + sensor_reader[0] + " CONNECTED")
            return COM_PM 
        else:
            is_PM_connect = False
            return None
            
    except Exception as e: 
        return None
    
try:
    while True :
        try:
            if(not is_PM_connect):
                COM_PM = connect_pm()
        
            PM = str(COM_PM.readline())
            if(PM.count("METONE") <= 0):
                PM = "b'000.000,0.0,+0.0,0,0,00,*0\\r\\n'"
                
            update_sensor_value(str(sys.argv[1]),PM.replace("'","''"))
            
            #print(PM)
        except Exception as e2:
            print(e2)
            is_PM_connect = False
            print("Reconnect PM Sensor ID: " + str(sys.argv[1]));
            update_sensor_value(str(sys.argv[1]),0)
        
        time.sleep(1)
        
except Exception as e: 
    print(e)