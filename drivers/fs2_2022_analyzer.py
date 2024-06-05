from __future__ import print_function
import sys
import serial
import time
import db_connect

is_ANALYZER_connect = False
ANALYZER = ""

try:
    mydb = db_connect.connecting()
    mycursor = mydb.cursor()
    
    mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
    sensor_reader = mycursor.fetchone()
except Exception as e: 
    print("[X]  ANALYZER Module ID: " + str(sys.argv[1]) + " " + e)
    
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
        
def connect_analyzer():
    global is_ANALYZER_connect
    try:
        mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
        sensor_reader = mycursor.fetchone()
        COM_ANALYZER = serial.Serial(sensor_reader[0], sensor_reader[1],serial.EIGHTBITS,serial.PARITY_NONE,serial.STOPBITS_ONE,2)
        time.sleep(1)
        
        ANALYZER = str(COM_ANALYZER.read_until(str("#").encode()))
        ANALYZER = ANALYZER + str(COM_ANALYZER.read_until(str("$MCU_ANZ,READY#").encode()))
        if(ANALYZER.count("$MCU_ANZ") > 0):
            is_ANALYZER_connect = True
            print("[V] ANALYZER Module " + sensor_reader[0] + " CONNECTED")
            
            time.sleep(1)
            COM_ANALYZER.write(str("$FAN,255#").encode())
            ANALYZER = ANALYZER + str(COM_ANALYZER.read_until(str("$MCU_ANZ,FAN").encode()))

            time.sleep(1)
            COM_ANALYZER.write(str("$BMP280,BEGIN#").encode())
            ANALYZER = ANALYZER + str(COM_ANALYZER.read_until(str("$MCU_ANZ,$BMP280").encode()))
            time.sleep(1)
            COM_ANALYZER.write(str("$BMP280,SET,AUTO#").encode())
            ANALYZER = ANALYZER + str(COM_ANALYZER.read_until(str("$MCU_ANZ,$BMP280").encode()))

            time.sleep(1)
            COM_ANALYZER.write(str("$BME280,BEGIN#").encode())
            ANALYZER = ANALYZER + str(COM_ANALYZER.read_until(str("$MCU_ANZ,$BME280").encode()))
            time.sleep(1)
            COM_ANALYZER.write(str("$BME280,SET,AUTO#").encode())
            ANALYZER = ANALYZER + str(COM_ANALYZER.read_until(str("$MCU_ANZ,$BME280").encode()))

            time.sleep(1)
            COM_ANALYZER.write(str("$SHT31,BEGIN#").encode())
            ANALYZER = ANALYZER + str(COM_ANALYZER.read_until(str("$MCU_ANZ,SHT31").encode()))
            time.sleep(1)
            COM_ANALYZER.write(str("$SHT31,SET,AUTO#").encode())
            ANALYZER = ANALYZER + str(COM_ANALYZER.read_until(str("$MCU_ANZ,SHT31").encode()))
            
            time.sleep(1)
            COM_ANALYZER.write(str("$VAC_IN,SET,AUTO#").encode())
            ANALYZER = ANALYZER + str(COM_ANALYZER.read_until(str("$MCU_ANZ,VAC_IN").encode()))
            time.sleep(1)
            COM_ANALYZER.write(str("$VAC_OUT,SET,AUTO#").encode())
            ANALYZER = ANALYZER + str(COM_ANALYZER.read_until(str("$MCU_ANZ,VAC_OUT").encode()))
            
            return COM_ANALYZER
        else:
            is_ANALYZER_connect = False
            return None
            
    except Exception as e: 
        return None
    
update_sensor_value(str(sys.argv[1]),"",0)
update_sensor_value(str(sys.argv[1]),"",1)
update_sensor_value(str(sys.argv[1]),"",2)
update_sensor_value(str(sys.argv[1]),"",3)
update_sensor_value(str(sys.argv[1]),"",4)
update_sensor_value(str(sys.argv[1]),"",5)
update_sensor_value(str(sys.argv[1]),"",6)
COM_ANALYZER = connect_analyzer()

try:
    while True :
        try:
            if(is_ANALYZER_connect == False):
                COM_ANALYZER = connect_analyzer()
            
            ANALYZER = str(COM_ANALYZER.read_until(str("#").encode()))
            if(ANALYZER.count("$MCU_ANZ") <= 0):
                ANALYZER = ""
                
            if(ANALYZER.count("$MCU_ANZ,PM,10,DATA,") > 0):
                update_sensor_value(str(sys.argv[1]),ANALYZER.replace("b'","").replace("'","''"),0)
                # print(ANALYZER.replace("b'","").replace("'","''"))
                
            if(ANALYZER.count("$MCU_ANZ,PM,2.5,DATA,") > 0):
                update_sensor_value(str(sys.argv[1]),ANALYZER.replace("b'","").replace("'","''"),1)
                # print(ANALYZER.replace("b'","").replace("'","''"))
                
            if(ANALYZER.count("$MCU_ANZ,BMP280,VAL") > 0):
                update_sensor_value(str(sys.argv[1]),ANALYZER.replace("b'","").replace("'","''"),2)
                # print(ANALYZER.replace("b'","").replace("'","''"))
                
            if(ANALYZER.count("$MCU_ANZ,BME280,VAL") > 0):
                update_sensor_value(str(sys.argv[1]),ANALYZER.replace("b'","").replace("'","''"),3)
                # print(ANALYZER.replace("b'","").replace("'","''"))
                
            if(ANALYZER.count("$MCU_ANZ,SHT31,VAL") > 0):
                update_sensor_value(str(sys.argv[1]),ANALYZER.replace("b'","").replace("'","''"),4)
                # print(ANALYZER.replace("b'","").replace("'","''"))
                
            if(ANALYZER.count("$MCU_ANZ,VAC_IN,RAW") > 0):
                update_sensor_value(str(sys.argv[1]),ANALYZER.replace("b'","").replace("'","''"),5)
                # print(ANALYZER.replace("b'","").replace("'","''"))
                
            if(ANALYZER.count("$MCU_ANZ,VAC_OUT,RAW") > 0):
                update_sensor_value(str(sys.argv[1]),ANALYZER.replace("b'","").replace("'","''"),6)
                # print(ANALYZER.replace("b'","").replace("'","''"))
            
        except Exception as e2:
            print(e2)
            is_ANALYZER_connect = False
            print("Reconnect ANALYZER Module ID: " + str(sys.argv[1]));
            update_sensor_value(str(sys.argv[1]),0)
        
except Exception as e: 
    print(e)