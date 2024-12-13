import serial
import struct
import time
import db
import sys

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
    
# Define constants
SERIAL_PORT = '/dev/ttyUSBHC'  # Replace with your serial port
BAUDRATE = 9600
SLAVE_ADDRESS = 1  # Modbus slave address (e.g., 1)
REGISTER_START = 40021  # Register address (e.g., hc)
REGISTER_COUNT = 2  # Number of registers to read (32-bit float needs 2 registers)

# Modbus function codes
FUNC_READ_HOLDING_REGISTERS = 0x03  # Function code to read holding registers

# Helper function to calculate CRC16 for Modbus RTU
def calculate_crc(data):
    crc = 0xFFFF
    for byte in data:
        crc ^= byte
        for _ in range(8):
            if crc & 0x0001:
                crc = (crc >> 1) ^ 0xA001
            else:
                crc >>= 1
    return struct.pack('<H', crc)

# Function to read holding registers using Modbus RTU
def read_modbus_registers(slave_address, start_address, count):
    # Construct Modbus RTU request frame
    request = bytearray()
    request.append(slave_address)  # Slave address
    request.append(FUNC_READ_HOLDING_REGISTERS)  # Function code 0x03
    request.append((start_address >> 8) & 0xFF)  # High byte of register address
    request.append(start_address & 0xFF)  # Low byte of register address
    request.append((count >> 8) & 0xFF)  # High byte of register count
    request.append(count & 0xFF)  # Low byte of register count
    
    # Append CRC (calculated using the request data)
    crc = calculate_crc(request)
    request.extend(crc)
    
    # Send the request via serial port
    with serial.Serial(SERIAL_PORT, BAUDRATE, timeout=1) as ser:
        ser.write(request)  # Send request frame
        
        # Read the response
        response = ser.read(5 + 2 * count)  # 5 header bytes + 2 bytes per register

        if len(response) < 5:
            print("Error: Invalid response")
            return None
        
        # Check the CRC of the response
        if calculate_crc(response[:-2]) != response[-2:]:
            print("Error: Invalid CRC in response")
            return None
        
        # Extract the data (the data part starts from byte 3 to byte 3 + 2 * count)
        data = response[3:3 + 2 * count]
        if len(data) == 4:  # 2 registers (32-bit float)
            swapped_data = data[2:4] + data[0:2]
            # Convert the 4 bytes directly to a float (big-endian)
            return struct.unpack('!f', swapped_data)[0]  # Convert to float using IEEE 754 format
        
        return None

def main():
    driver = get_driver()

    id = driver['id']
    motherboard = get_data_from_motherboard('read_hc')
    id_pin = motherboard[0]['id']
    pin = str(id) + str(id_pin)
    # Main loop to continuously read from the Modbus device
    while True:
        try:
            # Read the HC data (Register 40041 -> Address 40041 - 40001 = 40)
            hc = read_modbus_registers(SLAVE_ADDRESS, 40041 - 40001, REGISTER_COUNT)
            
            if hc:
                # concentration (mg/m3) = 0.0409 x concentration (ppm) x molecular weight
                # molecular weight = 44 g	/mo
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
