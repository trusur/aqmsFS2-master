from __future__ import print_function
import sys
import time
import db_connect
import random

is_PM_connect = False

try:
    mydb = db_connect.connecting()
    mycursor = mydb.cursor()
    
    mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
    sensor_reader = mycursor.fetchone()
    print("[V] PM " + sensor_reader[0] + " CONNECTED")
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
    
try:
    while True :
        try:
            PM = "000.0" + str(round(random.uniform(70, 99),0)).replace(".0","") + "," + str(round(random.uniform(2.0,2.2),1)) + ",+28.3,067,1007.4,00,*01543"
            update_sensor_value(str(sys.argv[1]),PM.replace("'","''"))
            
            # print(PM)
        except Exception as e2:
            print(e2)
            is_PM_connect = False
            print("Reconnect PM Sensor ID: " + str(sys.argv[1]));
            update_sensor_value(str(sys.argv[1]),0)
        
        time.sleep(1)
        
except Exception as e: 
    print(e)