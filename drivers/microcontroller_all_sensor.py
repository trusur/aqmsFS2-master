from __future__ import print_function
from textwrap import wrap
import sys
import serial
import time
import db_connect

is_SENSOR_connect = False
is_connect = False
current_state = 0
current_speed = 0
pump_state = 0

try:
    mydb = db_connect.connecting()
    mycursor = mydb.cursor()

    mycursor.execute(
        "SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '" + sys.argv[1] + "'")
    sensor_reader = mycursor.fetchone()
except Exception as e:
    print("[X]  SENSOR Module ID: " + str(sys.argv[1]) + " " + e)


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


mycursor.execute(
    "SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '" + sys.argv[1] + "'")
sensor_reader = mycursor.fetchone()
ser = serial.Serial(sensor_reader[0], sensor_reader[1], timeout=5)


def read_concentration():
    global is_SENSOR_connect
    try:
        # print(ser.write)
        if (is_SENSOR_connect == False):
            is_SENSOR_connect = True
            print("[V] SENSOR ID: " + sensor_reader[0] + " CONNECTED")
        ser.write(bytes(b'REQ,*'))
        time.sleep(1)

        data = ser.readline().decode('utf-8').strip('/r/n')
        # print(data)
        # ser.close()
        return (data)

    except Exception as e:
        print("[X]  SENSOR ID: " + str(sys.argv[1]) + " " + e)
        return [0, 0, 0, 0, 0]


def check_is_switch():
    global pump_state
    global is_connect
    global current_state
    try:
        try:
            mycursor.execute(
                "SELECT content FROM configurations WHERE name LIKE 'pump_state'")
            pump_state = mycursor.fetchone()[0]
            # print(pump_state)
            is_connect = True
            if pump_state != current_state and is_connect:
                current_state = pump_state
                if (current_state == '0'):
                    ser.write(b'switch,0,*')
                elif (current_state == '1'):
                    ser.write(b'switch,1,*')
                data = ser.readline().decode('utf-8').strip('/r/n')
                print(data)
                return (data)
            return int(pump_state[0])
        except Exception as e4:
            pump_state = ""
            is_connect = False
            print(e4)
        print('Pump Connect:', is_connect)
    except Exception as e:
        print(e)


def set_pwm():
    global current_speed
    mycursor.execute(
        "SELECT content FROM configurations WHERE name LIKE 'pump_speed'")
    pump_speed = mycursor.fetchone()[0]
    is_connect = True
    if pump_speed != current_speed and is_connect:
        current_speed = pump_speed
        data = 'setPWM,' + str(current_speed) + ',*'
        kutip = b''
        returnval = kutip.decode('utf-8') + data
        ser.write(returnval.encode('ascii'))
        time.sleep(1)
        ser.write(returnval.encode('ascii'))
    data = ser.readline().decode('utf-8').strip('/r/n')
    print(data)


# check_is_switch()

try:
    while True:
        set_pwm()
        check_is_switch()
        try:
            val = read_concentration()
            print(val)
        except Exception as e3:
            print(" : error val = connect_sensor()")
            print(e3)

        try:
            SENSOR = "MC," + str(val) + ",END,"
            # MC,NO2,SO2,O3,CO2,VOC,PWM,Volt,Current,Power,WD(derajat)
            update_sensor_value(str(sys.argv[1]), str(SENSOR))
        except Exception as e3:
            print("SENSOR value error")
        time.sleep(1)

except Exception as e:
    print(e)
