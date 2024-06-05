from __future__ import print_function
import sys
import serial
import time
import datetime
import db_connect

is_PSU_connect = False
next_psu_checking = str(datetime.datetime.now() + datetime.timedelta(minutes=1))[0:19]
is_auto_restart = 0

try:
    mydb = db_connect.connecting()
    mycursor = mydb.cursor()
    
    mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
    sensor_reader = mycursor.fetchone()
except Exception as e: 
    print("[X]  PSU Module ID: " + str(sys.argv[1]) + " " + e)
    
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
        
def connect_psu():
    global is_PSU_connect,is_auto_restart
    try:
        mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
        sensor_reader = mycursor.fetchone()
        
        COM_PSU = serial.Serial(sensor_reader[0], sensor_reader[1],serial.EIGHTBITS,serial.PARITY_NONE,serial.STOPBITS_ONE,2)
        time.sleep(1)
        
        PSU = str(COM_PSU.read_until(str("#").encode()))
        PSU = PSU + str(COM_PSU.read_until(str("$MCU_PSU,READY#").encode()))
        if(PSU.count("$MCU_PSU") > 0):
            is_PSU_connect = True
            print("[V] PSU Module " + sensor_reader[0] + " CONNECTED")
            
            time.sleep(1)
            COM_PSU.write(str("$FAN,255#").encode())
            PSU = PSU + str(COM_PSU.read_until(str("$MCU_PSU,FAN").encode()))

            time.sleep(1)
            COM_PSU.write(str("$BMP280,BEGIN#").encode())
            PSU = PSU + str(COM_PSU.read_until(str("$MCU_PSU,$BMP280").encode()))
            time.sleep(1)
            COM_PSU.write(str("$BMP280,SET,AUTO#").encode())
            PSU = PSU + str(COM_PSU.read_until(str("$MCU_PSU,$BMP280").encode()))
            
            if(is_auto_restart == 1):
                time.sleep(1)
                COM_PSU.write(str("$AUTO_RESTART,ON#").encode())
                PSU = PSU + str(COM_PSU.read_until(str("$MCU_PSU,AUTO_RESTART").encode()))
                print("AUTO RESTART MODE ON")
            else:
                print("AUTO RESTART MODE OFF")
            

            returnval = COM_PSU
        else:
            is_PSU_connect = False
            returnval = None
        
        mycursor.execute("SELECT content FROM configurations WHERE name = 'is_psu_restarting'")
        rec = mycursor.fetchone()
        is_psu_restarting = int(rec[0])
        
        time.sleep(2)
        if(is_psu_restarting == 1):
            print("Restarting");
            mycursor.execute("UPDATE configurations SET content = '0' WHERE name = 'is_psu_restarting'")
            mydb.commit()
            COM_PSU.write(str("$RESTART,3000#").encode());
            time.sleep(2)
        return returnval
            
    except Exception as e: 
        return None

mycursor.execute("SELECT content FROM configurations WHERE name = 'is_auto_restart'")
rec = mycursor.fetchone()
is_auto_restart = int(rec[0])

update_sensor_value(str(sys.argv[1]),"",0)
COM_PSU = connect_psu()

try:
    while True :
        try:
            if(is_auto_restart == 1):
                currenttime = datetime.datetime.now()
                if(next_psu_checking <= str(currenttime)[0:19]):
                    next_psu_checking = str(datetime.datetime.now() + datetime.timedelta(minutes=1))[0:19]
                    print(str(currenttime) + " => PSU CHECKING...")
                    COM_PSU.write(str("$CHECKING#").encode())
                    PSU = str(COM_PSU.read_until(str("$MCU_PSU,CHECKING_SUCCEED#").encode()))
                    if(PSU.count("CHECKING_SUCCEED") > 0):
                        print("$MCU_PSU,CHECKING_SUCCEED#")
                
            if(is_PSU_connect == False):
                COM_PSU = connect_psu()

            try:
                PSU = str(COM_PSU.read_until(str("#").encode()))
                if(PSU.count("$MCU_PSU") <= 0):
                    PSU = ""
            
                if(PSU.count("$MCU_PSU,BMP280,VAL") > 0):
                    update_sensor_value(str(sys.argv[1]),PSU.replace("b'","").replace("'","''"),0)
                    # print(PSU.replace("b'","").replace("'","''"))

            except Exception as e3:
                None
                        
            mydb.commit()
            mycursor.execute("SELECT content FROM configurations WHERE name = 'is_psu_restarting'")
            rec = mycursor.fetchone()
            is_psu_restarting = int(rec[0])
            time.sleep(2)
                
            if(is_psu_restarting == 1):
                mycursor.execute("UPDATE configurations SET content = '0' WHERE name = 'is_psu_restarting'")
                mydb.commit()
                time.sleep(1)
                COM_PSU.write(str("$RESTART,3000#").encode())
                time.sleep(1)
                
        except Exception as e2:
            print(e2)
            is_PSU_connect = False
            print("Reconnect PSU Module ID: " + str(sys.argv[1]));
            update_sensor_value(str(sys.argv[1]),0)
                
except Exception as e: 
    print(e)
