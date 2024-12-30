import struct
import time
import db
from pymodbus.client import ModbusSerialClient
from pymodbus.exceptions import ModbusException
import math

def parsefloat(text):
    try:
        num = float(text)
        if not math.isnan(num):
            return num
    except Exception:
        return None

SLAVE_SENSORPM = 1 # Sensor Gas & PM
SLAVE_SENSORWEATHER = 2 # SensorCuaca
SLAVE_ID = 3 # PLC

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

pin_map = {
    "PM25": 1,
    "PM10": 2,
    "CO": 3,
    "SO2": 4,
    "NO2": 5,
    "O3": 6,
    "HC": 7
}


def main():
    id = 1
    # Main loop to continuously read from the Modbus device
    while True:
        try:
            resultPump = client.write_coil(address="0", value="0", slave=SLAVE_ID)  

            if resultPump.isError():
                print("Error start Pump")
                time.sleep(1)
                continue
            else :
                print(resultPump)
                print ("Success menulis ke coil")
            # Read Modbus registers ( address start from 20 , total coil = 22 )
            result = client.read_holding_registers(address=20, count=22, slave=SLAVE_SENSORPM) 
            
            if result.isError():
                print(f"Error reading from Modbus slave {SLAVE_SENSORPM}.")
                time.sleep(1)  
                continue

            PM25 =  round(struct.unpack('>f', struct.pack('>HH', result.registers[1], result.registers[0]))[0], 2) 
            PM10 = round(struct.unpack('>f', struct.pack('>HH', result.registers[3], result.registers[2]))[0], 2)
            CO = round(struct.unpack('>f', struct.pack('>HH', result.registers[13], result.registers[12]))[0], 2)
            SO2 = round(struct.unpack('>f', struct.pack('>HH', result.registers[15], result.registers[14]))[0], 2)
            NO2 = round(struct.unpack('>f', struct.pack('>HH', result.registers[17], result.registers[16]))[0], 2)
            O3 = round(struct.unpack('>f', struct.pack('>HH', result.registers[19], result.registers[18]))[0], 2)
            HC = round(struct.unpack('>f', struct.pack('>HH', result.registers[21], result.registers[20]))[0], 2)
            
            print(f"Read Sensor 1 pin  1-7 ")
            # Update database based on parsed values
            for sensor, values in zip(['PM25', 'PM10', 'CO', 'SO2', 'NO2', 'O3', 'HC'], [PM25, PM10, CO, SO2, NO2, O3, HC]):
                value = parsefloat(values)
                if value is not None:
                    if sensor == 'HC':
                        ppm_hc = value / 1000
                        mg_hc = round(0.0409 * ppm_hc * 44, 2)
                        sensor_value = f"{sensor};{value};{mg_hc};END_{sensor}"
                    else:
                        sensor_value = f"{sensor};{value};END_{sensor}"
                    db.update_sensor_values(1, pin_map[sensor], sensor_value)
                    
                else:
                    db.update_sensor_values(1, pin_map[sensor], -999)
                    print(f"Pin {sensor} {pin_map[sensor]} Error")

            resultweather = client.read_holding_registers(address=501, count=16, slave=SLAVE_SENSORWEATHER)
            if resultweather.isError():
                print(f"Error reading from Modbus slave {SLAVE_SENSORWEATHER}.")
                time.sleep(1)  
                continue

            wsv = resultweather.registers[0]  # Atmospheric pressure 1
            wav = resultweather.registers[2]  # Wind Direction 360 2
            tmp = resultweather.registers[4] / 10  # Temperature value 3
            hum = resultweather.registers[3] / 10  # Humidity value 4
            prs = resultweather.registers[9]  # Atmospheric pressure 5
            hpr = resultweather.registers[13]  # Optical Rainfall Rainfall Value 6
            rad = resultweather.registers[15]  # Solar Radiation 7
            values = f"WEAHTER;{wsv};{wav};{tmp};{hum};{prs};{hpr};{rad};END_WEATHER"
            print(f"Read waether Pin 8")
            db.update_sensor_values(id,8,values)

        except Exception as e:
            for pin in pin_map.values():
                db.update_sensor_values(1, pin, -999)
            print(f"Error: {e}")
        
        # Wait before reading again
        time.sleep(1)


if __name__ == "__main__":
    main()
