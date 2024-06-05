from __future__ import print_function
from pymodbus.client.sync import ModbusSerialClient
import sys
import minimalmodbus
import serial
import serial.rs485
import time
import datetime
import struct
import db_connect

is_SDS019_connect = False
is_zero_calibrating = False
zerocal_finished_at = ""

try:
    mydb = db_connect.connecting()
    mycursor = mydb.cursor()
    
    mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
    sensor_reader = mycursor.fetchone()
        
except Exception as e: 
    print("[X]  SDS019 ID: " + str(sys.argv[1]) + " " + e)

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

def connect_sds019():
    global is_SDS019_connect
    try:
        mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
        sensor_reader = mycursor.fetchone()
        
        
        ser = serial.rs485.RS485(port=sensor_reader[0], baudrate=sensor_reader[1])
        ser.rs485_mode = serial.rs485.RS485Settings(rts_level_for_tx=False, rts_level_for_rx=True, delay_before_tx=0.0, delay_before_rx=-0.0)
        client = ModbusSerialClient(method='rtu')
        client.socket = ser
        client.connect()
        result = client.read_holding_registers(address=0x00B4, count=3, unit=1)
        client.close()
        
        if(is_SDS019_connect == False):
            is_SDS019_connect = True
            print("[V] SDS019 " + sensor_reader[0] + " CONNECTED")
        
        return result
        
    except Exception as e:
        print("[X]  SDS019 ID: " + str(sys.argv[1]) + " " + e)
        return None


try:
    while True:
        try:
            val = connect_sds019()
            # print(val)
            SDS019 = "FS2_SDS019;" + str(val.registers[0]) + ";" + str(val.registers[1]) + ";" + str(val.registers[2]) + ";END;"            
            update_sensor_value(str(sys.argv[1]),str(SDS019))
            # print(SDS019)
        except Exception as e2:
            print(e2)
            is_SDS019_connect = False
            print("Reconnect SDS019");
            update_sensor_value(str(sys.argv[1]),0)
            
        time.sleep(2)

except Exception as e:
    print(e)