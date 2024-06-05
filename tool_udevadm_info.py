from __future__ import print_function
import sys
import time
import serial
import subprocess
import glob
import db_connect

mydb = db_connect.connecting()
mycursor = mydb.cursor()

def serial_ports():
    if sys.platform.startswith('win'):
        ports = ['COM%s' % (i + 1) for i in range(256)]
    elif sys.platform.startswith('linux') or sys.platform.startswith('cygwin'):
        ports = glob.glob('/dev/tty[A-Za-z]*')
    elif sys.platform.startswith('darwin'):
        ports = glob.glob('/dev/tty.*')
    else:
        raise EnvironmentError('Unsupported platform')

    result = []
    for port in ports:
        try:
            s = serial.Serial(port)
            s.close()
            result.append(port)
        except (OSError, serial.SerialException):
            pass
    return result
    
mycursor.execute("TRUNCATE TABLE serial_ports")
mydb.commit()

for port in serial_ports():
    print("Adding port " + port)
    port_desc = ""
    id_product = ""
    id_vendor = ""
    serialno = ""

    if sys.platform.startswith('linux') or sys.platform.startswith('cygwin'):
        p = subprocess.Popen("udevadm info -a -n " + port + " | grep '{manufacturer}\|{product}\|{serial}\|{idVendor}\|{idProduct}' -m5", stdout=subprocess.PIPE, shell=True)
        (output, err) = p.communicate()
        p_status = p.wait()
        port_desc = output.decode("utf-8").replace('"',"")
        port_descs = port_desc.split("{serial}==")
        port_descs = port_descs[1].split("\n")
        serialno = port_descs[0]
        port_descs = port_desc.split("{idVendor}==")
        port_descs = port_descs[1].split("\n")
        id_vendor = port_descs[0]
        port_descs = port_desc.split("{idProduct}==")
        port_descs = port_descs[1].split("\n")
        id_product = port_descs[0]

    try:
        mycursor.execute("INSERT INTO serial_ports (port,id_product,id_vendor,serial,description) VALUES ('" + port + "','" + id_product + "','" + id_vendor + "','" + serialno + "','" + port_desc + "')")
        mydb.commit()
    except Exception as e:
        print(e)
        None
