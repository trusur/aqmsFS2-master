from pyModbusTCP.client import ModbusClient
import logging
from textwrap import wrap
import struct
def connect_motherboard():
    try:
        host = "192.168.123.150"
        host = "localhost"
        port = 502
        return ModbusClient(host=host, port=port, debug=False, timeout=5, unit_id=1, auto_open=True, auto_close=False)
    except Exception as e:
        logging.error("Motherboard connection error: " + str(e))
        return False
def float_to_hex(f):
    try:
        hexs = wrap(str(hex(struct.unpack('<I', struct.pack('<f', f))[0])).replace('0x',''),4)
        return [int(hexs[0],16),int(hexs[1],16)]
    except Exception as e:
        print("Error float to hex")
        print(e)
        return "0"
def main():
        board = connect_motherboard()
        board.open()
        print(board.is_open)
        if board:
            # Activate Semeatech
            test = board.read_input_registers(3000, 1)
            print(test)
            try:
                print(board.write_single_coil(303,True))
                print(board.read_coils(303,1)[0])
                print(board.write_single_coil(303,False))
                print(board.read_coils(303,1)[0])
            except Exception as e:
                print(e)
        else:
            print("Motherboard connection error")
if __name__ == "__main__":
    main()