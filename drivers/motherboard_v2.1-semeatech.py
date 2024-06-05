from __future__ import print_function
from pyModbusTCP.client import ModbusClient
from textwrap import wrap
from datetime import datetime
import sys
import time
import struct
import db_connect
import os

is_MOTHERBOARD_connect = False

socket = None
current_state = -1
is_shutdown = 0
current_speed = -1
pump_state_addr = []
pump_speed_addr = []
pump_state_addr.insert(0, 200)
pump_state_addr.insert(1, 201)
pump_speed_addr.insert(0, 40141)
pump_speed_addr.insert(1, 40142)
sensors = ['pm10', 'pm25', 'no2', 'so2', 'o3', 'co', 'hc', 'h2s', 'nh3', 'ws', 'wd', 'pressure', 'temp', 'hum', 'sr', 'rain', 'pm10_flow', 'pm25_flow', 'batt_v', 'batt_i', 'is_shutdown', 'flow_winsen']
#gas 2 - 8

try:
    mydb = db_connect.connecting()
    mycursor = mydb.cursor()
    mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '" + sys.argv[1] + "'")
    sensor_reader = mycursor.fetchone()
    mycursor.execute("TRUNCATE sensor_values")
    mydb.commit()
except Exception as e:
    print("[X]  MOTHERBOARD ID: " + str(sys.argv[1]) + " " + e)

def dectofloat(dec0, dec1):
    try:
        if (int(dec0) == 0 and int(dec1) == 0):
            return "0"

        hexvalue1 = str(hex(int(dec0))).replace("0x", "")
        hexvalue2 = str(hex(int(dec1))).replace("0x", "")
        hexvalue = hexvalue1.rjust(4, "0") + hexvalue2.rjust(4, "0")
        # print(str(hexvalue1) + ":" + str(hexvalue2) + " ==> " + hexvalue)
        if (len(hexvalue) == 8):
            # print("   ===> " +str(struct.unpack('!f', bytes.fromhex(hexvalue))[0]))
            return str(struct.unpack('!f', bytes.fromhex(hexvalue))[0])
        else:
            return "0"
    except Exception as e:
        print("Error dectofloat")
        print(e)
        return "0"


def float_to_hex(f):
    try:
        hexs = wrap(str(hex(struct.unpack('<I', struct.pack('<f', f))[0])).replace('0x',''),4)
        return [int(hexs[0],16),int(hexs[1],16)]
    except Exception as e:
        print("Error float to hex")
        print(e)
        return "0"


def update_sensor_value(sensor_reader_id, pin, value):
    try:
        try:
            mycursor.execute("SELECT id FROM sensor_values WHERE sensor_reader_id = '" + str(sensor_reader_id) + "' AND pin = '" + str(pin) + "'")
            sensor_value_id = mycursor.fetchone()[0]
            mycursor.execute("UPDATE sensor_values SET value = '" + str(value) + "' WHERE id = '" + str(sensor_value_id) + "'")
            mydb.commit()
        except Exception as e:
            mycursor.execute("INSERT INTO sensor_values (sensor_reader_id,pin,value) VALUES ('" + str(sensor_reader_id) + "','" + str(pin) + "','" + str(value) + "')")
            mydb.commit()
    except Exception as e2:
        print("Error update_sensor_value")
        print(e2)
        return None


def connect_motherboard():
    try:
        mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '" + sys.argv[1] + "'")
        sensor_reader = mycursor.fetchone()

        c = ModbusClient(host=sensor_reader[0], port=int(
            sensor_reader[1]), debug=False, timeout=5, unit_id=1, auto_open=True, auto_close=False)
        return c

    except Exception as e:
        print("[X]  MOTHERBOARD ID: " + str(sys.argv[1]) + " " + e)


