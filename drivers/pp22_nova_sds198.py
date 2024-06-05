from __future__ import print_function
import struct
import serial
import sys
import db_connect
import time

is_connect = False

try:
    mydb = db_connect.connecting()
    mycursor = mydb.cursor()
    
    mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
    sensor_reader = mycursor.fetchone()
        
except Exception as e: 
    print("[X]  Nova SDS198 ID: " + str(sys.argv[1]) + " " + e)
def dectofloat(dec0, dec1):
    try:
        if (int(dec0) == 0 and int(dec1) == 0):
            return "0"

        hexvalue1 = str(hex(int(dec0))).replace("0x", "")
        hexvalue2 = str(hex(int(dec1))).replace("0x", "")
        hexvalue = hexvalue1.rjust(4, "0") + hexvalue2.rjust(4, "0")
        print(str(hexvalue1) + ":" + str(hexvalue2) + " ==> " + hexvalue)
        if (len(hexvalue) == 8):
            # print("   ===> " +str(struct.unpack('!f', bytes.fromhex(hexvalue))[0]))
            return str(struct.unpack('!f', bytes.fromhex(hexvalue))[0])
        else:
            return "0"
    except Exception as e:
        print("Error dectofloat")
        print(e)
        return "0"
    
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

        ser = serial.Serial(sensor_reader[0], sensor_reader[1])
        ser.baudrate = sensor_reader[1]
        ser.bytesize = serial.EIGHTBITS
        ser.parity = serial.PARITY_NONE
        ser.stopbits = serial.STOPBITS_ONE
        ser.timeout = 2

        if(is_connect == False):
            is_connect = True
            print("[V] SDS011 " + sensor_reader[0] + " CONNECTED")
        return ser
    except Exception as e:
        print("[X]  SDS011 ID: " + str(sys.argv[1]) )
        print(e)
        return None

try:
    while True:
        try:
            sensor = connect_sensor()
            if(sensor == None):
                continue
            value = 0
            values = "SDS198;0;0;END"
            try:
                req = bytes.fromhex("AA B4 04 00 00 00 00 00 00 00 00 00 00 00 00 FF FF 02 AB")
                sensor.write(req)
                retval = ""
                retval = sensor.readline().hex()
                valuepm25 = int(retval[6:8]+retval[4:6],16)
                valuepm100 = int(retval[10:12]+retval[8:10],16)
                values = "SDS198;" + str(valuepm25) + ";"+str(valuepm100)+"END"
                # print(value)
            except Exception as e:
                print(e)
                None
            
        except Exception as e:
            print(e)
        time.sleep(2)

except Exception as e:
    print(e)