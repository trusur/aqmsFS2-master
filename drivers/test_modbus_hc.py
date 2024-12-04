from pymodbus.client import ModbusSerialClient as ModbusClient
import struct

# Konfigurasi Modbus RTU
client = ModbusClient(port='/dev/ttyUSB1', baudrate=9600, stopbits=1, parity='N', bytesize=8)

# Tentukan alamat perangkat dan register yang ingin dibaca
device_address = 1  # Alamat perangkat Modbus (0x01)
registers = [40041] 

# Fungsi untuk membaca data
def read_registers():
    for reg in registers:
        # Konversi alamat register Modbus (misalnya 40021) menjadi format yang benar untuk Modbus
        reg_address = reg - 40001  # Mengurangi 40001 untuk mendapatkan alamat yang benar dalam perangkat
        # Baca 2 register 16-bit untuk mendapatkan data 32-bit
        result = client.read_holding_registers(reg_address, 2, unit=device_address)
        
        if result.isError():
            print(f"Error membaca register {reg}")
        else:
            # Gabungkan dua register 16-bit untuk mendapatkan nilai 32-bit
            data = result.registers
            # Gabungkan dua register menjadi satu 32-bit unsigned integer
            combined = (data[0] << 16) | data[1]
            
            # Konversi 32-bit unsigned integer menjadi float 32-bit (IEEE 754)
            # struct.pack('!I', combined) mengonversi ke format bytes
            float_value = struct.unpack('!f', struct.pack('!I', combined))[0]

            print(f"Register {reg} (32-bit float): {float_value:.2f}")

# Connect ke client
client.connect()

# Baca data
read_registers()

# Disconnect setelah selesai
client.close()
