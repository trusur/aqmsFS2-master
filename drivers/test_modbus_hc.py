from pymodbus.client import ModbusSerialClient as ModbusClient

# Konfigurasi Modbus RTU
client = ModbusClient(port='/dev/ttyUSB1', baudrate=9600, stopbits=1, parity='N', bytesize=8)

# Tentukan alamat perangkat dan register yang ingin dibaca
device_address = 1  # Address perangkat (0x01)
registers = [40041]  # PM2.5, PM10, CO (contoh)

# Fungsi untuk membaca data
def read_registers():
    for reg in registers:
        # Gunakan parameter 'slave' atau 'address' untuk menentukan alamat perangkat
        reg_address = reg - 40001  # Alamat register Modbus mulai dari 40001
        result = client.read_holding_registers(reg_address, 2, address=device_address)
        
        if result.isError():
            print(f"Error membaca register {reg}")
        else:
            # Gabungkan dua register 16-bit untuk mendapatkan nilai 32-bit
            data = result.registers
            floating_point_value = (data[0] << 16) | data[1]
            print(f"Register {reg}: {floating_point_value}")

# Connect ke client
client.connect()

# Baca data
read_registers()

# Disconnect setelah selesai
client.close()
