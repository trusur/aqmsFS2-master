import serial
import db
import sys
from datetime import datetime, timedelta

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

def switch_pump(pump_state):
    try:
        driver = get_driver()
        port = driver['sensor_code']
        baudrate = driver['baud_rate']
        if(pump_state == 1):
            command = "pump2.set.100#"
        else:
            command = "pump.set.100#"
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
        while response.find("END_PUMP") == -1 and timeout < max_timeout:
            response += ser.readline().decode('utf-8').strip('\r\n')
            print(response)
            timeout += 1
        ser.close()
        db.set_configuration("pump_state",pump_state)
        return True
    except Exception as e: 
        print('Switch Pump Error: ',e)
        return False
# Check Switch Pump
def check_pump():
    try:
        now = datetime.now()
        pump_last = db.get_configuration("pump_last")
        pump_interval = db.get_configuration("pump_interval")
        pump_state = db.get_configuration("pump_state")
        pump_switch_to = 1 if pump_state == "0" else 0

        if(pump_last in [None,'']):
            db.set_configuration("pump_last",str(now))
            # Switch Pompa 1 
            switch_pump(pump_switch_to)

            return True
        pump_last = datetime.strptime(pump_last, '%Y-%m-%d %H:%M:%S.%f')
        # Apakah waktu sekarang sudah melewati waktu interval
        last_switch  = now - timedelta(seconds=int(pump_interval))
        if(last_switch > pump_last):
            db.set_configuration("pump_last",str(now))
            # Switch Pump
            switch_pump(pump_switch_to)
            return True
        return False
    except Exception as e: 
        print('Check Pump Error: ',e)
        return False

# Running Main Function
def main():
    # Check Pump Switch
    check_pump()
    driver = get_driver()
    sensor_reader_id = driver['id']
    port = driver['sensor_code']
    baudrate = driver['baud_rate']
    motherboards = get_motherboards()
    print("Executing "+str(len(motherboards))+" commands")
    for motherboard in motherboards:
        pin = motherboard['id']
        command = motherboard['command']
        prefix_return = motherboard['prefix_return']
        response = get_motherboard_value(port, baudrate, command, prefix_return)
        if(command.find("data.semeatech") == 0):
            sematech = response.split(";END;")
            for index,res in enumerate(sematech):
                final_str = res.replace("SEMEATECH START;","")
                final_str = final_str.replace("SEMEATECH FINISH;","")
                new_pin = str(pin) +  str(index)
                if(final_str not in ['', None]):
                    db.update_sensor_values(sensor_reader_id,new_pin, final_str)
        if(response not in ['', None]):
            db.update_sensor_values(sensor_reader_id,pin, response)
        else:
            db.update_sensor_values(sensor_reader_id,pin, '-999')
    print("Done")
if __name__ == "__main__":
    main()