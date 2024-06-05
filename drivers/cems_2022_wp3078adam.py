from __future__ import print_function
from pymodbus.client.sync import ModbusSerialClient as ModbusClient
import sys
import time
import datetime
import struct
import db_connect

currentnow = 4000
portADC = 'COM12'


def setAnalogOutput(address,value):
    try:
        address = int(address)
        client = ModbusClient(method = 'rtu',port=portADC,baudrate=9600,parity = 'N',timeout=3)
        connection = client.connect()
        if(connection):
            write = client.write_register(address, value, unit=1)
            print(write)
            client.close()
            return True
        
    except Exception as e:
        print("[X]  WP3078ADAM ID: " + str(sys.argv[1]) + " " + e)
        return None

try:
    while True:
        try:
            print("currentnow : " + str(currentnow))
            val = setAnalogOutput(0,currentnow)
            currentnow = currentnow + 1000
            if(currentnow > 20000):
                currentnow = 4000
        except Exception as e2:
            print(e2)
            is_connect = False
            print("Reconnect WP3078ADAM");
            
        time.sleep(2)

except Exception as e:
    print(e)