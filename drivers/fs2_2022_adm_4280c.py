from __future__ import print_function
from pymodbus.client.sync import ModbusSerialClient
import sys
import minimalmodbus
import serial
import time
import datetime
import struct
import db_connect

is_connect = False

try:
    mydb = db_connect.connecting()
    mycursor = mydb.cursor()
    
    mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
    sensor_reader = mycursor.fetchone()
        
except Exception as e: 
    print("[X]  ADM_4280C ID: " + str(sys.argv[1]) + " " + e)

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
        rs485=minimalmodbus.Instrument(sensor_reader[0],1)
        rs485.serial.baudrate=sensor_reader[1]
        rs485.serial.parity=serial.PARITY_NONE
        rs485.serial.bytesize=8
        rs485.serial.stopbits=1
        rs485.mode=minimalmodbus.MODE_RTU
        rs485.serial.timeout=3
        value = rs485.read_registers(0,8,3)
        if(is_connect == False):
            is_connect = True
            print("[V] ADM_4280C " + sensor_reader[0] + " CONNECTED")
        
        return value
        
    except Exception as e:
        print("[X]  ADM_4280C ID: " + str(sys.argv[1]) + " " + e)
        return None

try:
    while True:
        try:
            val = connect_sensor()
            VAL = "CEMS_ADM_4280C;" + str(val[0]) + ";" + str(val[1]) + ";" + str(val[2]) + ";" + str(val[3]) + ";" + str(val[4]) + ";" + str(val[5]) + ";" + str(val[6]) + ";" + str(val[7]) + ";" + "END;"            
            update_sensor_value(str(sys.argv[1]),str(VAL))
        except Exception as e2:
            print(e2)
            is_connect = False
            print("Reconnect ADM_4280C");
            update_sensor_value(str(sys.argv[1]),0)
            
        time.sleep(2)

except Exception as e:
    print(e)