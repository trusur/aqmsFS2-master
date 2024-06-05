from __future__ import print_function
import sys
import serial
import time
import datetime
import glob
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
    
    
def port_check():
    try:
        print("Port Checking...")
        mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
        sensor_reader = mycursor.fetchone()
        ports = glob.glob(sensor_reader[0])
        if(len(ports) > 0):
            print(ports)
            print("[V] " + str(sensor_reader[0]) + " Found")
        else:
            print("[X] " + str(sensor_reader[0]) + " Not Found")
            print("Try to restart signal to MCU PSU")
            mycursor.execute("UPDATE configurations SET content = '1' WHERE  name = 'is_psu_restarting'")
            mydb.commit()            
            time.sleep(5)
            
    except Exception as e2:
        return None
def update_sensor_value(sensor_reader_id,value,pin = 0):
    try:
        try:
            mycursor.execute("SELECT id FROM sensor_values WHERE sensor_reader_id = '"+ sensor_reader_id +"' AND pin = '"+ str(pin) +"'")
            sensor_value_id = mycursor.fetchone()[0]
            mycursor.execute("UPDATE sensor_values SET value = '" + value + "' WHERE id = '" + str(sensor_value_id) + "'")
            mydb.commit()
        except Exception as e:
            mycursor.execute("INSERT INTO sensor_values (sensor_reader_id,pin,value) VALUES ('" + sensor_reader_id + "','"+ str(pin) +"','" + value + "')")
            mydb.commit()
    except Exception as e2:
        return None
        
def connect_pump():
    global is_PUMP_connect
    try:
        mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
        sensor_reader = mycursor.fetchone()
        
        COM_PUMP = serial.Serial(sensor_reader[0], sensor_reader[1],serial.EIGHTBITS,serial.PARITY_NONE,serial.STOPBITS_ONE,2)
        time.sleep(1)
        
        PUMP = str(COM_PUMP.read_until(str("#").encode()))
        PUMP = PUMP + str(COM_PUMP.read_until(str("$MCU_PUMP,READY#").encode()))
        if(PUMP.count("$MCU_PUMP") > 0):
            is_PUMP_connect = True
            print("[V] PUMP Module " + sensor_reader[0] + " CONNECTED")
            
            time.sleep(1)
            COM_PUMP.write(str("$FAN,255#").encode())
            PUMP = PUMP + str(COM_PUMP.read_until(str("$MCU_PUMP,FAN").encode()))

            time.sleep(1)
            COM_PUMP.write(str("$BMP280,BEGIN#").encode())
            PUMP = PUMP + str(COM_PUMP.read_until(str("$MCU_PUMP,$BMP280").encode()))
            time.sleep(1)
            COM_PUMP.write(str("$BMP280,SET,AUTO#").encode())
            PUMP = PUMP + str(COM_PUMP.read_until(str("$MCU_PUMP,$BMP280").encode()))

            time.sleep(1)
            COM_PUMP.write(str("$PRESSURE,SET,AUTO#").encode())
            PUMP = PUMP + str(COM_PUMP.read_until(str("$MCU_PUMP,PRESSURE").encode()))
            
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
            
        COM_PUMP.write(str("$PUMP," + str(pump_state+1) + ",SET," + str(pump_speed) + "#").encode());
        time.sleep(2)
        return returnval
        
    except Exception as e: 
        port_check()
        return None

update_sensor_value(str(sys.argv[1]),"",0)
update_sensor_value(str(sys.argv[1]),"",1)    
COM_PUMP = connect_pump()

try:
    while True :
        try:
            if(is_PUMP_connect == False):
                COM_PUMP = connect_pump()
                        
            PUMP = str(COM_PUMP.read_until(str("#").encode()))
            if(PUMP.count("$MCU_PUMP") <= 0):
                PUMP = ""
            
            if(PUMP.count("$MCU_PUMP,BMP280,VAL") > 0):
                update_sensor_value(str(sys.argv[1]),PUMP.replace("b'","").replace("'","''"),0)
                # print(PUMP.replace("b'","").replace("'","''"))
                
            if(PUMP.count("$MCU_PUMP,PRESSURE,RAW") > 0):
                update_sensor_value(str(sys.argv[1]),PUMP.replace("b'","").replace("'","''"),1)
                # print(PUMP.replace("b'","").replace("'","''"))
            
            mydb.commit()
            mycursor.execute("SELECT content FROM configurations WHERE name = 'pump_state'")
            rec = mycursor.fetchone()
            pump_state = int(rec[0])
            time.sleep(2)
            
            if pump_state != cur_pump_state and is_PUMP_connect:
                port_check()
                print(str(datetime.datetime.now()) + " => Switch Pump...")
                mycursor.execute("SELECT content FROM configurations WHERE name = 'pump_speed'")
                rec = mycursor.fetchone()
                pump_speed = int(rec[0])
                speed = (pump_state * 100) + pump_speed;
                cur_pump_state = pump_state
                COM_PUMP.write(str("$PUMP," + str(pump_state+1) + ",SET," + str(pump_speed) + "#").encode());
                time.sleep(2)
                
        except Exception as e2:
            print(e2)
            is_PUMP_connect = False
            port_check()
            print("Reconnect PUMP Module ID: " + str(sys.argv[1]));
            update_sensor_value(str(sys.argv[1]),0)
                
except Exception as e: 
    print(e)
