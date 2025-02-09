import minimalmodbus
import serial
import time
import struct
import db
import math
from datetime import datetime
from pymodbus.client import ModbusSerialClient

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

SLAVE_SENSORPM = 1 # Sensor Gas & PM
SLAVE_SENSORWEATHER = 2 # SensorCuaca
SLAVE_PLC = 3 # PLC
port = '/dev/ttyUSBHC' 
baudrate = 9600
parity = 'N'
stopbits = 1
bytesize = 8
timeout=2

client = ModbusSerialClient(
    port=port,
    baudrate=baudrate,
    parity=parity,
    stopbits=stopbits,
    bytesize=bytesize,
    timeout=timeout,
)

def parsefloat(text):
    try:
        num = float(text)
        if not math.isnan(num):
            return num
    except Exception:
        return None
def read_float_swap(instrument, address):
    try:
        registers = instrument.read_registers(address, 2)
        bytes_ab = registers[0].to_bytes(2, byteorder='big')
        bytes_cd = registers[1].to_bytes(2, byteorder='big')
        
        swapped = bytes_cd + bytes_ab
        value = struct.unpack('>f', swapped)[0]
        return round(value, 2)
    except Exception as e:
        return None
        raise Exception(f"Error reading float: {str(e)}")

# Running Main Function
def main():
    # JUST TESTING TO PLC
    # instrument_plc = minimalmodbus.Instrument(port=port, slaveaddress=SLAVE_PLC)
    # instrument_plc.serial.baudrate = 9600
    # instrument_plc.serial.bytesize = serial.EIGHTBITS
    # instrument_plc.serial.parity = serial.PARITY_NONE
    # instrument_plc.serial.stopbits = serial.STOPBITS_ONE  

    # print(f"Connected on port : {port} slave id : {SLAVE_PLC}")

    # while True:
    #     try:
    #         data = instrument_plc.write_bit(20, 1)
    #         if data == None:
    #             print(f"PLC : Pump On" )
    #             break
    #     except minimalmodbus.ModbusException:
    #         print("Trying to turn on pump")
    #     time.sleep(0.1)

    try:
        instrument = minimalmodbus.Instrument(port=port, slaveaddress=SLAVE_SENSORPM)
        instrument.serial.baudrate = baudrate
        instrument.serial.bytesize = serial.EIGHTBITS
        instrument.serial.parity = serial.PARITY_NONE
        instrument.serial.stopbits = serial.STOPBITS_ONE
        instrument.serial.timeout = 1
        print(f"Connected on port : {port} slave id : {SLAVE_PLC}")
    
        while True:
            print(f"Read Sensor Pin 1-7")
            for key, address in ADRESS.items():
                try:
                    value = read_float_swap(instrument, address)
                    fix_value = parsefloat(value)
                    # if  fix_value is  None:
                    #     raise ValueError(f"Invalid value for {key} at address {address}")
                        
                    sensor_value = f"{key};{fix_value};END_{key}"
                    db.update_sensor_values(1, PIN_MAP[key], sensor_value)

                except Exception as e:
                    db.update_sensor_values(1, PIN_MAP[key], -999)
                    print(f"{key} - Address {address} error")
                time.sleep(0.02)  
            
            print(f"Read waether Pin 8")
            resultweather = client.read_holding_registers(address=500, count=16, slave=SLAVE_SENSORWEATHER)
            if resultweather.isError():
                print(f"Error reading from slave id {SLAVE_SENSORWEATHER}.")
                time.sleep(1)  
                continue
        
            wsv = resultweather.registers[0] / 100 if len(resultweather.registers) > 0 else None # wind speed 1
            wav = resultweather.registers[3]  if len(resultweather.registers) > 3 else None # Wind Direction 2
            tmp = resultweather.registers[5] / 10 if len(resultweather.registers) > 5 else None  # Temperature value 3
            hum = resultweather.registers[4] / 10 if len(resultweather.registers) > 4 else None # Humidity value 4
            prs = resultweather.registers[9] if len(resultweather.registers) > 9 else None # Atmospheric pressure 5
            hpr = resultweather.registers[13] / 10 if len(resultweather.registers) > 13 else None # Optical Rainfall Rainfall Value 6
            rad = resultweather.registers[15] if len(resultweather.registers) > 15 else None  # Solar Radiation 7

            values = f"WEAHTER;{wsv};{wav};{tmp};{hum};{prs};{hpr};{rad};END_WEATHER"
            db.update_sensor_values(1,8,values)

            # print(f"--\nSelama {time.time() - start_time}")
            # print(f"Selesai {datetime.now().strftime('%Y-%m-%d %H:%M:%S.%f')[:-3]}")
            
    except Exception as e:
        print(f"Error koneksi: {e}")
    finally:
        # if 'instrument_plc' in locals() and instrument_plc.serial.is_open:
        #     instrument_plc.serial.close()
        if 'instrument' in locals() and instrument.serial.is_open:
            instrument.serial.close()
        if client:
            client.close()
        

if __name__ == "__main__":
    main()
