from __future__ import print_function
from pymodbus.client.sync import ModbusSerialClient
import sys
import minimalmodbus
import serial
import time
import struct
import db_connect

is_MEMBRAPOR_connect = False

try:
    mydb = db_connect.connecting()
    mycursor = mydb.cursor()
    
    mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
    sensor_reader = mycursor.fetchone()
except Exception as e: 
    print("[X]  MEMBRAPOR ID: " + str(sys.argv[1]) + " " + e)

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

def connect_membrapor(membrapormode):
    global is_MEMBRAPOR_connect
    try:
        mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
        sensor_reader = mycursor.fetchone()
    
        rs485=minimalmodbus.Instrument(sensor_reader[0],1)
        rs485.serial.baudrate=sensor_reader[1]
        rs485.serial.parity=serial.PARITY_EVEN
        rs485.serial.bytesize=8
        rs485.serial.stopbits=1
        rs485.mode=minimalmodbus.MODE_RTU
        rs485.serial.timeout=0.2
        
        add0=rs485.read_register(1000,0,3,False)
        add1=rs485.read_register(1001,0,3,False)
        add2=rs485.read_register(1002,0,3,False)
        add3=rs485.read_register(1003,0,3,False)
        add4=rs485.read_register(1004,0,3,False)
        add5=rs485.read_register(1005,0,3,False)
        add6=rs485.read_register(1006,0,3,False)
        add7=rs485.read_register(1007,0,3,False)
        
        add00=rs485.read_register(1010,0,3,False)
        add01=rs485.read_register(1011,0,3,False)
        add02=rs485.read_register(1012,0,3,False)
        add03=rs485.read_register(1013,0,3,False)
        add04=rs485.read_register(1014,0,3,False)
        add05=rs485.read_register(1015,0,3,False)
        add06=rs485.read_register(1016,0,3,False)
        add07=rs485.read_register(1017,0,3,False)
        
        add000=rs485.read_register(1070,0,3,False)
        add001=rs485.read_register(1071,0,3,False)
        add002=rs485.read_register(1072,0,3,False)
        add003=rs485.read_register(1073,0,3,False)
        add004=rs485.read_register(1074,0,3,False)
        add005=rs485.read_register(1075,0,3,False)
        add006=rs485.read_register(1076,0,3,False)
        add007=rs485.read_register(1077,0,3,False)
        
        return str(add0) +";"+ str(add1) +";"+ str(add2) +";"+ str(add3) +";"+ str(add4) +";"+ str(add5) +";"+ str(add6) +";"+ str(add7) + ";" + str(add00) +";"+ str(add01) +";"+ str(add02) +";"+ str(add03) +";"+ str(add04) +";"+ str(add05) +";"+ str(add06) +";"+ str(add07) + ";" + str(add000) +";"+ str(add001) +";"+ str(add002) +";"+ str(add003) +";"+ str(add004) +";"+ str(add005) +";"+ str(add006) +";"+ str(add007)
        
    except Exception as e:
        print(e)
        return None


try:
    while True:
        try:
            MEMBRAPOR = connect_membrapor(int(sys.argv[1]))
            update_sensor_value(str(sys.argv[1]),str(MEMBRAPOR))
            #print(MEMBRAPOR)
        except Exception as e2:
            print(e2)
            print("Reconnect MEMBRAPOR");
            update_sensor_value(str(sys.argv[1]),0)

        time.sleep(1)

except Exception as e:
    print(e)