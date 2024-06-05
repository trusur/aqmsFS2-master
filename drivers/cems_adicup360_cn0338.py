from __future__ import print_function
import sys
import serial
import time
import db_connect

is_SENSOR_connect = False
SENSOR = ""
COM_SENSOR = None

try:
    mydb = db_connect.connecting()
    mycursor = mydb.cursor()
    
    mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
    sensor_reader = mycursor.fetchone()
except Exception as e: 
    print("[X]  SENSOR Module ID: " + str(sys.argv[1]) + " " + e)
    
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
    global is_SENSOR_connect,COM_SENSOR
    try:
        SENSOR = ""
        mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
        sensor_reader = mycursor.fetchone()
        COM_SENSOR = serial.Serial(sensor_reader[0], sensor_reader[1])
        COM_SENSOR.write("\n\r".encode())
        time.sleep(1)
        # COM_SENSOR.write(b'\x03')
        COM_SENSOR.write(str("run\n\r").encode())
        time.sleep(1)
        for x in range(5):
            SENSOR = SENSOR + str(COM_SENSOR.readline())
                
        if(SENSOR.count("CN0338") > 0):
            is_SENSOR_connect = True
            print("[V] SENSOR Module " + sensor_reader[0] + " CONNECTED")
            COM_SENSOR.write(str("run\n\r").encode())
        else:
            is_SENSOR_connect = False
            
        time.sleep(2)
        
    except Exception as e: 
        is_SENSOR_connect = False
        return None

try:
    while True :
        try:
            if(not is_SENSOR_connect):
                connect_sensor()
                
            SENSOR = str(COM_SENSOR.readline())
            if(SENSOR.count("CEMS_CN0338;") <=0):
                SENSOR = "0;0;0;0;0;0;0;0;0;\\r\\n'"
                
            # print(SENSOR.replace("b'","'").replace("'","''"))
            update_sensor_value(str(sys.argv[1]),SENSOR.replace("b'","").replace("'",""))
            
            time.sleep(1)
            
        except Exception as e2:
            print(e2)
            is_SENSOR_connect = False
            print("Reconnect SENSOR Module ID: " + str(sys.argv[1]));
            update_sensor_value(str(sys.argv[1]),0)
        
        time.sleep(1)
        
except Exception as e: 
    print(e)