def read_sensors():
    try:
        regs = socket.read_input_registers(30000, 25)
        data = []
        # PM
        for i in range(0, 4, 2):
            data.insert(i, dectofloat(regs[i], regs[i+1]))
        # Gas
        for i in range(4, 18, 2):
            data.insert(i, int(regs[i]+regs[i+1]))
            # data.insert(i, dectofloat(regs[i+1],regs[i]))
        # Meteorology
        for i in range(18, 25, 1):
            data.insert(i, regs[i])
        regs = socket.read_input_registers(31006, 2)
        i = i+1
        data.insert(i, dectofloat(regs[1], regs[0]))  # pm10_flow
        regs = socket.read_input_registers(31014, 2)
        i = i+1
        data.insert(i, dectofloat(regs[1], regs[0]))  # pm25_flow
        i = i+1
        data.insert(i, socket.read_input_registers(31037, 1)[0])  # batt_v
        i = i+1
        data.insert(i, socket.read_input_registers(31038, 1)[0])  # batt_i
        i = i+1
        data.insert(i, socket.read_coils(101, 1)[0])  # is_shutdown
        i = i+1
        data.insert(i, socket.read_input_registers(32007, 1)[0])  # flow winsen (i (pin sensor)=19)
        return data
    except Exception as e:
        print("Error read input register")
        print(e)


def set_pump():
    global current_state, current_speed, pump_speed_addr, pump_state_addr
    try:
        mycursor.execute("SELECT content FROM configurations WHERE name = 'pump_test'")
        configuration = mycursor.fetchone()
        if (configuration and configuration[0] != "" and configuration[0] == "1"):
            print("Start Manual Pump")
            mycursor.execute("UPDATE configurations SET content='0' WHERE name = 'pump_test'")
            mydb.commit()

            socket.write_single_coil(203, 1)  # mode pump manual
            time.sleep(0.5)

            mycursor.execute("SELECT content FROM configurations WHERE name = 'pump_state'")
            configuration = mycursor.fetchone()
            if (configuration[0] != ""):
                pump_state = int(configuration[0])
            else:
                pump_state = current_state

            print("pump_state " + str(pump_state))
            #if (pump_state != current_state):
            current_state = pump_state
            socket.write_single_coil(pump_state_addr[current_state], 1)
            #for i, val in enumerate(sensors):
            #    update_sensor_value(str(sys.argv[1]), i, sensors[i] + ";" + str(0))
            time.sleep(30)
            
            #mycursor.execute("SELECT content FROM configurations WHERE name = 'pump_speed'")
            #configuration = mycursor.fetchone()
            #if (configuration[0] != ""):
            #    pump_speed = int(configuration[0])
            #else:
            #    pump_speed = current_speed

            #if (pump_speed != current_speed):
            #    current_speed = pump_speed
            #   socket.write_single_register(
             #       pump_speed_addr[current_state], pump_speed*10)
            #    time.sleep(0.5)
            
            print("Start Automatic Pump")
            socket.write_single_coil(203, 0)  # mode pump auto
            time.sleep(0.5)
    except Exception as e:
        print("set_pump error")


def set_zero():
    try:
        mycursor.execute("SELECT content FROM configurations WHERE name = 'set_zero'")
        configuration = mycursor.fetchone()
        if (configuration[0] == "1"):
            print("Set Zero Start")
            mycursor.execute("UPDATE configurations SET content='0' WHERE name = 'set_zero'")
            mydb.commit()

            print("write_single_register",40006, 31)
            socket.write_single_register(40006, 31)
            time.sleep(2)
            for i in range(40008, 40013, 1):
                print("write_single_register",i)
                socket.write_single_register(i, 0)
                time.sleep(2)
            print("write_single_register",40007, 31)
            socket.write_single_register(40007, 31)
            time.sleep(2)
            print("write_single_register",40006,0)
            socket.write_single_register(40006, 0)
            time.sleep(2)
            print("write_single_register",40007,0)
            socket.write_single_register(40007, 0)


            # for i in range(40008, 40013, 1):
            #     print("write_single_register",40006, pow(2,(i-40008)))
            #     socket.write_single_register(40006, pow(2,(i-40008)))
            #     time.sleep(1)
            #     print("write_single_register",i)
            #     socket.write_single_register(i, 0)
            #     time.sleep(1)
            #     print("write_single_register",40007, pow(2,(i-40008)))
            #     socket.write_single_register(40007, pow(2,(i-40008)))
            #     time.sleep(1)
            #     print("write_single_register",40006,0)
            #     socket.write_single_register(40006, 0)
            #     time.sleep(1)
            #     print("write_single_register",40007,0)
            #     socket.write_single_register(40007, 0)
            #     time.sleep(2)
            #     print("")

            print("Set Zero Done!")
    except Exception as e:
        print("set_zero error")


