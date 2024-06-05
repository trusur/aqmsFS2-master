from __future__ import print_function
from pymodbus.client.sync import ModbusSerialClient
import sys
import minimalmodbus
import serial
import time
import datetime
import struct
import subprocess
import db_connect

is_MEMBRAPOR_connect = False
is_zero_calibrating = False
zerocal_finished_at = ""
concentration0 = "0"
concentration1 = "0"
concentration2 = "0"
concentration3 = "0"

try:
    mydb = db_connect.connecting()
    mycursor = mydb.cursor()
    
    mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
    sensor_reader = mycursor.fetchone()
        
except Exception as e: 
    print("[X]  MEMBRAPOR ID: " + str(sys.argv[1]) + " " + e)
    
def dectofloat(dec0,dec1):
    try:
        if(int(dec0) == 0 and int(dec1) == 0):
            return "0"
        
        hexvalue1 = str(hex(int(dec0))).replace("0x","")
        hexvalue2 = str(hex(int(dec1))).replace("0x","")
        hexvalue = hexvalue1.rjust(4, "0") + hexvalue2.rjust(4, "0")
        #print(str(hexvalue1) + ":" + str(hexvalue2) + " ==> " + hexvalue)
        if(len(hexvalue) == 8):
            #print("   ===> " +str(struct.unpack('!f', bytes.fromhex(hexvalue))[0]))
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
            mycursor.execute("SELECT id FROM sensor_values WHERE sensor_reader_id = '" + str(sensor_reader_id) + "' AND pin = '0'")
            sensor_value_id = mycursor.fetchone()[0]
            mycursor.execute("UPDATE sensor_values SET value = '" + str(value) + "' WHERE id = '" + str(sensor_value_id) + "'")
            mydb.commit()
        except Exception as e:
            mycursor.execute("INSERT INTO sensor_values (sensor_reader_id,pin,value) VALUES ('" + str(sensor_reader_id) + "','0','" + str(value) + "')")
            mydb.commit()
    except Exception as e2:
        print("Error update_sensor_value")
        print(e2)
        return None

