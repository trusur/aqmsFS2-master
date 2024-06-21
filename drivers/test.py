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
test = "SEMEATECH 0x01;NO2;58;0.002;28.50;79"
a = test.split(";")
print(a[1])
calibration = db.get_calibration(a[1])
print(calibration)