from pymodbus.client.sync import ModbusSerialClient
import struct

def read_register(client, address, count=2):
    """
    Membaca register Modbus dan mengembalikan nilai floating-point 32-bit.
    :param client: ModbusSerialClient instance
    :param address: Alamat register (0-based offset)
    :param count: Jumlah register untuk dibaca (default: 2 untuk float 32-bit)
    :return: Nilai float
    """
    try:
        response = client.read_holding_registers(address, count, unit=1)
        if not response.isError():
            # Menggabungkan register ke float 32-bit
            registers = response.registers
            value = struct.unpack('>f', struct.pack('>HH', registers[0], registers[1]))[0]
            return value
        else:
            print(f"Error reading address {address}: {response}")
    except Exception as e:
        print(f"Exception occurred: {e}")
    return None

def main():
    # Konfigurasi koneksi Modbus
    client = ModbusSerialClient(
        method='rtu',
        port='/dev/ttyUSB1',  # Port USB
        baudrate=9600,
        parity='N',
        stopbits=1,
        bytesize=8,
        timeout=1
    )

    if client.connect():
        print("Connected to Modbus device")

        # Membaca sensor PM2.5 dan PM10 (alamat register dari manual)
        pm25_address = 20  # Alamat register untuk PM2.5 (40021 - 40001)
        pm10_address = 22  # Alamat register untuk PM10 (40023 - 40001)

        pm25_value = read_register(client, pm25_address)
        pm10_value = read_register(client, pm10_address)

        if pm25_value is not None:
            print(f"PM2.5: {pm25_value} µg/m³")
        if pm10_value is not None:
            print(f"PM10: {pm10_value} µg/m³")

        # Tutup koneksi Modbus
        client.close()
    else:
        print("Failed to connect to Modbus device")

if __name__ == "__main__":
    main()
