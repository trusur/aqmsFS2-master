from pymodbus.client import ModbusSerialClient as ModbusClient

# Menggunakan Modbus RTU dengan parameter yang sesuai
client = ModbusClient(
    port='/dev/ttyUSB1',  
    baudrate=9600,
    stopbits=1,
    parity='N',
    bytesize=8
)

# Membuka koneksi
client.connect()


result = client.read_holding_registers(40041, 2, unit=0x01)

if result.isError():
    print("Error:", result)
else:
    # Menampilkan hasil pembacaan
    print(f"HC: {result.registers[0]} {result.registers[1]}")

# Menutup koneksi setelah selesai
client.close()
