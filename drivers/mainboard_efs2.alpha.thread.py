import serial
import db
import sys
from datetime import datetime, timedelta
import atexit
import os
import time
from concurrent.futures import ProcessPoolExecutor


def process_motherboard(motherboard, ser, sensor_reader_id):
    pin = motherboard['id']
    command = motherboard['command']
    prefix_return = motherboard['prefix_return']

    response = get_motherboard_value(ser, command, prefix_return)

    if response in ['', None, 'COMMAND_ERROR;']:
        db.update_sensor_values(sensor_reader_id, pin, -999, "ERROR")
        print(f"Sensor Value : -999 (Pin: {pin})")
        return  

    execute_command(command, sensor_reader_id, pin, response)


def exit_handler(ser):
    print("Closing Serial Port...")
    if(ser is not None):
        ser.close()

# Store Data Gas
def store_data_gas(sensor_reader_id:str,pin:str,data:str):
    try:
        sematech = data.replace("END_SEMEATECH_BATCH;", "").replace("SEMEATECH_BATCH;", "").replace(" ","").split("END_SEMEATECH_DATA;")
        for index, res in enumerate(sematech):
            new_pin = str(pin) + str(index+1)
            data_gas = res.split(";")
            if data_gas not in ['', None] and len(data_gas) > 7:
                db.update_sensor_values(sensor_reader_id, new_pin, res,data_gas[2])
    except Exception as e: 
        print('Gas Data Validation Error: '+str(e))

    
def store_data_pm(sensor_reader_id:str,pin:str,data:str):
    try:
        data_pm = data.replace(" ", "").split(";")
        print("masuk kesini")
        if data_pm not in ['', None] and len(data_pm) >= 11:
            new_pin = str(pin) + str(0)
            db.update_sensor_values(sensor_reader_id, new_pin, data_pm,'PM')
        else :
            raise Exception(f"PM Response Doesnt match : {data}")
    except Exception as e:
        print('PM Data Validation Error: '+str(e))


def store_data_weather(sensor_reader_id:str,pin:str,data:str):
    try:
        data_meteorologi = data.replace(" ", "").split(";")
        if data_meteorologi not in ['',None] and len(data_meteorologi) > 12:
            new_pin = str(pin) + str(0)
            db.update_sensor_values(sensor_reader_id, new_pin, data,'WEATHER')
        else :
            raise Exception(f"PM Response Doesnt match : {data}")
    except Exception as e:
        print('PM Data Validation Error: '+str(e))


def store_data_hc_senovol(sensor_reader_id:str,pin:str,data:str):
    try:
        data_senovol = data.replace(" ", "").split(";")
        if data_senovol not in ['',None] and len(data_senovol) == 3:
            new_pin = str(pin) + str(0)
            db.update_sensor_values(sensor_reader_id, new_pin, data,'HC')
        else :
            raise Exception(f"HC Senovol Response Doesnt match : {data}")
    except Exception as e:
        print('HC Senovol Data Validation Error: '+str(e))


def store_data_hc_semeatech(sensor_reader_id:str,pin:str,data:str):
    try:
        store_data_hc_semeatech = data.replace(" ", "").split(";")
        if store_data_hc_semeatech not in ['',None] and len(store_data_hc_semeatech) > 7:
            new_pin = str(pin) + str(0)
            db.update_sensor_values(sensor_reader_id, new_pin, data,'HC')
        else :
            raise Exception(f"HC Semeatech Response Doesnt match : {data}")
    except Exception as e:
        print('HC Semeatech Data Validation Error: '+str(e))

    
# Hashing by command
def execute_command(command, sensor_reader_id, pin, data):
    command_to_function = {
        'getData,pm_opc,#': store_data_pm,
        'getData,semeatech,[devID],#': "store_semeatech_single",
        'getData,semeatech,batch,1,4,#': store_data_gas,
        'getData,senovol,[AnalogInPin],[PIDValue],[AREF],#': store_data_hc_senovol,
        '4ECM;[deviceID];[SensorType];[SensorUnit];[SensorConcentrationValue];[MeasurementRange];[CalibrationGas];END_4ECM;' : store_data_hc_semeatech,
        'getData,RK900-011,#': store_data_weather
    }

    if command in command_to_function:
        command_to_function[command](sensor_reader_id,pin,data)
    else:
        print(f"Unknown command: {command}")
        return None

# Get Motherboard Command List
def get_read_data_from_motherboard():
    try:
        cnx = db.connect()
        cursor = cnx.cursor(dictionary=True, buffered=True)
        cursor.execute("SELECT * FROM motherboard where type='read' and is_enable = 1 order by is_priority desc")
        rows = cursor.fetchall()
        cursor.close()
        cnx.close()
        return rows
    except Exception as e: 
        print('Get Motherboards Error: ',e)
        return []
    
