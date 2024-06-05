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


def check_sensor_type():
    global semea_tech_type
    types = [i for i in range(41)]
    types[2] = "CO"
    types[3] = "O2"
    types[4] = "H2"
    types[5] = "CH4"
    types[7] = "CO2"
    types[8] = "O3"
    types[9] = "H2S"
    types[10] = "SO2"
    types[11] = "NH3"
    types[12] = "CL2"
    types[13] = "ETO"
    types[14] = "HCL"
    types[15] = "PH3"
    types[17] = "HCN"
    types[19] = "HF"
    types[21] = "NO"
    types[22] = "NO2"
    types[23] = "NOX"
    types[24] = "CLO2"
    types[31] = "THT"
    types[32] = "C2H2"
    types[33] = "C2H4"
    types[34] = "CH2O"
    types[39] = "CH3SH"
    types[40] = "C2H3CL"

    try:
        mycursor.execute(
            "SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '" + sys.argv[1] + "'")
        sensor_reader = mycursor.fetchone()
        ser = serial.Serial(sensor_reader[0], sensor_reader[1], timeout=2)
        ser.write(b'\x3A\x10\x01\x00\x00\x01\x00\x00\x82\xB0')
        val = wrap(ser.read(6).hex(), 2)
        ser.close()
        semea_tech_type = types[int(val[3], 16)]
    except Exception as e:
        semea_tech_type = "N/A"


def read_concentration():
    global is_SENSOR_connect
    try:
        mycursor.execute(
            "SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '" + sys.argv[1] + "'")
        sensor_reader = mycursor.fetchone()

        ser = serial.Serial(sensor_reader[0], sensor_reader[1], timeout=5)
        ser.write(byte_formater("3A100300000600003293"))

        if (is_SENSOR_connect == False):
            is_SENSOR_connect = True
            print("[V] SENSOR ID: " + sensor_reader[0] + " CONNECTED")

        val = wrap(ser.read(20).hex(), 2)
        ser.close()
        ug = int(val[6]+val[7]+val[8]+val[9], 16)
        ppb = int(val[10]+val[11]+val[12]+val[13], 16)
        ppm = ppb/1000
        celcius = int(val[14]+val[15], 16)/100
        rh = int(val[16]+val[17], 16)/100
        return [ug, ppb, ppm, celcius, rh]

    except Exception as e:
        print("[X]  SENSOR ID: " + str(sys.argv[1]) + " " + e)
        return [0, 0, 0, 0, 0]


def check_is_zero():
    try:
        try:
            mycursor.execute(
                "SELECT content FROM configurations WHERE name LIKE 'is_zerocal'")
            is_zerocal = mycursor.fetchone()[0]
        except Exception as e4:
            is_zerocal = ""
            print("setSpan configurations not found")

        if (is_zerocal == str(sys.argv[1])):
            mycursor.execute(
                "SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '" + sys.argv[1] + "'")
            sensor_reader = mycursor.fetchone()
            ser = serial.Serial(
                sensor_reader[0], sensor_reader[1], timeout=2)
            zero_command = "3A1007000001000082D6"
            print(byte_formater(zero_command))
            print("Zeroing...")
            ser.write(byte_formater(zero_command))
            val = wrap(ser.read(10).hex(), 2)
            ser.close()
            print(val)
            mycursor.execute(
                "UPDATE configurations SET content = '' WHERE name LIKE 'is_zerocal'")
            mydb.commit()
    except Exception as e:
        print(e)


def check_is_span():
    try:
        try:
            mycursor.execute(
                "SELECT content FROM configurations WHERE name LIKE 'setSpan'")
            setSpan = mycursor.fetchone()[0]
        except Exception as e4:
            setSpan = ""
            print("setSpan configurations not found")

        setSpans = setSpan.split(";")  # 10;10
        if (str(setSpans[0]) == str(sys.argv[1])):
            mycursor.execute(
                "SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '" + sys.argv[1] + "'")
            sensor_reader = mycursor.fetchone()
            ser = serial.Serial(
                sensor_reader[0], sensor_reader[1], timeout=2)
            span_command = "3A1009000001" + \
                hex(int(setSpans[1])).split('0x')[1].rjust(4, '0')
            crc16 = crcmod.mkCrcFun(
                0x18005, rev=True, initCrc=0xFFFF, xorOut=0x0000)
            crc = wrap(
                str(hex(crc16(codecs.decode(span_command, "hex")))).replace("0x", ""), 2)
            span_command = span_command + crc[1] + crc[0]
            # print(byte_formater(span_command))
            print("Span Process...")

            ser.write(byte_formater(span_command))
            val = wrap(ser.read(6).hex(), 2)

            mycursor.execute(
                "UPDATE configurations SET content = ';" + setSpan + ";" + val[3] + "' WHERE name LIKE 'setSpan'")
            mydb.commit()

            checkspanresponse = True
            try:
                while checkspanresponse:
                    val = wrap(ser.read(6).hex(), 2)
                    # print(val)
                    try:
                        if (val[0].upper() == '3A'):
                            checkspanresponse = False
                    except Exception as e3:
                        None

                    time.sleep(1)
            except Exception as e2:
                print(e2)

            mycursor.execute(
                "UPDATE configurations SET content = '' WHERE name LIKE 'setSpan'")
            mydb.commit()
            ser.close()

    except Exception as e:
        print(e)


check_sensor_type()

try:
    while True:
        check_is_span()
        check_is_zero()
        try:
            val = read_concentration()
        except Exception as e3:
            print(str(datetime.datetime.now()) +
                  " : error val = connect_sensor()")
            print(e3)

        try:
            SENSOR = "SEMEATECH;" + semea_tech_type + ";" + str(val[0]) + ";" + str(
                val[1]) + ";" + str(val[2]) + ";" + str(val[3]) + ";" + str(val[4]) + ";END;"
            # print(SENSOR)
            update_sensor_value(str(sys.argv[1]), str(SENSOR))
        except Exception as e3:
            SENSOR = "SEMEATECH;;0;0;0;0;0;END"
            print("SENSOR value error")
        time.sleep(1)

except Exception as e:
    print(e)
