from pyModbusTCP.client import ModbusClient
import logging
def connect_motherboard():
    try:
        host = "192.168.123.150"
        port = 502
        return ModbusClient(host=host, port=port, debug=False, timeout=5, unit_id=1, auto_open=True, auto_close=False)
    except Exception as e:
        logging.error("Motherboard connection error: " + str(e))
        return False
    
def main():
        board = connect_motherboard()
        if board:
            board.open()
            # Activate Semeatech
            print(board.write_single_coil(303,1))
            print(board.write_holding_registers(303,1))
            print(board.read_input_registers(303,1))
        else:
            print("Motherboard connection error")
if __name__ == "__main__":
    main()