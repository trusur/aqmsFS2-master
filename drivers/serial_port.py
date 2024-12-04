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
        data = response[3:3 + 2 * count]  # Get the 2 registers
        
        # Convert the 16-bit register pairs to a 32-bit float (correct byte order)
        if len(data) == 4:  # 2 registers (32-bit float)
            # Combine the 2 registers into a single 32-bit integer
            msw = (data[0] << 8) | data[1]  # Most Significant Word
            lsw = (data[2] << 8) | data[3]  # Least Significant Word
            combined = (msw << 16) | lsw  # Combine into a 32-bit unsigned integer
            
            # Convert the 32-bit unsigned integer to a float (IEEE 754 format)
            return struct.unpack('!f', struct.pack('!I', combined))[0]
        
        return None

# Main loop to continuously read from the Modbus device
while True:
    try:
        # Read the hc data (Register 40021 -> Address 40021 - 40001 = 20)
        hc_5 = read_modbus_registers(SLAVE_ADDRESS, 40041 - 40001, REGISTER_COUNT)
        
        if hc_5 is not None:
            print(f"HC: {hc_5} µg/m³")
        else:
            print("Failed to read data or invalid response")
    
    except Exception as e:
        print(f"Error: {e}")
    
    # Wait before reading again
    time.sleep(1)
