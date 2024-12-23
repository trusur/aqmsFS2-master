import serial
import db
import sys
from datetime import datetime, timedelta
import atexit
from time import sleep


def exit_handler(ser):
    print("Closing Serial Port...")
    if(ser is not None):
        ser.close()

# Store Data Gas
def store_data_batch(sensor_reader_id:str,pin:str,data:str,prefix_return:str=None):
    try:
        datas = data.replace(" ", "").split(prefix_return) if prefix_return else data.replace(" ", "")
        for index, res in enumerate(datas):
            new_pin = str(pin) + str(index+1)
            if 'ERROR' in res:
                db.update_sensor_values(sensor_reader_id, new_pin, -999)
            else :
                db.update_sensor_values(sensor_reader_id, new_pin, res)

    except Exception as e: 
        print('Data Batch Validation Error: '+str(e))

def store_data_single(sensor_reader_id:str,pin:str,data:str,prefix_return:str=None):
    """
    Store a single data into database.

    Parameters
    ----------
    sensor_reader_id : str
        Sensor reader ID
    pin : str
        Pin number
    data : str
        Data to store
    prefix_return : str, optional
        Prefix return string, by default None

    Returns
    -------
    None
    """

    try:
        new_pin = str(pin) + str(0)
        if 'ERROR' in data:
            db.update_sensor_values(sensor_reader_id,new_pin, -999)
        else :
            db.update_sensor_values(sensor_reader_id,new_pin, data)
   
    except Exception as e: 
        print('Data Batch Validation Error: '+str(e))


    
def store_data(sensor_reader_id:str,pin:str,data:str,sensor_type:str,prefix_return:str=None):
    try:
        datas = data.replace(" ", "").split(";")
        if datas not in ['', None] and len(datas) >= 11:
            new_pin = str(pin) + str(0)
            db.update_sensor_values(sensor_reader_id, new_pin, data,sensor_type)
    except Exception as e:
        print(f'{sensor_type} Data Validation Error: '+str(e))


    
# Hashing by command
def execute_command( sensor_reader_id, pin, data,prefix_return_batch=None):
    try:
        hash_function = {
            'single': store_data_single,
            'batch' : store_data_batch
            }

        sensor_types = "single" if not prefix_return_batch else "batch"
        hash_function[sensor_types](sensor_reader_id,pin,data,prefix_return_batch)
        
    except Exception as e:
        print(f'Data Validation Error: '+str(e))
        return None
    

# Get Motherboard Command List
def get_data_from_motherboard(type):
    try:
        cnx = db.connect()
        cursor = cnx.cursor(dictionary=True, buffered=True)
        cursor.execute("SELECT * FROM motherboard where type=%s and is_enable = 1 order by is_priority desc", (type,))
        rows = cursor.fetchall() if type == "read" else cursor.fetchone()
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
        responses = ""
        ser.write(bytes(command, 'utf-8'))
        timeout = 0
        while responses.find(prefix_return) == -1 and timeout < max_timeout:
            line = ser.readline().decode('utf-8').strip('\r\n')
        

            #tambahakan kode pengecheckan apakah response = SELESAI CALIBRATION?

            responses += line
            timeout += 1
        # ser.close()
        return responses
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

def update_pump_data(ser, get_pump):
    # Get pump data from motherboard
    command = get_pump['command']
    prefix_return = get_pump['prefix_return']
    response = get_motherboard_value(ser, command, prefix_return)

    if "ERROR;" in response or not response :
        print("Error Read Pump Data")
        return False

    if 'END_PLC_DELTA;' in response:
        response = response.split("END_PLC_DELTA;")[1]

    res = response.split(";")
    # SMART_PUMP;[StatusMode];[SpeedPWM];[PumpStatus];[SetTime];[Currenttime];END_SMART_PUMP;"
    db.set_configuration("pump_speed", res[2])
    db.set_configuration("pump_state", res[3])
    db.set_configuration("pump_interval", res[4])
    
    # Calculate and set the last pump time
    # time_runner = int(res[4]) - int(res[5])
    pump_last = datetime.now() - timedelta(seconds=int(res[5]))
    db.set_configuration("pump_last", pump_last.strftime("%Y-%m-%d %H:%M:%S"))
    
    return True

