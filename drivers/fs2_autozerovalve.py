from __future__ import print_function
import sys
import serial
import time
import db_connect

is_AUTOZEROVALVE_connect = False
cur_autozerovalve_state = 0

try:
    mydb = db_connect.connecting()
    mycursor = mydb.cursor()
    
    mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
    sensor_reader = mycursor.fetchone()
except Exception as e: 
    print("[X]  AUTOZEROVALVE Module ID: " + str(sys.argv[1]) + " " + e)
    
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
        
def connect_autozerovalve():
    global is_AUTOZEROVALVE_connect
    try:
        mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
        sensor_reader = mycursor.fetchone()
        
        COM_AUTOZEROVALVE = serial.Serial(sensor_reader[0], sensor_reader[1])
        AUTOZEROVALVE = str(COM_AUTOZEROVALVE.readline())
        if(AUTOZEROVALVE.count("FS2_AUTO_ZERO_VALVE") > 0):
            is_AUTOZEROVALVE_connect = True
            print("[V] AUTOZEROVALVE Module " + sensor_reader[0] + " CONNECTED")
            returnval = COM_AUTOZEROVALVE
        else:
            is_AUTOZEROVALVE_connect = False
            returnval = None
        
        mycursor.execute("SELECT content FROM configurations WHERE name = 'is_zerocal'")
        rec = mycursor.fetchone()
        is_zerocal = int(rec[0])
        
        time.sleep(2)
        
        cur_autozerovalve_state = is_zerocal
        if str(cur_autozerovalve_state) == "0":
            COM_AUTOZEROVALVE.write(b'i')
        else:
            COM_AUTOZEROVALVE.write(b'j')
        
        return returnval
            
    except Exception as e: 
        return None
    
connect_autozerovalve()

try:
    while True :
        try:
            if(not is_AUTOZEROVALVE_connect):
                COM_AUTOZEROVALVE = connect_autozerovalve()
                
            AUTOZEROVALVE = str(COM_AUTOZEROVALVE.readline())
            if(AUTOZEROVALVE.count("FS2_AUTO_ZERO_VALVE") <= 0):
                AUTOZEROVALVE = "FS2_AUTO_ZERO_VALVE;0;\\r\\n'"
                
            update_sensor_value(str(sys.argv[1]),AUTOZEROVALVE.replace("'","''"))
            
            mycursor.execute("SELECT content FROM configurations WHERE name = 'is_zerocal'")
            rec = mycursor.fetchone()
            is_zerocal = int(rec[0])
        
            # print(str(cur_autozerovalve_state) + ":" + str(is_zerocal))
            # print(AUTOZEROVALVE)
            
            if is_zerocal != cur_autozerovalve_state and is_AUTOZEROVALVE_connect:
                mycursor.execute("SELECT content FROM configurations WHERE name = 'is_zerocal'")
                rec = mycursor.fetchone()
                is_zerocal = int(rec[0])
                cur_autozerovalve_state = is_zerocal
                if str(cur_autozerovalve_state) == "0":
                    COM_AUTOZEROVALVE.write(b'i')
                else:
                    COM_AUTOZEROVALVE.write(b'j')
                    
                time.sleep(2)
                
            
        except Exception as e2:
            print(e2)
            is_AUTOZEROVALVE_connect = False
            print("Reconnect AUTOZEROVALVE Module ID: " + str(sys.argv[1]));
            update_sensor_value(str(sys.argv[1]),0)
                
except Exception as e: 
    print(e)
