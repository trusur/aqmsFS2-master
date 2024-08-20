import serial
import db
import sys
from datetime import datetime, timedelta
import atexit

def exit_handler(ser):
    print("Closing Serial Port...")
    if(ser is not None):
        ser.close()


# Get Motherboard Command List
def get_motherboards():
    try:
        cnx = db.connect()
        cursor = cnx.cursor(dictionary=True, buffered=True)
        cursor.execute("SELECT * FROM motherboard where is_enable = 1 order by is_priority desc")
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
    db.set_configuration("pump_has_trigger_change","1")
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
        # start_time = time.time()
        try:
            check_pump(ser)
            motherboards = get_motherboards()
            is_calibration = db.get_configuration("is_calibration") # ID table calibrations
            calibration_mode = db.get_configuration("calibration_mode") # 0 = Zero, 1 = Span
            set_span = db.get_configuration("set_span")
            if(set_span not in [None,'']):
                _set_span = set_span.split(";")
                _parameter = _set_span[0]
            else:
                _parameter = None
            # print("Executing "+str(len(motherboards))+" commands")
            for motherboard in motherboards:
                pin = motherboard['id']
                command = motherboard['command']
                prefix_return = motherboard['prefix_return']
                response = get_motherboard_value(ser, command, prefix_return)
                # print(response)
                if(command.find("getData,semeatech,batch,1,4,#") == 0):
                    sematech = response.split("END_SEMEATECH;")
                    for index,res in enumerate(sematech):
                        new_pin = str(pin) +  str(index)
                        final_str = res.replace("SEMEATECH_BATCH;","")
                        final_str = final_str.replace("END_SEMEATECH_BATCH;","")
                        if(final_str not in ['', None]):
                            db.update_sensor_values(sensor_reader_id,new_pin, final_str)
                            if (is_calibration != None and calibration_mode == '1' and final_str.find(_parameter) > -1):
                                calibration = db.get_calibration(is_calibration)
                                if(calibration is not None):
                                    if(calibration['is_executed'] == 0):
                                        # Send Signal to Mainboard here 
                                        None
                                    ppb_value = final_str.split(";")[4] if len(final_str.split(";")) > 6 else None
                                    db.set_calibration_log(calibration['id'],calibration['parameter_id'],ppb_value,datetime.now())

                if(response not in ['', None]):
                    db.update_sensor_values(sensor_reader_id,pin, response)
                else:
                    db.update_sensor_values(sensor_reader_id,pin, '-999')
        except Exception as e:
            print('main funciton error: ',e)
        # print("Total Time: " + str(time.time() - start_time))
if __name__ == "__main__":
    main()