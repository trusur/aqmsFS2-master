import minimalmodbus
import serial
import time
import struct
import db
import math
from datetime import datetime

def parsefloat(text):
    try:
        num = float(text)
        if not math.isnan(num):
            return num
    except Exception:
        return None

PORT = '/dev/ttyUSBHC'
SLAVE_ID = 1
REGISTER_ADDRESS = 40
ADRESS = {
    "PM25" : 20, # 40021 - 40001
    "PM10" : 22, # 40023 - 40001
    "CO"  : 32, # 40033 - 40001
    "SO2"  : 34, # 40035 - 40001
    "NO2"  : 36, # 40037 - 40001
    "O3"  : 38, # 40039 - 40001
    "HC"  : 40, # 40041 - 40001
}

PIN_MAP = {
    "PM25": 1,
    "PM10": 2,
    "CO": 3,
    "SO2": 4,
    "NO2": 5,
    "O3": 6,
    "HC": 7
}

def read_float_swap(instrument, address):
    try:
        registers = instrument.read_registers(address, 2)
        bytes_ab = registers[0].to_bytes(2, byteorder='big')
        bytes_cd = registers[1].to_bytes(2, byteorder='big')
        
        swapped = bytes_cd + bytes_ab
        value = struct.unpack('>f', swapped)[0]
        return round(value, 2)
    except Exception as e:
        raise Exception(f"Error reading float: {str(e)}")

try:
    instrument = minimalmodbus.Instrument(port=PORT, slaveaddress=SLAVE_ID)
    instrument.serial.baudrate = 9600
    instrument.serial.bytesize = serial.EIGHTBITS
    instrument.serial.parity = serial.PARITY_NONE
    instrument.serial.stopbits = serial.STOPBITS_ONE
    instrument.serial.timeout = 1
    
    print(f"Berhasil terhubung ke {PORT}")
    
    while True:
        # start_time = time.time()
        #print(f"Mulai {datetime.now().strftime('%Y-%m-%d %H:%M:%S.%f')[:-3]}")
        for key, address in ADRESS.items():
            try:
                value = read_float_swap(instrument, address)
                fix_value = parsefloat(value)
        
                if  fix_value is  None:
                    raise ValueError(f"Invalid value for {key}")
                    
                sensor_value = f"{key};{fix_value};END_{key}"
                db.update_sensor_values(1, PIN_MAP[key], sensor_value)
            except Exception as e:
                db.update_sensor_values(1, PIN_MAP[key], -999)
                print(f"{key} - Address {key} error")
            time.sleep(0.02)  
            
        # print(f"--\nSelama {time.time() - start_time}")
        # print(f"Selesai {datetime.now().strftime('%Y-%m-%d %H:%M:%S.%f')[:-3]}")
        
except Exception as e:
    print(f"Error koneksi: {e}")

