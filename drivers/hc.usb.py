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


def read_modbus_registers(client, slave_address, start_address, count):
    try:
        result = client.read_holding_registers(address=start_address, count=count, slave=slave_address)
        if result.isError():
            print(f"Error reading from Modbus slave {slave_address}.")
            return None
        return result
    except ModbusException as e:
        print(f"Modbus Exception: {e}")
        return None
    except Exception as e:
        print(f"Error: {e}")
        return None


def main():
    id = 2
    pin = 20
    while True:
        with ModbusSerialClient(
                port=port,
                baudrate=baudrate,
                parity=parity,
                stopbits=stopbits,
                bytesize=bytesize,
                timeout=timeout) as client:

            if not client.connect():
                print(f"Unable to connect to Modbus device at {port}")
                time.sleep(1)
                continue

            result = read_modbus_registers(client, SLAVE_SENSORPM, 20, 22)

            if result:
                hc = round(struct.unpack('>f', struct.pack('>HH', result.registers[21], result.registers[20]))[0], 2)

                if hc:
                    ppm_hc = hc / 1000
                    mg_hc = 0.0409 * ppm_hc * 44
                    value = f"HC;{hc};{mg_hc};END_HC"
                    db.update_sensor_values(id, pin, value)
                    print(f"Read Pin {pin}")
                else:
                    db.update_sensor_values(id, pin, -999)
                    print(f"Pin {pin} Error")

        time.sleep(1)


if __name__ == "__main__":
    main()