# Running Main Function
def main():
    driver = get_driver()

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
                print(responseReady)
                break
    except Exception as e:
        print("Serial Port Error : ")
        print(e)
        ser = None
        return None
 
    sensor_reader_id = driver['id']
    while True:
        try:
            # get command to get data
            motherboards = get_data_from_motherboard('read')

            #  command to get and set smart pump 
            get_pump = get_data_from_motherboard('read_pump')
            if get_pump:
                #  check trigger smart pump for setting interval and speed
                pump_has_trigger_change = db.get_configuration("pump_has_trigger_change","1")
                if pump_has_trigger_change :
                    # set pump speed
                    set_pump_speed = get_data_from_motherboard('set_pump_speed')
                    if not set_pump_speed:
                        print("Command Set Pump Speed not active or not exist")
                        sleep(2)
                        continue
                    pump_speed = db.get_configuration("pump_speed") or 100 # set default 100% for pump speed
                    command_pump_speed = set_pump_speed['command'].replace('value', str(pump_speed))
                    prefix_return_pump_speed = set_pump_speed['prefix_return']
                    response = get_motherboard_value(ser, command_pump_speed, prefix_return_pump_speed)
                    if 'ERROR' in response:
                        print("Error Set Pump Speed ")
                        sleep(2)
                        continue

                    # set pump interval
                    set_pump_interval = get_data_from_motherboard('set_pump_interval')
                    if not set_pump_speed:
                        print("Command Set Pump Interval not active or not exist")
                        sleep(2)
                        continue
                    pump_interval = db.get_configuration("pump_interval") or 21600 # set default 6 hours for pump interval
                    command_pump_interval = set_pump_interval['command'].replace('value', str(pump_interval))
                    prefix_return_pump_interval = set_pump_interval['prefix_return']
                    response = get_motherboard_value(ser, command_pump_interval, prefix_return_pump_interval)
                    if 'ERROR' in response:
                        print("Error Set Pump Interval")
                        sleep(2)
                        continue

                    db.set_configuration("pump_has_trigger_change","")

                # check last pump to update data pump
                last_pump = db.get_configuration("pump_last")
                interval = db.get_configuration("pump_interval")
                if last_pump in [None,'']:
                    # if fails read pump data then repeat proccess
                    if not update_pump_data(ser, get_pump):
                        sleep(3)
                        continue
                else :
                    result = datetime.now() - timedelta(seconds=int(interval)) > datetime.strptime(last_pump, '%Y-%m-%d %H:%M:%S')
                    if result:
                        if not update_pump_data(ser, get_pump):
                            continue

                #  check trigger smart pump for setting interval and speed
                pump_switch = db.get_configuration("pump_switch","1")
                if pump_switch :
                    # command for switching mode pump manua, 0 untuk auto, 1 untuk Manual.
                    switching_pump = get_data_from_motherboard('mode_pump')
                    command_switching_pump = switching_pump['command'].replace('value', '1')
                    prefix_return_switching_pump = switching_pump['prefix_return']
                    response = get_motherboard_value(ser, command_switching_pump, prefix_return_switching_pump)
                    if 'ERROR' in response:
                        print("Error Switching Pump")
                        sleep(2)
                        continue

                    # response = "SMART_PUMP;[StatusMode];[SpeedPWM];[PumpStatus];[SetTime];[Currenttime];END_SMART_PUMP;"
                    mode = response.split(";")[1] 
                    if mode != 1:
                        sleep(2)
                        continue
                        
                    # command for switching togle pump
                    switching_pump = get_data_from_motherboard('togle_pump')
                    command_switching_pump = switching_pump['command']
                    prefix_return_switching_pump = switching_pump['prefix_return']
                    response = get_motherboard_value(ser, command_switching_pump, prefix_return_switching_pump)
                    if 'ERROR' in response:
                        print("Error Togle Pump")
                        sleep(2)
                        continue
                    pump_status = response.split(";")[3]
                    pump_status_now = db.get_configuration("pump_state")
                    if pump_status == pump_status_now:
                        sleep(2)
                        continue

                    # command for switching mode pump auto 
                    switching_pump = get_data_from_motherboard('mode_pump')
                    command_switching_pump = switching_pump['command'].replace('value', '0')
                    prefix_return_switching_pump = switching_pump['prefix_return']
                    response = get_motherboard_value(ser, command_switching_pump, prefix_return_switching_pump)
                    if 'ERROR' in response:
                        print("Error Switching Pump")
                        sleep(2)
                        continue
                    
                    mode = response.split(";")[1] 
                    if mode != 0:
                        sleep(2)
                        continue

                    db.set_configuration("pump_state",response.split(";")[3])
                    db.set_configuration("pump_switch",None)

                    # # if fails read pump data then repeat proccess
                    # if not update_pump_data(ser, get_pump):
                    #     continue
                        
                    
                # check proses calibration
                check_calibration = db.get_calibration_active()
                # parameter_calibration = check_calibration['code'] if check_calibration else None

                if check_calibration:
                    calibration_type = 'zero' if check_calibration['calibration_type'] == 0 else 'span'
                    get_motherboard = get_data_from_motherboard(calibration_type)
                    
                    # start if not executed
                    if check_calibration['is_executed'] == 0:
                        command = get_motherboard['command']
                        prefix_return = get_motherboard['prefix_return']
                        response = get_motherboard_value(ser,command,prefix_return)
                        if not 'SUCCESS' in response:
                            print("Error Start Calibration")
                            continue

            # process get data sensor
            for motherboard in motherboards:
                pin = motherboard['id']
                command = motherboard['command']
                prefix_return = motherboard['prefix_return']
                prefix_return_batch = motherboard['prefix_return_batch']
               
                response = get_motherboard_value(ser, command, prefix_return)

                if response in ['',None, 'COMMAND_ERROR;']:
                    new_pin = str(pin) + str(0)
                    db.update_sensor_values(sensor_reader_id,new_pin, -999)
                    print("Pin "+str(pin)+" Error")
                    continue
           
                execute_command(sensor_reader_id, pin, response,prefix_return_batch)
                print(f"Read Pin {pin}")
                #print(f"Get Data Pin {pin} " + datetime.now().strftime("%Y-%m-%d %H:%M:%S"))
                  
        except Exception as e:
            print('main function error: ',e)
        
          
if __name__ == "__main__":
    main()
