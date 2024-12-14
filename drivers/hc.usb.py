import struct
import time
import db
from pymodbus.client import ModbusSerialClient
from pymodbus.exceptions import ModbusException

SLAVE_SENSORPM = 1  # Sensor Gas & PM
port = '/dev/ttyUSBHC'
baudrate = 9600
parity = 'N'
stopbits = 1
bytesize = 8
timeout = 3

client = ModbusSerialClient(
    port=port,
    baudrate=baudrate,
    parity=parity,
    stopbits=stopbits,
    bytesize=bytesize,
    timeout=timeout,
)

def main():
    id = 2
    pin = 20

    if not client.is_open():
        if not client.connect():
            print("Failed to connect to Modbus device!")
            return

    # Main loop untuk terus membaca dari perangkat Modbus
    while True:
        try:
            # Baca register Modbus (alamat mulai dari 20, total register = 22)
            result = client.read_holding_registers(address=20, count=22, slave=SLAVE_SENSORPM)
            if result.isError():
                print(f"Error reading from Modbus slave {SLAVE_SENSORPM}.")
                time.sleep(1)
                continue

            # Ambil data untuk HC (Cahaya Hidrokarbon) dari register 20 dan 21
            hc = round(struct.unpack('>f', struct.pack('>HH', result.registers[21], result.registers[20]))[0], 2)

            if hc:
                ppm_hc = hc / 1000
                mg_hc = 0.0409 * ppm_hc * 44
                value = f"HC:{hc}:{mg_hc};END_HC"
                db.update_sensor_values(id, pin, value)
                print(f"Read Pin {pin}")
            else:
                print(f"Pin {pin} Error")

        except Exception as e:
            print(f"Error: {e}")

        # Tunggu sebentar sebelum membaca lagi
        time.sleep(1)


if __name__ == "__main__":
    main()
