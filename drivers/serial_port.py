import serial
import struct
import time

# Define constants
SERIAL_PORT = '/dev/ttyUSB1'  # Replace with your serial port
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
            # Convert the 4 bytes directly to a float (big-endian)
            return struct.unpack('!f', data)[0]  # Convert to float using IEEE 754 format
        
        return None

# Main loop to continuously read from the Modbus device
while True:
    try:
        # Read the HC data (Register 40041 -> Address 40041 - 40001 = 40)
        hc = read_modbus_registers(SLAVE_ADDRESS, 40041 - 40001, REGISTER_COUNT)
        
        if hc is not None:
            print(f"HC: {hc} ppb")
        else:
            print("Failed to read data or invalid response")
    
    except Exception as e:
        print(f"Error: {e}")
    
    # Wait before reading again
    time.sleep(1)