def set_span():
    try:
        mycursor.execute("SELECT content FROM configurations WHERE name = 'set_span'")
        _set_span = mycursor.fetchone()
        mycursor.execute("SELECT content FROM configurations WHERE name = 'port_span'")
        port_span = mycursor.fetchone()

        if (_set_span[0] != "0" and port_span[0] != "-1"):
            print("Set Span port:", port_span[0], "," , _set_span[0], "ppm")
            mycursor.execute("UPDATE configurations SET content='0' WHERE name = 'set_span'")
            mydb.commit()
            mycursor.execute("UPDATE configurations SET content='-1' WHERE name = 'port_span'")
            mydb.commit()
            set_span = float_to_hex(int(_set_span[0]))
            print("write_single_register",40006, pow(2,int(port_span[0])))
            socket.write_single_register(40006, pow(2,int(port_span[0])))
            time.sleep(2)
            print("write_multiple_registers",40013 + (int(port_span[0]) * 2), set_span)
            socket.write_multiple_registers(40013 + (int(port_span[0]) * 2), set_span)
            time.sleep(2)

            print("write_single_register",40023, pow(2,int(port_span[0])))
            socket.write_single_register(40023, pow(2,int(port_span[0])))
            time.sleep(2)
            print("write_single_register",40007, pow(2,int(port_span[0])))
            socket.write_single_register(40007, pow(2,int(port_span[0])))
            time.sleep(2)

            print("write_single_register",40006, 0)
            socket.write_single_register(40006, 0)
            time.sleep(2)
            print("write_single_register",40007, 0)
            socket.write_single_register(40007, 0)
            time.sleep(2)
            print("write_single_register",40023, 0)
            socket.write_single_register(40023, 0)
            print("")

    except Exception as e:
        print("set_span error" ,e)


def shutdown():
    global is_shutdown
    try:
        #mycursor.execute("SELECT content FROM configurations WHERE name = 'is_shutdown'")
        #configuration = mycursor.fetchone()
        #if (configuration[0] == "1"):
        #    mycursor.execute("UPDATE configurations SET content='0' WHERE name = 'is_shutdown'")
        #    mydb.commit()

        if (socket.read_coils(101, 1)[0] == True):
            print("shutdown requested")
            is_shutdown = 1
            socket.write_single_coil(101, 0)
            print("shutting down")
            time.sleep(5)
            os.system("shutdown now -h")

        #    else:
        #        socket.write_single_coil(101, 1)
        #        time.sleep(0.5)

    except Exception as e:
        print("is_shutdown configuration is not found")


try:
    while True:
        try:
            try:
                if (not is_MOTHERBOARD_connect):
                    socket = connect_motherboard()
                    test_read = socket.read_input_registers(30000, 1)
                    if (test_read != None):
                        is_MOTHERBOARD_connect = True
                        print("[V] MOTHERBOARD V2.0 " + sensor_reader[0] + " CONNECTED")
                        #for i, val in enumerate(sensors):
                        #    update_sensor_value(str(sys.argv[1]), i, sensors[i] + ";" + str(0))
                        time.sleep(30);
                        
                        #socket.write_single_coil(203, 1)  # mode pump manual
                        socket.write_single_coil(203, 0)  # mode pump auto
                        time.sleep(0.5)
                        socket.write_single_coil(202, 1)  # set dual pump
                        time.sleep(0.5)
                        socket.write_single_coil(100, 1)  # maintenance mode
                        time.sleep(0.5)
                        socket.write_multiple_registers(40102, [10, 10, 10, 10, 10])  # sampling time 1s
                        time.sleep(1);
                        socket.write_single_coil(100, 0)  # measurement mode
                        time.sleep(0.5)
                    else:
                        print("[X]  MOTHERBOARD ID: " + str(sys.argv[1]))
                    
                else:
                    if (is_shutdown == 0) :
                        data = read_sensors()
                        dt_string = "[" + datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                        #print(data)
                        for i, val in enumerate(data):
                            if (i >= 2 and i <=8):
                                data[i] = (float(data[i]) * 1) / 1000
                            dt_string += "," + str(data[i])
                            update_sensor_value(str(sys.argv[1]), i, sensors[i] + ";" + str(data[i]))
                        dt_string += "]"
                        # print(dt_string)

                        set_pump()
                        set_zero()
                        set_span()
                        shutdown()
                    else:
                        break
                    
                socket.close()
                
            except Exception as e:
                print(e)

        except Exception as e2:
            print(e2)

        time.sleep(1)

except Exception as e:
    print(e)
