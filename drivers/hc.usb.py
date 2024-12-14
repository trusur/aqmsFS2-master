import struct
import time
import db
import sys
import serial
import struct
from pymodbus.client import ModbusSerialClient
from pymodbus.exceptions import ModbusException


SLAVE_SENSORPM = 1 # Sensor Gas & PM

port = '/dev/ttyUSBHC' 
baudrate = 9600
parity = 'N'
stopbits = 1
bytesize = 8
timeout=3

client = ModbusSerialClient(
    port=port,
    baudrate=baudrate,
    parity=parity,
    stopbits=stopbits,
    bytesize=bytesize,
    timeout=timeout,
)




def get_data_from_motherboard(type):
    try:
        cnx = db.connect()
        cursor = cnx.cursor(dictionary=True, buffered=True)
        cursor.execute("SELECT * FROM motherboard where type=%s and is_enable = 1 order by is_priority desc", (type,))
        rows = cursor.fetchall() if type == "read" else cursor.fetchone()
        cursor.close()
        cnx.close()
        return rows
    except Exception as e:
        print('Get Motherboards Error: ',e)
        return []
    finally:
        cursor.close()
        cnx.close()

def get_driver():
    try:
        filename = sys.argv[0].split("/")[-1]
        cnx = db.connect()
        cursor = cnx.cursor(dictionary=True)
        cursor.execute("select * from sensor_readers where driver='"+filename+"'")
        row = cursor.fetchone()
        cursor.close()
        cnx.close()
        return row
    except Exception as e:
        return None
    finally:
        cursor.close()
        cnx.close()
    

def main():
    # driver = get_driver()

    # id = driver['id']
    # motherboard = get_data_from_motherboard('read_hc')
    # id_pin = motherboard[0]['id']
    # pin = str(id) + str(id_pin)
    id = 2
    pin = 20
    # Main loop to continuously read from the Modbus device
    while True:
        try:
            # Read Modbus registers ( address start from 20 , total coil = 22 )

            result = client.read_holding_registers(address=20, count=22, slave=SLAVE_SENSORPM) 
        
            hc = round(struct.unpack('>f', struct.pack('>HH', result.registers[21], result.registers[20]))[0], 2) if not result.isError() else None
            
            if hc:
                ppm_hc = hc / 1000
                mg_hc  =  0.0409 * ppm_hc * 44
                value = f"HC:{hc}:{mg_hc};END_HC"
                db.update_sensor_values(id,pin, value)
                print(f"Read Pin {pin}")
            else:
                print(f"Pin {pin} Error")
        
        except Exception as e:
            print(f"Error: {e}")
        
        # Wait before reading again
        time.sleep(1)


if __name__ == "__main__":
    main()
