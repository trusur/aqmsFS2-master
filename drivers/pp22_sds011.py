from __future__ import print_function
import simple_sds011
import json
import sys
import time
import datetime
import db_connect

is_connect = False

try:
    mydb = db_connect.connecting()
    mycursor = mydb.cursor()
    
    mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
    sensor_reader = mycursor.fetchone()
        
except Exception as e: 
    print("[X]  SDS011 ID: " + str(sys.argv[1]) + " " + e)

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

def connect_sensor():
    global is_connect
    try:
        mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
        sensor_reader = mycursor.fetchone()
        sds011 = simple_sds011.SDS011(sensor_reader[0])
        sds011.mode = simple_sds011.MODE_PASSIVE
        sds011.period=0
        
        if(is_connect == False):
            is_connect = True
            print("[V] SDS011 " + sensor_reader[0] + " CONNECTED")
        
        return sds011
        
    except Exception as e:
        print("[X]  SDS011 ID: " + str(sys.argv[1]) + " " + e)
        return None

try:
    while True:
        try:
            if(is_connect == False):
                sds011 = connect_sensor()
                
            if(sds011 != None):
                pm_value = sds011.query()
                PM = "SDS011;" + str(pm_value['value']['pm2.5']) + ";" + str(pm_value['value']['pm10.0']) + ";END;"  
            else:
                PM = "SDS011;0;0;END;"
                
            # print(PM)
            update_sensor_value(str(sys.argv[1]),str(PM))
        except Exception as e2:
            print(e2)
            is_connect = False
            print("Reconnect SDS011");
            update_sensor_value(str(sys.argv[1]),0)
            
        time.sleep(5)

except Exception as e:
    print(e)