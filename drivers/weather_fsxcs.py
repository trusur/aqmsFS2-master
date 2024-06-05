from __future__ import print_function
from pymodbus.client.sync import ModbusSerialClient
import sys
import minimalmodbus
import serial
import time
import datetime
import db_connect

is_SENSOR_connect = False

try:
    mydb = db_connect.connecting()
    mycursor = mydb.cursor()

    mycursor.execute(
        "SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '" + sys.argv[1] + "'")
    sensor_reader = mycursor.fetchone()

except Exception as e:
    print("[X]  SENSOR ID: " + str(sys.argv[1]) + " " + e)


def update_sensor_value(sensor_reader_id, value):
    try:
        try:
            mycursor.execute("SELECT id FROM sensor_values WHERE sensor_reader_id = '" +
                             str(sensor_reader_id) + "' AND pin = '0'")
            sensor_value_id = mycursor.fetchone()[0]
            mycursor.execute("UPDATE sensor_values SET value = '" +
                             str(value) + "' WHERE id = '" + str(sensor_value_id) + "'")
            mydb.commit()
        except Exception as e:
            mycursor.execute("INSERT INTO sensor_values (sensor_reader_id,pin,value) VALUES ('" +
                             str(sensor_reader_id) + "','0','" + str(value) + "')")
            mydb.commit()
    except Exception as e2:
        print("Error update_sensor_value")
        print(e2)
        return None


def connect_sensor():
    global is_SENSOR_connect
    try:
        mycursor.execute(
            "SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '" + sys.argv[1] + "'")
        sensor_reader = mycursor.fetchone()

        rs485 = minimalmodbus.Instrument(sensor_reader[0], 1, 'rtu', False)
        rs485.serial.baudrate = sensor_reader[1]
        rs485.serial.parity = serial.PARITY_NONE
        rs485.serial.bytesize = 8
        rs485.serial.stopbits = 1
        rs485.serial.timeout = 5

        regValue = rs485.read_registers(500, 10, 3)

        if(is_SENSOR_connect == False):
            is_SENSOR_connect = True
            print("[V] SENSOR ID: " + sensor_reader[0] + " CONNECTED")

        return regValue

    except Exception as e:
        print("[X]  SENSOR ID: " + str(sys.argv[1]) + " " + e)
        return [0, 0, 0, 0, 0, 0, 0, 0, 0, 0]


try:
    while True:
        try:
            val = connect_sensor()
        except Exception as e3:
            print(str(datetime.datetime.now()) +
                  " : error val = connect_sensor()")
            print(e3)

        try:
            SENSOR = "FSXCS;" + str(val[0]) + ";" + str(val[1]) + ";" + str(val[2]) + ";" + str(val[3]) + ";" + str(val[4]) + ";" + str(
                val[5]) + ";" + str(val[6]) + ";" + str(val[7]) + ";" + str(val[8]) + ";" + str(val[9]) + ";END;"
            update_sensor_value(str(sys.argv[1]), str(SENSOR))
        except Exception as e3:
            SENSOR = "FSXCS;0;0;0;0;0;0;0;0;0;0;END;"
            print("SENSOR value error")

        time.sleep(1)

except Exception as e:
    print(e)
