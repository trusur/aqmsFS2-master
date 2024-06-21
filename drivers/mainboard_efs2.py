import serial
import db
import sys
from datetime import datetime, timedelta
import time
# Get Motherboard Command List
def get_motherboards():
    try:
        cnx = db.connect()
        cursor = cnx.cursor(dictionary=True)
        cursor.execute("SELECT * FROM motherboard where is_enable = 1")
        rows = cursor.fetchall()
        cursor.close()
        cnx.close()
        return rows
    except Exception as e: 
        print('Get Motherboards Error: ',e)
        return []
    
# Get Response Values From Motherboard
def get_motherboard_value(port, baudrate, command, prefix_return):
    try:
        max_timeout = 50
        timeout = 0
        response = ""
        responseReady = ""
        ser = serial.Serial(port, baudrate, timeout=3)
        while responseReady != "Ready" and timeout < max_timeout:
            responseReady = ser.readline().decode('utf-8').strip('\r\n')
            timeout += 1
            if(responseReady == "Ready"):
                break

        ser.write(bytes(command, 'utf-8'))
        timeout = 0
        while response.find(prefix_return) == -1 and timeout < max_timeout:
            response += ser.readline().decode('utf-8').strip('\r\n')
            print(response)
            timeout += 1
        ser.close()
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
        while response.find("Ready") == -1 and timeout < max_timeout:
            response = ser.readline().decode('utf-8').strip('\r\n')
            if(response == "Ready"):
                timeout = max_timeout
                break
            timeout+1
        if(response.find("Ready") == 0):
            return True
        
        return False
    except:
        return False


# Running Main Function
def main():
    time.sleep(1)
    driver = get_driver()
    sensor_reader_id = driver['id']
    port = driver['sensor_code']
    baudrate = driver['baud_rate']
    motherboards = get_motherboards()
    is_calibration = db.get_configuration("is_calibration") # 0 = Inactive, 1 = Calibration Running, 2 = Calibration Done
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
        response = get_motherboard_value(port, baudrate, command, prefix_return)
        if(command.find("data.semeatech") == 0):
            sematech = response.split(";END;")
            for index,res in enumerate(sematech):
                new_pin = str(pin) +  str(index)
                final_str = res.replace("SEMEATECH START;","")
                final_str = final_str.replace("SEMEATECH FINISH;","")
                if(final_str not in ['', None]):
                    db.update_sensor_values(sensor_reader_id,new_pin, final_str)
                    if (is_calibration != None and calibration_mode == '1' and final_str.find(_parameter) > -1):
                        calibration = db.get_calibration(is_calibration)
                        if(calibration is not None):
                            ppm_value = final_str.split(";")[2] if len(final_str.split(";")) > 4 else None
                            db.set_calibration_log(calibration['id'],calibration['parameter_id'],ppm_value,datetime.now())

        if(response not in ['', None]):
            db.update_sensor_values(sensor_reader_id,pin, response)
        else:
            db.update_sensor_values(sensor_reader_id,pin, '-999')
    # print("Done")
if __name__ == "__main__":
    main()