# Get Response Values From Motherboard
def get_motherboard_value(ser, command, prefix_return):
    try:
        if(ser is None):
            return None
        max_timeout = 50
        timeout = 0
        response = ""
        ser.write(bytes(command, 'utf-8'))
        timeout = 0
        while response.find(prefix_return) == -1 and timeout < max_timeout:
            response += ser.readline().decode('utf-8').strip('\r\n')
            timeout += 1
        # ser.close()
        return response
    except Exception as e: 
        print('Connect Serial Error: ',e)
        return None
    
# Get Driver Info From Filename
def get_driver():
    try:
        filename = sys.argv[0].split("/")[-1]
        cnx = db.connect()
        cursor = cnx.cursor(dictionary=True)
        cursor.execute("select * from sensor_readers where driver='"+filename+"'")
        row = cursor.fetchone()
        cursor.close()
        cnx.close()
        return row
    except Exception as e:
        # print(e)
        return None
def is_motherboard_ready(ser):
    try:
        response = ""
        timeout = 0
        max_timeout = 5
        while response.find("DEVICE_READY;") == -1 and timeout < max_timeout:
            response = ser.readline().decode('utf-8').strip('\r\n')
            if(response == "DEVICE_READY;"):
                timeout = max_timeout
                break
            timeout+1
        if(response.find("DEVICE_READY;") == 0):
            return True
        
        return False
    except:
        return False

    
def switch_pump(ser, pump_state):
    try:
        if (ser is None):
            return False
        pump_speed = db.get_configuration("pump_speed")
        pump_speed = 100 if int(pump_speed) > 100 else int(pump_speed)
        if(pump_state == 1):
            command = "pump2.set."+str(pump_speed)+"#"
        else:
            command = "pump.set."+str(pump_speed)+"#"
        max_timeout = 60
        timeout = 0
        response = ""
        ser.write(bytes(command, 'utf-8'))
        timeout = 0
        while response.find("END_PUMP") == -1 and timeout < max_timeout:
            response += ser.readline().decode('utf-8').strip('\r\n')
            timeout += 1
        if(response.find("END_PUMP") > -1):
            db.set_configuration("pump_state",pump_state)
            return True
        return False
    except Exception as e: 
        print('Switch Pump Error: '+str(e))
        return False
# Check Switch Pump
def check_pump(ser):
    try:
        if(ser is None):
            return False
        now = datetime.now()
        pump_last = db.get_configuration("pump_last")
        pump_interval = db.get_configuration("pump_interval")
        pump_state = db.get_configuration("pump_state")
        pump_switch_to = 1 if pump_state == "0" else 0
        pump_has_trigger_change = db.get_configuration("pump_has_trigger_change")
        if(not pump_has_trigger_change in ['']):
            if (switch_pump(ser,pump_state=pump_has_trigger_change) == True):
                print("Pump Switch to: "+str(pump_switch_to))
                db.set_configuration("pump_has_trigger_change","")
                db.set_configuration("pump_last",str(now))
                return True

        if(pump_last in [None,'']):
            db.set_configuration("pump_last",str(now))
            # Switch Pompa 1 
            print("Pump Switch to: "+str(pump_switch_to))
            return switch_pump(ser,pump_switch_to)

        pump_last = datetime.strptime(pump_last, '%Y-%m-%d %H:%M:%S.%f')
        # Apakah waktu sekarang sudah melewati waktu interval
        last_switch  = now - timedelta(minutes=int(pump_interval))
        if(last_switch > pump_last):
            db.set_configuration("pump_last",str(now))
            # Switch Pump
            print("Pump Switch to: "+str(pump_switch_to))
            return switch_pump(ser,pump_switch_to)
        # print("Not Switch Pump")
        return False
    except Exception as e: 
        print('Check Pump Error: '+str(e))
        return False


# Running Main Function
def main():
    driver = get_driver()
    # db.set_configuration("pump_has_trigger_change","1")

    try:
        ser = serial.Serial(driver['sensor_code'], driver['baud_rate'], timeout=3)
        atexit.register(exit_handler, ser)
        max_timeout = 5
        timeout = 0
        responseReady = ""
        while responseReady != "DEVICE_READY;" and timeout < max_timeout:
            responseReady = ser.readline().decode('utf-8').strip('\r\n')
            timeout += 1
            if(responseReady == "DEVICE_READY;"):
                break
    except Exception as e:
        print("Serial Port Error : ")
        print(e)
        ser = None
        return None
 
    sensor_reader_id = driver['id']
    while True:
        try:
            start_time = time.time()
            
            # get command to get data
            motherboards = get_read_data_from_motherboard()

            with ProcessPoolExecutor(max_workers=4) as executor:
                futures = [executor.submit(process_motherboard, motherboard, ser, sensor_reader_id) for motherboard in motherboards]

            
            print("Done in: " + str(time.time() - start_time))
              
        except Exception as e:
            print('main function error: ',e)
        
          
if __name__ == "__main__":
    main()
