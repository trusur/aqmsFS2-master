from __future__ import print_function
from pyvantagepro import VantagePro2
import sys
import time
import sqlite3
conn = sqlite3.connect('../gui/app/Database/database.s3db')

is_WS_connect = False
sensor_reader = ["","",""]

try:
    cursor = conn.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
    for row in cursor:
        sensor_reader[0] = row[0]
        sensor_reader[1] = row[1]
except Exception as e: 
    print("[X]  [V] VantagePro2  Sensor ID: " + str(sys.argv[1]) + " " + e)
    
def update_sensor_value(sensor_reader_id,value):
    try:
        try:
            cursor = conn.execute("SELECT id FROM sensor_values WHERE sensor_reader_id = '"+ sensor_reader_id +"' AND pin = '0'")        
            for row in cursor:
                sensor_value_id = row[0]
                
            conn.execute("UPDATE sensor_values SET value = '" + value + "', xtimestamp = datetime('now') WHERE id = '" + str(sensor_value_id) + "'")
            conn.commit()
        except Exception as e:
            conn.execute("INSERT INTO sensor_values (sensor_reader_id,pin,value) VALUES ('" + sensor_reader_id + "','0','" + value + "')")
            conn.commit()
    except Exception as e2:
        return None
    
def connect_ws():
    global is_WS_connect
    try:
        COM_WS = VantagePro2.from_url("serial:%s:%s:8N1" % (sensor_reader[0], sensor_reader[1]))
        ws_data = COM_WS.get_current_data()
        WS = ws_data.to_csv(';',False)
        print("[V] VantagePro2 " + sensor_reader[0] + " CONNECTED")
        is_WS_connect = True
        return COM_WS 
    except:
        is_WS_connect = False
        return None
    
try:
    while True :
        try:
            if(not is_WS_connect):
                COM_WS = connect_ws()
        
            ws_data = COM_WS.get_current_data()
            WS = ws_data.to_csv(';',False)
            
            update_sensor_value(str(sys.argv[1]),WS)
            #print(WS)
        except Exception as e2: 
            is_WS_connect = False
            print("Reconnect WS Davis");
            update_sensor_value(str(sys.argv[1]),';0;0;0;0;0;0;0;0;0;0;0;0;0.0;0;0;0;0')
        
        time.sleep(1)
        
except Exception as e: 
    print(e)