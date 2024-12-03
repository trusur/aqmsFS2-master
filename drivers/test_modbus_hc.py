from pymodbus.client import ModbusSerialClient as ModbusClient
import struct

# Initialize Modbus client (RS485, Modbus-RTU)
client = ModbusClient(method='rtu', port='/dev/ttyUSB0', baudrate=9600, stopbits=1, parity='N', bytesize=8)

# Connect to the Modbus server
connection = client.connect()
if not connection:
    print("Failed to connect to the Modbus device")
    exit()

# Read HC parameter from address 40041 (register 0x9D05)
# Register 40041 is the 40000-based address, so we subtract 40001 to get the correct Modbus register address
result = client.read_holding_registers(0x9D05, 2, unit=0x01)

if result.isError():
    print("Error reading register")
else:
    # Modbus returns the data as two 16-bit registers (32-bit floating point)
    registers = result.registers
    # Combine the two 16-bit registers into a single 32-bit value (big-endian)
    hc_raw = struct.unpack('>f', struct.pack('>HH', registers[0], registers[1]))[0]
    
    print(f"HC (Hex) Raw Value: {hc_raw:.2f} PPB")

# Close the connection
client.close()