def connect_membrapor():
    global is_MEMBRAPOR_connect
    try:
        mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
        sensor_reader = mycursor.fetchone()
    
        rs485=minimalmodbus.Instrument(sensor_reader[0],1,'rtu',False)
        rs485.serial.baudrate=sensor_reader[1]
        rs485.serial.parity=serial.PARITY_EVEN
        rs485.serial.bytesize=8
        rs485.serial.stopbits=1
        rs485.serial.timeout=5

        try:
            regConcentration = rs485.read_registers(1000,8,3)
        except Exception as e2:
            regConcentration = [0, 0, 0, 0, 0, 0, 0, 0]
            
        try:
            regVoltage = rs485.read_registers(1010,8,3)
        except Exception as e2:
            regVoltage = [0, 0, 0, 0, 0, 0, 0, 0]
            
        try:
            regTemp = rs485.read_registers(1070,8,3)
        except Exception as e2:
            regTemp = [0, 0, 0, 0, 0, 0, 0, 0]
        
        
        if(is_MEMBRAPOR_connect == False):
            is_MEMBRAPOR_connect = True
            print("[V] MEMBRAPOR " + sensor_reader[0] + " CONNECTED")
            
        try:
            return regConcentration + regVoltage + regTemp
        except Exception as e2:
            return [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
        
    except Exception as e:
        print("[X]  MEMBRAPOR ID: " + str(sys.argv[1]) + " " + e)
        return [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]

def zeroing():
    global is_zero_calibrating
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
        
        print("Zeroing...")
        is_zero_calibrating = False
        rs485.write_registers(1200,[0,0,0,0])
        time.sleep(1)
        rs485.write_registers(1220,[0,0,0,0])
        time.sleep(3)
        # rs485.write_registers(1210,[0,0,0,0])
        # time.sleep(3)
        
        
        mycursor.execute("SELECT content FROM configurations WHERE name LIKE 'calibrator_name'")
        calibrator_name = mycursor.fetchone()[0]
        mycursor.execute("SELECT content FROM configurations WHERE name LIKE 'zerocal_started_at'")
        zerocal_started_at = mycursor.fetchone()[0]
        mycursor.execute("SELECT content FROM configurations WHERE name LIKE 'zerocal_finished_at'")
        zerocal_finished_at = mycursor.fetchone()[0]
        mycursor.execute("SELECT value FROM sensor_values WHERE sensor_reader_id = '" + sys.argv[1] + "' AND pin=0")
        value = mycursor.fetchone()[0]
        
        mycursor.execute("INSERT INTO calibrations (calibrator_name,started_at,finished_at,sensor_reader_id,value) VALUES ('" + calibrator_name + "','" + zerocal_started_at + "','" + zerocal_finished_at + "','" + sys.argv[1] + "','" + value + "')")
        mydb.commit()
        
        
        mycursor.execute("UPDATE configurations SET content = '0' WHERE name LIKE 'is_zerocal'")
        mydb.commit()
        mycursor.execute("UPDATE configurations SET content = '' WHERE name LIKE 'zerocal_started_at'")
        mydb.commit()
        mycursor.execute("UPDATE configurations SET content = '' WHERE name LIKE 'zerocal_finished_at'")
        mydb.commit()
        
        return True
        
    except Exception as e:
        print("Error zeroing")
        print(e)
        return None

def check_is_span():
    try:
        try:
            mycursor.execute("SELECT content FROM configurations WHERE name LIKE 'setSpan'")
            setSpan = mycursor.fetchone()[0]        
        except Exception as e4:
            setSpan = "";
            print("setSpan configurations not found")

        setSpans = setSpan.split(";")
        # print("setSpan : " + setSpan)
        
        if(str(setSpans[0]) == str(sys.argv[1])):        
            mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
            sensor_reader = mycursor.fetchone()
        
            rs485=minimalmodbus.Instrument(sensor_reader[0],1)
            rs485.serial.baudrate=sensor_reader[1]
            rs485.serial.parity=serial.PARITY_EVEN
            rs485.serial.bytesize=8
            rs485.serial.stopbits=1
            rs485.mode=minimalmodbus.MODE_RTU
            rs485.serial.timeout=0.2
        
            port = int(setSpans[1])
            span = int(setSpans[2])
            spanAddress = 1230 + (2*port);
            spanDecs = str(struct.unpack("HH", struct.pack("f", span))).replace("(","").replace(")","")
            spanDecs = spanDecs.split(", ")
            print(spanDecs)
            
            mycursor.execute("UPDATE configurations SET content = '' WHERE name LIKE 'setSpan'")
            mydb.commit()
            
            print("Spaning...")
            print("Port : " + str(port))
            print("Span Address : " + str(spanAddress))
            print("Span Concentration: " + str(span))
            rs485.write_registers(1200,[0,0,0,0])
            time.sleep(1)
            print("Span Calibration Started")
            read_calibrated = rs485.read_registers(spanAddress,2,3)
            print("read calibrated before: " + str(read_calibrated))
            rs485.write_registers(spanAddress,[int(spanDecs[0]),int(spanDecs[1])])
            print("Span Calibration writing")
            time.sleep(1)
            read_calibrated = rs485.read_registers(spanAddress,2,3)
            print("read calibrated after: " + str(read_calibrated))
            rs485.write_registers(1210,[0,0,0,0])
            print("Span Calibration Ended")
            time.sleep(1)
        
    except Exception as e:
        print("Error check_is_span")
        print(e)
        return None
        

try:
    while True:
        try:
            check_is_span()
            try:
                mycursor.execute("SELECT content FROM configurations WHERE name LIKE 'is_zerocal'")
                is_zerocal = mycursor.fetchone()[0]
            except Exception as e4:
                is_zerocal = "0"
                print("is_zerocal configurations not found")
            
            try:
                mycursor.execute("SELECT content FROM configurations WHERE name LIKE 'zerocal_finished_at'")
                zerocal_finished_at = mycursor.fetchone()[0]
            except Exception as e4:
                zerocal_finished_at = "";
                print("zerocal_finished_at configurations not found")
            
            try:
                mycursor.execute("SELECT content FROM configurations WHERE name LIKE 'is_valve_calibrator'")
                is_valve_calibrator = str(mycursor.fetchone()[0])
            except Exception as e4:
                is_valve_calibrator = "0";
                print("is_valve_calibrator configurations not found")
                
            # print(is_zerocal + " : " + zerocal_finished_at)
            
            if(int(is_valve_calibrator) == 1 and int(is_zerocal) == 1 and zerocal_finished_at != ""):
                is_zero_calibrating = True
                    
            if(int(is_valve_calibrator) == 0 and int(is_zerocal) == 1):
                is_zero_calibrating = True
                
            if(is_zero_calibrating == True):
                try:
                    currenttime = datetime.datetime.now()
                    # print(zerocal_finished_at + " ||| " + str(currenttime)[0:19])
                    if(zerocal_finished_at <= str(currenttime)[0:19] or zerocal_finished_at == ""):
                        zeroing()
                        time.sleep(1)
                        
                except Exception as e3:
                    print(e3)
                    print("error zero calibrating")
        
            try:
                val = connect_membrapor()
            except Exception as e3:
                print(str(datetime.datetime.now()) + " : error val = connect_membrapor()")
                print(e3)
                
            # print(str(datetime.datetime.now()) + " : " + str(val))
            try:
                if(dectofloat(val[1],val[0]) != "0"):
                    concentration0 = dectofloat(val[1],val[0])
                    # hexvalue1 = str(hex(int(val[1]))).replace("0x","")
                    # hexvalue2 = str(hex(int(val[0]))).replace("0x","")
                    # print("0: " + hexvalue1 + ":" + hexvalue2 + " ==> " + concentration0)
                if(dectofloat(val[3],val[2]) != "0"):
                    concentration1 = dectofloat(val[3],val[2])
                    # hexvalue1 = str(hex(int(val[3]))).replace("0x","")
                    # hexvalue2 = str(hex(int(val[2]))).replace("0x","")
                    # print("1: " + hexvalue1 + ":" + hexvalue2 + " ==> " + concentration1)
                if(dectofloat(val[5],val[4]) != "0"):
                    concentration2 = dectofloat(val[5],val[4])
                    # hexvalue1 = str(hex(int(val[5]))).replace("0x","")
                    # hexvalue2 = str(hex(int(val[4]))).replace("0x","")
                    # print("2: " + hexvalue1 + ":" + hexvalue2 + " ==> " + concentration2)
                if(dectofloat(val[7],val[6]) != "0"):
                    concentration3 = dectofloat(val[7],val[6])
                    # hexvalue1 = str(hex(int(val[7]))).replace("0x","")
                    # hexvalue2 = str(hex(int(val[6]))).replace("0x","")
                    # print("3: " + hexvalue1 + ":" + hexvalue2 + " ==> " + concentration3)
            except Exception as e3:
                print("error decode concentrations")
                print(e3)
            
            try:
                MEMBRAPOR = "FS2_MEMBRASENS;" + concentration0 + ";" + concentration1 + ";" + concentration2 + ";" + concentration3 + ";" + dectofloat(val[9],val[8]) + ";" + dectofloat(val[11],val[10]) + ";" + dectofloat(val[13],val[12]) + ";" + dectofloat(val[15],val[14]) + ";" + dectofloat(val[17],val[16]) + ";" + dectofloat(val[19],val[18]) + ";" + dectofloat(val[21],val[20]) + ";" + dectofloat(val[23],val[22]) + ";END;"            
                update_sensor_value(str(sys.argv[1]),str(MEMBRAPOR))
            except Exception as e3:
                MEMBRAPOR = "FS2_MEMBRASENS;0;0;0;0;0;0;0;0;0;0;0;0;END;"
                print("MEMBRAPOR value error")
                
            # print(str(datetime.datetime.now()) + " ==> " + str(sys.argv[1]) + ":" + MEMBRAPOR)
        except Exception as e2:
            print(str(datetime.datetime.now()) + " => UNKNOWN ERROR FS2_MEMBRASENS !")
            print(e2)
            is_MEMBRAPOR_connect = False
            print("Reconnect MEMBRAPOR");
            update_sensor_value(str(sys.argv[1]),"0")
            
        time.sleep(1)

except Exception as e:
    print(e)