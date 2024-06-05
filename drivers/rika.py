from __future__ import print_function
from textwrap import wrap
import sys
import serial
import time
import datetime
import db_connect
import codecs
import crcmod

is_SENSOR_connect = False
semea_tech_type = ""

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


def byte_formater(data):
    data = wrap(data, 2)
    returnval = b''
    for x in data:
        returnval = returnval + bytes.fromhex(x)

    return returnval


def read_concentration():
    global is_SENSOR_connect
    try:
        mycursor.execute(
            "SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '" + sys.argv[1] + "'")
        sensor_reader = mycursor.fetchone()

        ser = serial.Serial(sensor_reader[0], sensor_reader[1], timeout=5)
        ser.write(byte_formater("01030000000585C9"))

        if (is_SENSOR_connect == False):
            is_SENSOR_connect = True
            print("[V] SENSOR ID: " + sensor_reader[0] + " CONNECTED")

        val = wrap(ser.read(20).hex(), 2)
        # print(val)
        ser.close()
        ws = int(val[3]+val[4], 16)/100
        wd = int(val[5]+val[6], 16)
        celcius = int(val[7]+val[8], 16)/10
        rh = int(val[9]+val[10], 16)/10
        p = int(val[11]+val[12], 16)/10
        return [ws, wd, celcius, rh, p]

    except Exception as e:
        print("[X]  SENSOR ID: " + str(sys.argv[1]) + " " + e)
        return [0, 0, 0, 0, 0]


try:
    while True:
        try:
            val = read_concentration()
            # print(val)
        except Exception as e3:
            print(str(datetime.datetime.now()) +
                  " : error val = connect_sensor()")
            print(e3)

        try:
            SENSOR = "RIKA;" + str(val[0]) + ";" + str(
                val[1]) + ";" + str(val[2]) + ";" + str(val[3]) + ";" + str(val[4]) + ";END;"
            # print(SENSOR)
            update_sensor_value(str(sys.argv[1]), str(SENSOR))
        except Exception as e3:
            SENSOR = "RIKA;;0;0;0;0;0;END"
            print("SENSOR value error")
        time.sleep(1)

except Exception as e:
    print(e)
