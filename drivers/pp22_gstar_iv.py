from __future__ import print_function
import sys
import serial
import time
import db_connect

is_connect = False
GPS = ";;0"

try:
    mydb = db_connect.connecting()
    mycursor = mydb.cursor()
    
    mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
    sensor_reader = mycursor.fetchone()
except Exception as e: 
    print("[X]  Sensor ID: " + str(sys.argv[1]) + " " + e)
    
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
                            
        if(SENSOR.count("$GPGGA") > 0):
            is_connect = True
            print("[V] Sensor " + sensor_reader[0] + " CONNECTED")
            return COM
        else:
            is_connect = False
            return None
            
    except Exception as e: 
        return None
    
# connect_sensor()
# time.sleep(3)

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
                
            if(SENSOR.count("$GPGGA") > 0):
                try:
                    GPGGA = SENSOR.split("$GPGGA,")[1];
                    GPGGA = GPGGA.split("\\r\\n")[0];
                except Exception as x:
                    GPGGA = ""

                lat = GPGGA.split(",")[1];
                ns = GPGGA.split(",")[2];
                lon = GPGGA.split(",")[3];
                ew = GPGGA.split(",")[4];
                altitude = GPGGA.split(",")[8];
                
                if(altitude == ""):
                    altitude = "0"
                    
                if(GPGGA.split(",")[9] != "M"):
                    altitude = str(float(altitude) * 0,3048)
                    
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

                GPS = lat + ";" + lon + ";" + altitude
                
            else:
                GPS = ";;0"
                
            update_sensor_value(str(sys.argv[1]),GPS)
            
            sql = "UPDATE configurations SET content='" + lat + "' WHERE name = 'latitude'";
            mycursor.execute(sql)
            mydb.commit()
            sql = "UPDATE configurations SET content='" + lon + "' WHERE name = 'longitude'";
            mycursor.execute(sql)
            mydb.commit() 
            
        except Exception as e2:
            print(e2)
            is_connect = False
            print("Reconnect Sensor ID: " + str(sys.argv[1]));
            update_sensor_value(str(sys.argv[1]),";;0")
            sql = "UPDATE configurations SET content='' WHERE name = 'latitude'";
            mycursor.execute(sql)
            mydb.commit()
            sql = "UPDATE configurations SET content='' WHERE name = 'longitude'";
            mycursor.execute(sql)
            mydb.commit() 
        
        time.sleep(1)
        
except Exception as e: 
    print(e)