import serial, struct, time
import sys
import db_connect

is_connect = False
returnval = "pp22_sensirion_sps30;0;0;0;0;0;0;0;0;0;0;";

try:
    mydb = db_connect.connecting()
    mycursor = mydb.cursor()
    
    mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
    sensor_reader = mycursor.fetchone()
        
except Exception as e: 
    print("[X]  SENSOR ID: " + str(sys.argv[1]) + " " + e)

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


def time_(): return int(time.time())

def datetime_():
    return time.strftime('%x %X', time.localtime())

class SPS30:
    NAME = 'SPS30'
    WARMUP = 9 # seconds
    
    def __init__(self, port, INTERVAL=1):
        self.port = port
        self.interval = INTERVAL
        self.warmup = SPS30.WARMUP
        self.name = SPS30.NAME
        self.lastSample = 0
        self.fanOn = 0
        self.is_started = False
        self.ser = serial.Serial(self.port, baudrate=115200, stopbits=1, parity="N",  timeout=2)

    def __str__(self):
        return f'{self.port}, {self.name}, {self.fanOn}, {self.lastSample}'

    
    def start(self):
        self.ser.write([0x7E, 0x00, 0x00, 0x02, 0x01, 0x03, 0xF9, 0x7E])
        
    def stop(self):
        self.ser.write([0x7E, 0x00, 0x01, 0x00, 0xFE, 0x7E])
    
    def fan_cleaning(self):
        self.ser.flushInput()
        self.ser.write([0x7E, 0x00, 0x56, 0x00, 0x00, 0xA9, 0x7E])
        time.sleep(10)
        self.ser.write([0x7E, 0x00, 0x56, 0x00, 0xA9, 0x7E])
        time.sleep(10)
        
    
    def read_values(self):
        self.ser.flushInput()        
        # Ask for data
        self.ser.write([0x7E, 0x00, 0x03, 0x00, 0xFC, 0x7E])
        toRead = self.ser.inWaiting()
        # Wait for full response
        # (may be changed for looking for the stop byte 0x7E)
        while toRead < 47:

            toRead = self.ser.inWaiting()
            # print(f'Wait: {toRead}')
            time.sleep(1)
        raw = self.ser.read(toRead)
        
        # Reverse byte-stuffing
        if b'\x7D\x5E' in raw:
            raw = raw.replace(b'\x7D\x5E', b'\x7E')
        if b'\x7D\x5D' in raw:
            raw = raw.replace(b'\x7D\x5D', b'\x7D')
        if b'\x7D\x31' in raw:
            raw = raw.replace(b'\x7D\x31', b'\x11')
        if b'\x7D\x33' in raw:
            raw = raw.replace(b'\x7D\x33', b'\x13')
        
        # Discard header and tail
        rawData = raw[5:-2]
        
        try:
            data = struct.unpack(">ffffffffff", rawData)
        except struct.error:
            data = (0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0)
        return data
    
    def read_serial_number(self):
        self.ser.flushInput()
        self.ser.write([0x7E, 0x00, 0xD0, 0x01, 0x03, 0x2B, 0x7E])
        toRead = self.ser.inWaiting()
        while toRead < 7:  #24
            toRead = self.ser.inWaiting()
            # print(f'Wait: {toRead}')
            time.sleep(1)
        raw = self.ser.read(toRead)
        
        # Reverse byte-stuffing
        if b'\x7D\x5E' in raw:
            raw = raw.replace(b'\x7D\x5E', b'\x7E')
        if b'\x7D\x5D' in raw:
            raw = raw.replace(b'\x7D\x5D', b'\x7D')
        if b'\x7D\x31' in raw:
            raw = raw.replace(b'\x7D\x31', b'\x11')
        if b'\x7D\x33' in raw:
            raw = raw.replace(b'\x7D\x33', b'\x13')
        
        # Discard header, tail and decode
        serial_number = raw[5:-3].decode('ascii')
        return serial_number

    def run_query(self):
        global returnval;
        if time_() - self.lastSample >= self.interval:
            if not self.is_started:   
                self.start()
                self.fanOn = time_()
                self.is_started = True
            if self.name == SPS30.NAME:
                self.fan_cleaning()
                name_  = self.read_serial_number()
                if len(name_) >0:
                    self.name = f'SPS_{name_}'
            if time_() - self.fanOn >= self.warmup:
                output = self.read_values()
                sensorData = ""
                for val in output:
                    sensorData += "{0:.2f},".format(val)

                output = ','.join([self.name, datetime_(),sensorData[:-1]])
                returnval = "pp22_sensirion_sps30;" + str(sensorData[:-1]).replace(",",";");
                self.lastSample = time_()
                if self.is_started:
                    self.stop()
                    self.is_started = False
            # else:
                # time.sleep(1)
        return None

    def close_port(self):
        self.ser.close()


if __name__ == '__main__':
    mycursor.execute("SELECT sensor_code,baud_rate FROM sensor_readers WHERE id = '"+ sys.argv[1] +"'")
    sensor_reader = mycursor.fetchone()

    p = SPS30(port=sensor_reader[0])
    while True:
        p.run_query()
        # print(returnval)
        update_sensor_value(sys.argv[1],str(returnval))
        time.sleep(1)