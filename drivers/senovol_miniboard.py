import serial
import sys
import time
import db_connect
# HC Reader Senovol
try:
    mydb = db_connect.connecting()
    mycursor = mydb.cursor()    
    mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
    sensor_reader = mycursor.fetchone()
    ser = serial.Serial(str(sensor_reader[0]),int(sensor_reader[1]),timeout=1)
    print("[V] Senovol Miniboard Connected")
except Exception as e: 
    print("[X] Senovol Miniboard Sensor ID: " + str(sys.argv[1]) + " " + e)
    ser = None
    
def update_sensor_value(sensor_reader_id,pin,value):
    try:
        value = value.replace("b'","")
        value = value.replace("'","")
        try:
            mycursor.execute("SELECT id FROM sensor_values WHERE sensor_reader_id = '{}' AND pin = '{}'".format(sensor_reader_id,pin))
            sensor_value = mycursor.fetchone()
            sensor_value_id = sensor_value[0] if sensor_value != None else None
            if(sensor_value_id == None):
                mycursor.execute("INSERT INTO sensor_values (sensor_reader_id,pin,value) VALUES ('{}','{}','{}')".format(sensor_reader_id,pin,value))
                mydb.commit()
            else:
                mycursor.execute("UPDATE sensor_values SET value = '{}' WHERE id = '{}'".format(value,str(sensor_value_id)))
                mydb.commit()
        except Exception as e:
            print("Query Error: "+e)
    except Exception as e2:
        print("Query Erorr: "+e2)
        return None
print("Trying to reading data...")
while True:
    try:
        if(ser.isOpen() == False):
            ser.open()
        ser.write(b'data.senovol#')
        if(ser.in_waiting > 0):
            response = str(ser.readline())
            response = response.replace("b'","")
            response = response.replace("'","")
            response = response.replace("'","")
            #print(response)
            if("END_SENOVOL" in response):
                value = response
                pin = "0"           
                update_sensor_value(str(sys.argv[1]), pin, value)
    except Exception as e:
        print("Error Reading Sensor: ",e)
        value = "SENOVOL;0.046;END_SENOVOL"
        update_sensor_value(str(sys.argv[1]), "0", value)
        ser.close()
    time.sleep(1)