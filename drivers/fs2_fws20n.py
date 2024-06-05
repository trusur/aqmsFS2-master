import usb.core
import time
import struct
import math
import datetime
import db_connect
import sys
from mysql.connector.constants import ClientFlag
import mysql.connector

# Run in as sudo
# sudo python3 fs2_fws20n.py <sensor reader id>
try:
    mydb = db_connect.connecting()
    mycursor = mydb.cursor()    
    print("[V] WS FWS20N Database CONNECTED")
except Exception as e: 
    print("[X]  WS FWS20N " + e)

def update_sensor_value(sensor_reader_id,value):
    try:
        try:
            mycursor.execute("SELECT id FROM sensor_values WHERE sensor_reader_id = '"+ sensor_reader_id +"' AND pin = '0'")
            sensor_value_id = mycursor.fetchone()[0]
            mycursor.execute("UPDATE sensor_values SET value = '" + value + "' WHERE id = '" + str(sensor_value_id) + "'")
            mydb.commit()
        except Exception as e:
            mycursor.execute("INSERT INTO sensor_values (sensor_reader_id,pin,value) VALUES ('" + sensor_reader_id + "','0','" + value + "')")
            mydb.commit()
            print(e)
    except Exception as e2:
        print(e2)
        return None

VENDOR = 0x1941
PRODUCT = 0x8021
WIND_DIRS = [0, 22.5, 45, 67.5, 90, 112.5, 135, 157.5, 180, 202.5, 225, 247.5, 270, 292.5, 315, 337.5]
is_WS_connect = False
rain = 0
lastrain = 0
i_raindata = 0
last_time = 0
current_time = 0
i_dev_reset = 0;

def time_to_min(hour, minute):
    return hour*60+minute

def open_ws():
    global is_WS_connect
    usb_device = usb.core.find(idVendor=VENDOR, idProduct=PRODUCT)
    if usb_device is None:
        raise ValueError('Device not found')
    
    try:
        usb_device.get_active_configuration()

        if usb_device.is_kernel_driver_active(0):
            usb_device.detach_kernel_driver(0)

        time.sleep(5)
        return usb_device
    except Exception as e:
        is_WS_connect = False
        return None
        


def read_block(device, offset):
    least_significant_bit = offset & 0xFF
    most_significant_bit = offset >> 8 & 0xFF
    # Construct a binary message
    tbuf = struct.pack('BBBBBBBB', 0xA1, most_significant_bit, least_significant_bit, 32, 0xA1, most_significant_bit, least_significant_bit, 32)
    timeout = 1000  # Milliseconds
    retval = device.ctrl_transfer(0x21, 0x09, 0x200, 0, tbuf, timeout)
    return device.read(0x81, 32, timeout)

try:
    current_time = time_to_min(time.localtime().tm_hour, time.localtime().tm_min)
    last_time = current_time
    while True:
        try:
            current_time = time_to_min(time.localtime().tm_hour, time.localtime().tm_min)
            if(current_time < last_time):
                last_time = 0
                
            if((current_time - last_time) > 60):
                last_time = current_time
                rain = 0
                lastrain = 0
                i_raindata = 0
        
            if (is_WS_connect == False):
                dev = open_ws()
                dev.set_configuration()

            fixed_block = read_block(dev, 0)

            if (fixed_block[0] != 0x55):
                raise ValueError('Bad data returned')
            else:
                is_WS_connect = True

            curpos = struct.unpack('H', fixed_block[30:32])[0]
            current_block = read_block(dev, curpos)

            indoor_humidity = current_block[1]
            tlsb = current_block[2]
            tmsb = current_block[3] & 0x7f
            tsign = current_block[3] >> 7
            indoor_temperature = (tmsb * 256 + tlsb) * 0.1

            if tsign:
                indoor_temperature *= -1

            outdoor_humidity = current_block[4]
            tlsb = current_block[5]
            tmsb = current_block[6] & 0x7f
            tsign = current_block[6] >> 7
            outdoor_temperature = (tmsb * 256 + tlsb) * 0.1

            if tsign:
                outdoor_temperature *= -1

            abs_pressure = struct.unpack('H', current_block[7:9])[0]*0.1	
            
            wind = current_block[9]
            wind_extra = current_block[11]
            wind_dir = current_block[12]

            total_rain = struct.unpack('H', current_block[13:15])[0]*0.3
            wind_speed = (wind + ((wind_extra & 0x0F) << 8)) * 0.36
            
            if(i_raindata > 1 and total_rain != lastrain):
                rain += total_rain - lastrain
            
            lastrain = total_rain;
            if(i_raindata < 2):
                i_raindata += 1

            WS = str(datetime.datetime.now()) + ";0;" + str(abs_pressure/33.8639) + ";" + str((indoor_temperature*9/5)+32) + ";" + str(indoor_humidity) + ";" + str((outdoor_temperature*9/5)+32) + ";" + str(round(wind_speed,2)) + ";" + str(round(wind_speed,2)) + ";" + str(WIND_DIRS[wind_dir]) + ";" + str(outdoor_humidity) + ";" + str(round(rain,2)) + ";0;0;0.0;0;" + str(round(rain,2)) + ";0;0"

            update_sensor_value(str(sys.argv[1]),WS)
            
                
        except Exception as e2:
            try:
                dev.reset()
                i_dev_reset += 1
                print("Dev Reset : " + str(i_dev_reset))
            except Exception as e3:
                print("e3 : " + str(e3))
            
            is_WS_connect = False
            WS = ';0;0;0;0;0;0;0;0;0;0;0;0;0.0;0;0;0;0'
            print("Reconnect WS FWS20N : " + str(e2));
            update_sensor_value(str(sys.argv[1]),WS)

        time.sleep(10)
	

except Exception as e:
    print(e)
