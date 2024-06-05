from __future__ import print_function
import sys
import time
import db_connect
import random

is_WS_connect = False

try:
    mydb = db_connect.connecting()
    mycursor = mydb.cursor()
    
    mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
    sensor_reader = mycursor.fetchone()
    print("[V] VantagePro2 " + sensor_reader[0] + " CONNECTED")
except Exception as e: 
    print("[X]  [V] VantagePro2  Sensor ID: " + str(sys.argv[1]) + " " + e)
    
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
    
try:
    while True :
        try:
            pressure = str(round(random.uniform(990, 1000)/33.8639,2))
            wd = str(round(random.uniform(0, 360),0)).replace(".0","")
            ws = str(round(random.uniform(1, 4),0)).replace(".0","")
            temperature = str(round(random.uniform(78, 86),1))
            humidity = str(round(random.uniform(50, 80),0)).replace(".0","")
            sr = str(round(random.uniform(10, 20),0)).replace(".0","")
            rain_intensity = str(round(random.uniform(10, 20),0)).replace(".0","")
            WS = "XXX;0;" + pressure + ";0;0;" + temperature + ";" + ws + ";0;" + wd + ";" + humidity + ";0;0;" + sr + ";0.0;0;" + rain_intensity + ";0;0"
            
            update_sensor_value(str(sys.argv[1]),WS[0:149])
            # print(WS)
        except Exception as e2: 
            is_WS_connect = False
            print("Reconnect WS Davis");
            update_sensor_value(str(sys.argv[1]),';0;0;0;0;0;0;0;0;0;0;0;0;0.0;0;0;0;0')
        
        time.sleep(1)
        
except Exception as e: 
    print(e)