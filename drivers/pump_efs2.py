import serial
import db
from datetime import datetime, timedelta
def get_driver():
    try:
        filename = "mainboard_efs2.py"
        cnx = db.connect()
        cursor = cnx.cursor(dictionary=True)
        cursor.execute("select * from sensor_readers where driver='"+filename+"'")
        row = cursor.fetchone()
        cursor.close()
        cnx.close()
        return row
    except Exception as e:
        print(e)
        return None
    
def switch_pump(pump_state):
    try:
        driver = get_driver()
        port = driver['sensor_code']
        baudrate = driver['baud_rate']
        if(pump_state == 1):
            command = "pump2.set.100#"
        else:
            command = "pump.set.100#"
        max_timeout = 60
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
        if(response.find("END_PUMP") > -1):
            db.set_configuration("pump_state",pump_state)
            return True
        return False
    except Exception as e: 
        print('Switch Pump Error: ',e)
        return False
# Check Switch Pump
def main():
    try:
        now = datetime.now()
        pump_last = db.get_configuration("pump_last")
        pump_interval = db.get_configuration("pump_interval")
        pump_state = db.get_configuration("pump_state")
        pump_switch_to = 1 if pump_state == "0" else 0

        if(pump_last in [None,'']):
            db.set_configuration("pump_last",str(now))
            # Switch Pompa 1 
            return switch_pump(pump_switch_to)

        pump_last = datetime.strptime(pump_last, '%Y-%m-%d %H:%M:%S.%f')
        # Apakah waktu sekarang sudah melewati waktu interval
        last_switch  = now - timedelta(minutes=int(pump_interval))
        if(last_switch > pump_last):
            db.set_configuration("pump_last",str(now))
            # Switch Pump
            return switch_pump(pump_switch_to)
        print("Not Switch Pump")
        return False
    except Exception as e: 
        print('Check Pump Error: ',e)
        return False

if __name__ == "__main__":
    main()
