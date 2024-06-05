from __future__ import print_function
import sys
from labjack import ljm
import time
import db_connect

AIN = [0.0,0.0,0.0,0.0,0.0,0.0,0.0,0.0]

try:
    mydb = db_connect.connecting()
    mycursor = mydb.cursor()
    mycursor.execute("SELECT sensor_code,pins FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
    sensor_reader = mycursor.fetchone()
    labjack = ljm.openS("ANY", "ANY", sensor_reader[0])
    print("[V] Labjack " + sensor_reader[0] + " CONNECTED")
except Exception as e:
    print("[X]  Labjack " + e)
    
def update_sensor_value(sensor_reader_id,value,pin):
    try:
        mycursor.execute("SELECT id FROM sensor_values WHERE sensor_reader_id = '"+ sensor_reader_id +"' AND pin = '" + pin + "'")
        sensor_value_id = mycursor.fetchone()[0]
        mycursor.execute("UPDATE sensor_values SET value = '" + value + "' WHERE id = '" + str(sensor_value_id) + "'")
        mydb.commit()
    except Exception as e:
        mycursor.execute("INSERT INTO sensor_values (sensor_reader_id,pin,value) VALUES ('" + sensor_reader_id + "','" + pin + "','0')")
        mydb.commit()

while True:
    try:
        labjack = ljm.openS("ANY", "ANY", sensor_reader[0])
        for x in sensor_reader[1].split(','):
            AIN[int(x)] = ljm.eReadName(labjack, "AIN" + x)
        
    except Exception as e: 
        for x in sensor_reader[1].split(','):
            AIN[int(x)] = 0
    
    for x in sensor_reader[1].split(','):
        update_sensor_value(str(sys.argv[1]),str(AIN[int(x)]),x)
            
    time.sleep(1) 