from pymodbus.client import ModbusSerialClient as ModbusClient  # versi pymodbus 3.x

# Membuat koneksi ke port serial (misalnya /dev/ttyUSB1)
client = ModbusClient(
    method='rtu',       # Modbus RTU
    port='/dev/ttyUSB1',  # Port serial USB yang digunakan
    baudrate=9600,       # Baudrate
    stopbits=1,          # Stop bits
    parity='N',          # Parity
    bytesize=8           # Data bits (bytesize)
)

# Coba membuka koneksi
if client.connect():
    print("Koneksi berhasil!")
    # Membaca data dari register (misalnya, HC - 40041)
    rr = client.read_holding_registers(40041, 2, unit=0x01)
    if rr.isError():
        print("Terjadi kesalahan saat membaca register.")
    else:
        print(f"Data register 40041: {rr.registers}")
else:
    print("Gagal membuka koneksi.")

# Menutup koneksi
client.close()
