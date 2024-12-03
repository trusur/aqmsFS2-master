from pymodbus.client import ModbusSerialClient
from pymodbus.exceptions import ModbusException
import struct

def read_sensor_hc():
    # Konfigurasi MODBUS Serial
    client = ModbusSerialClient(
        port='/dev/ttyUSB0',  # Ganti dengan port serial perangkat Anda
        baudrate=9600,
        parity='N',           # Parity None
        stopbits=1,           # Stop bits 1
        bytesize=8,           # Data bits 8
        timeout=1             # Timeout dalam detik
    )

    # Alamat register untuk sensor HC (Base 40001 -> Address 40041 => Offset 40)
    register_address = 40

    try:
        # Buka koneksi
        if not client.connect():
            print("Gagal terhubung ke perangkat MODBUS")
            return

        # Membaca dua register (32-bit floating-point membutuhkan 2 register)
        response = client.read_holding_registers(register_address, 2)
        if response.isError():
            print(f"Error membaca register: {response}")
        else:
            # Gabungkan 2 register menjadi float (IEEE 754)
            raw = struct.pack('>HH', response.registers[0], response.registers[1])
            hc_value = struct.unpack('>f', raw)[0]
            print(f"Sensor HC: {hc_value:.2f} PPB")
    except ModbusException as e:
        print(f"Kesalahan MODBUS: {e}")
    except Exception as e:
        print(f"Terjadi kesalahan: {e}")
    finally:
        # Tutup koneksi
        client.close()

if __name__ == "__main__":
    read_sensor_hc()
