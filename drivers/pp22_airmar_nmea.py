#/etc/udev/rules.d/aqmsfs2.rules
#ACTION=="add", ATTRS{idVendor}=="0403", ATTRS{idProduct}=="cc60", RUN+="/sbin/modprobe ftdi_sio" RUN+="/bin/sh -c 'echo 0403 cc60 > /sys/bus/usb-serial/drivers/ftdi_sio/new_id'"
#sudo udevadm control --reload


from __future__ import print_function
import sys
import serial
import time
import db_connect

is_connect = False

try:
    mydb = db_connect.connecting()
    mycursor = mydb.cursor()
    
    mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
    sensor_reader = mycursor.fetchone()
except Exception as e: 
    print("[X]  Airmar Sensor ID: " + str(sys.argv[1]) + " " + e)
    
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
    except Exception as e2:
        return None
        
def connect_sensor():
    global is_connect
    try:
        mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
        sensor_reader = mycursor.fetchone()
        
        COM = serial.Serial(sensor_reader[0], sensor_reader[1])
        
        i = 0
        SENSOR = ""
        while i <= 12:
            SENSOR = SENSOR + str(COM.readline());
            i += 1
                
        if(SENSOR.count("$WIMDA") > 0 or SENSOR.count("$GPGGA") > 0):
            is_connect = True
            print("[V] Airmar Sensor " + sensor_reader[0] + " CONNECTED")
            return COM
        else:
            is_connect = False
            return None
            
    except Exception as e: 
        return None

COM = connect_sensor()    
connect_sensor()

try:
    while True :
        try:
            if(is_connect == False):
                COM = connect_sensor()
            
            i = 0
            SENSOR = ""
            while i <= 12:
                SENSOR = SENSOR + str(COM.readline());
                i += 1
                
            if(SENSOR.count("$WIMDA") > 0 or SENSOR.count("$GPGGA") > 0):
                try:
                    WIMDA = SENSOR.split("$WIMDA,")[1];
                    WIMDA = WIMDA.split("\\r\\n")[0];
                except Exception as x:
                    WIMDA = ""
                
                try:
                    WIMWV = SENSOR.split("$WIMWV,")[1];
                    WIMWV = WIMWV.split("\\r\\n")[0];
                except Exception as x:
                    WIMWV = ""
                    
                try:
                    GPGGA = SENSOR.split("$GPGGA,")[1];
                    GPGGA = GPGGA.split("\\r\\n")[0];
                except Exception as x:
                    GPGGA = ""
                
                try:
                    barometer = WIMDA.split(",")[0];
                    if barometer == "": barometer = "0.0";
                    
                    temp = WIMDA.split(",")[4];
                    if temp == "": temp = "0.0";
                    temp = str((9/5 * float(temp)) + 32);
                    
                    humidity = WIMDA.split(",")[8];
                    if humidity == "": humidity = "0.0";
                except Exception as x:
                    barometer = ""
                    temp = ""
                    humidity = ""
                    
                try:
                    windspeed = WIMWV.split(",")[2];
                    if windspeed == "": windspeed = "0.0";
                    windspeed = '{:.{}f}'.format(1.852 * float(windspeed), 1);
                    
                    winddir = WIMWV.split(",")[0];
                    if winddir == "": winddir = "0.0";
                except Exception as x:
                    windspeed = ""
                    winddir = ""
                rainrate = "0.0";
                try:
                    WIXDR = SENSOR.split("$WIXDR,")[1]
                    rainrate = WIXDR.split(",")[1];
                except Exception as x:
                    rainrate = "0.0";                
                
                solarrad = "0.0";
                
                lat = GPGGA.split(",")[1];
                ns = GPGGA.split(",")[2];
                lon = GPGGA.split(",")[3];
                ew = GPGGA.split(",")[4];
                
                if lat != "" and lon != "":
                    lat = str(float(lat) / 100);
                    lon = str(float(lon) / 100);
                    lat1 = lat.split(".")[0];
                    lat2 = str(float("0." + lat.split(".")[1]) / 60).replace("0.00","");
                    lat = lat1 + "." + lat2;
                    lon1 = lon.split(".")[0];
                    lon2 = str(float("0." + lon.split(".")[1]) / 60).replace("0.00","");
                    lon = lon1 + "." + lon2;
                    
                    if ns == "S": lat = "-" + lat;
                    if ew == "W": lon = "-" + lon;
                    
                WS = "PP22_AIRMAR;0;" + barometer + ";" + temp + ";" + humidity + ";" + temp + ";" + windspeed + ";" + windspeed + ";" + winddir + ";" + humidity + ";" + rainrate + ";0;" + solarrad + ";0.0;0;" + rainrate + ";" + lat + ";" + lon;
                
            else:
                WS = "PP22_AIRMAR;0;0;0;0;0;0;0;0;0;0;0;0;0.0;0;0;0;0\\r\\n'"
                
            update_sensor_value(str(sys.argv[1]),WS.replace("'","''"))
            
        except Exception as e2:
            print(e2)
            is_connect = False
            print("Reconnect Airmar Sensor ID: " + str(sys.argv[1]));
            update_sensor_value(str(sys.argv[1]),0)
        
        time.sleep(1)
        
except Exception as e: 
    print(e)