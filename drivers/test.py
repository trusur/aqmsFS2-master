# from pymodbus.client import ModbusSerialClient
# from pymodbus.client import ModbusTcpClient
# # client = ModbusSerialClient(
# #     method="rtu",
# #     port="/dev/tty.usbserial-1140",
# #     baudrate=9600,
# #     bytesize=8,
# #     parity="N",
# #     stopbits=1,
# #     timeout=1,
# #     strict=True
# # )
# client = ModbusTcpClient(
#     host="127.0.0.1",
#     port=502,
#     timeout=1
# )

# print(client.connect())
# print(client.write_coil(address=1,value=True))
# init_address = 2048
# for i in range(99):
#     print(client.write_coil(address=init_address+i,value=False))
#     read_coils = client.read_coils(address=init_address)
#     print(read_coils.bits[0])
# # print(client.read_coils(address=1,slave=1).registers)
# # print(client.read_input_registers(address=1).registers)
import db
test = "SEMEATECH 0x1;NO2;1;1;26.70;58.16;SEMEATECH 0x1 END;SEMEATECH 0x2;SO2;81;31;25.77;61.27;SEMEATECH 0x2 END;SEMEATECH 0x3;O3;9;5;25.61;61.41;SEMEATECH 0x3 END;SEMEATECH 0x4;CO;258;226;25.33;62.05;SEMEATECH 0x4 END"
a = test.split(" END;")
for i in a:
    print(i)
print(a)