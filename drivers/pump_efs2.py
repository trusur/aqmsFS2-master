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
        pump_speed = db.get_configuration("pump_speed")
        pump_speed = 100 if int(pump_speed) > 100 else int(pump_speed)
        driver = get_driver()
        port = driver['sensor_code']
        baudrate = driver['baud_rate']
        print("Switch Pump: "+str(pump_state))
        print("Pump Speed: "+str(pump_speed))
        if(pump_state == 1):
            command = "pump2.set."+str(pump_speed)+"#"
        else:
            command = "pump.set."+str(pump_speed)+"#"
        max_timeout = 60
        timeout = 0
        response = ""
        responseReady = ""
        ser = serial.Serial(port, baudrate, timeout=5)
        while responseReady != "Ready" and timeout < max_timeout:
            responseReady = ser.readline().decode('utf-8').strip('\r\n')
            timeout += 1
            if(responseReady == "Ready"):
                break

        ser.write(bytes(command, 'utf-8'))
        timeout = 0
        while response.find("END_PUMP") == -1 and timeout < max_timeout:
            response += ser.readline().decode('utf-8').strip('\r\n')
            timeout += 1
        ser.close()
        if(response.find("END_PUMP") > -1):
            db.set_configuration("pump_state",pump_state)
            return True
        return False
    except Exception as e: 
        print('Switch Pump Error: '+str(e))
        return False
# Check Switch Pump
def main():
    try:
        now = datetime.now()
        pump_last = db.get_configuration("pump_last")
        pump_interval = db.get_configuration("pump_interval")
        pump_state = db.get_configuration("pump_state")
        pump_switch_to = 1 if pump_state == "0" else 0
        pump_has_trigger_change = db.get_configuration("pump_has_trigger_change")
        if(not pump_has_trigger_change in ['']):
            if (switch_pump(pump_state=pump_has_trigger_change) == True):
                db.set_configuration("pump_has_trigger_change","")
                db.set_configuration("pump_last",str(now))
                return True

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
            print("Pump Switch to: "+str(pump_switch_to))
            return switch_pump(pump_switch_to)
        # print("Not Switch Pump")
        return False
    except Exception as e: 
        print('Check Pump Error: '+str(e))
        return False

if __name__ == "__main__":
    main()
