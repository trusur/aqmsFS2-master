import minimalmodbus
import serial

def connect_slave():
    try:
        instrument = minimalmodbus.Instrument('COM1', mode=minimalmodbus.MODE_RTU, slaveaddress=1,debug=True)
        instrument.serial.baudrate = 19200
        instrument.serial.parity = serial.PARITY_NONE
        instrument.serial.bytesize = 8
        instrument.serial.stopbits = 1
        instrument.serial.timeout = 0.2
        return instrument
    except Exception as e:
        print(e)
        return False
    
def start_pump(instrument):
    try:
        return instrument.write_bit(2048,1)
    except Exception as e:
        return False

def set_pump(instrument, type, value):
    try:
        address = None
        match type:
            case "a1":
                address = 2051
            case "a2":
                address = 2052
            case "b1":
                address = 2053
            case "b2":
                address = 2054
        instrument.write_bit(address,value)
        return True
    except Exception as e:
        print("Set Pump Error: ",e)
        return False
def set_span(instrument, type):
    try:
        address = None
        match type:
            case "so2":
                address = 2056
            case "no2":
                address = 2057
            case "co":
                address = 2058
            case "o3":
                address = 2059
            case "hc":
                address = 2060
        return instrument.write_bit(address,1)
    except Exception as e:
        print("Set Span Error: ",e)
        return False
def set_zero(instrument):
    try:
        return instrument.write_bit(2055,1)
    except Exception as e:
        print("Set Zero Error: ",e)
        return False

def stop_all(instrument):
    try:
        instrument.write_bit(2061,1)
    except Exception as e:
        print("Set Stop Error: ",e)

def get_pump_status(instrument):
    try:
        is_pump_connect = True if instrument.read_bit(1304,1) == 1 else False
        main_pump = 1 if instrument.read_bit(1305,1) == 1 else 0
        sec_pump = 3 if instrument.read_bit(1306,1) == 1 else 4
        return {
            "is_pump_connect": is_pump_connect,
            "main_pump": main_pump,
            "sec_pump": sec_pump
        }
    except Exception as e:
        print("Get Pump Status Error :",e)
        return False
def is_zero(instrument):
    try:
        return instrument.read_bit(2055,1) == 1
    except Exception as e:
        print("Get Is Zero Failed: ", e)
        return False
def is_span(instrument,type):
    try:
        address = None
        match type:
            case "so2":
                address = 2056
            case "no2":
                address = 2057
            case "co":
                address = 2058
            case "o3":
                address = 2059
            case "hc":
                address = 2060
        return instrument.read_bit(address,1) == 1
    except Exception as e:
        print("Get Span Error: ",e)
        return False

def get_vacuum(instrument):
    try:
        data = []
        index=0
        for i in range(101024, 101047,2):
            data[index] = instrument.read_input_registers(i, 2)
            index+=1
        return data
    except Exception as e:
        print("Get Vacuum Error :", e)
        return False
def get_valve(instrument):
    try:
        data = []
        index=0
        for i in range(1284, 1303):
            data[index] = instrument.read_bit(i) == 1
            index+=1
        return data
    except Exception as e:
        print("Get Valce Error :", e)
        return False

def main():
    instrument = connect_slave()
    if(instrument):
        try:
            # Usage
            # Start Pump
            start_pump(instrument)
            # Set Span
            set_span(instrument,"no2")
            # Set Zero
            set_zero(instrument)
            # Set Stop All Output
            stop_all(instrument)
            # Set Vacum
            print(get_vacuum(instrument))
            # Get Valves
            print(get_valve(instrument))
            
        except Exception as e:
            print("Error: ",e)


if __name__ == "__main__":
    main()